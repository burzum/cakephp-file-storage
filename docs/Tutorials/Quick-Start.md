Quick-Start
===========

Add this to your composer.json. Imagine is optional but you'll need it if you want to process images.

```js
{
	"require": {
		"burzum/cakephp-file-storage": "dev-3.0",
		"burzum/cakephp-imagine-plugin": "dev-3.0",
		"cakephp/migrations": "dev-master"
	}
}
```

app/Config/file_storage.php
---------------------------

There is a good amount of code to be added to prepare everything. In theory you can put all of this in bootstrap as well but to keep things clean it is recommended to put all of this in a separate file.

This might look like a lot things to do but when this is done storing the files will work immediately and you have a *very* flexible and powerful storage system configured.

```php
use Aws\S3\S3Client;
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageUtils;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;

// Instantiate a storage event listener
$listener = new BaseListener([
	'imageProcessing' => true, // Required if you want image processing!
	'pathBuilderOptions' => [
		// Preserves the original filename in the storage backend.
		// Otherwise it would use a UUID as filename by default.
		'preserveFilename' => true
	]
]);
// Attach the BaseListener to the global EventManager
EventManager::instance()->on($listener);

Configure::write('FileStorage', [
// Configure image versions on a per model base
	'imageSizes' => [
		'ProductImage' => [
			'large' => [
				'thumbnail' => [
					'mode' => 'inbound',
					'width' => 800,
					'height' => 800
				]
			],
			'medium' => [
				'thumbnail' => [
					'mode' => 'inbound',
					'width' => 200,
					'height' => 200
				]
			],
			'small' => [
				'thumbnail' => [
					'mode' => 'inbound',
					'width' => 80,
					'height' => 80
				]
			]
		]
	]
]);

// This is very important! The hashes are needed to calculate the image versions!
StorageUtils::generateHashes();

// Lets use the Amazon S3 adapter here instead of the default `Local` config.
// We need to pass a S3Client instance to this adapter to make it work
$S3Client = new S3Client([
	'version' => 'latest',
	'region'  => 'eu-central-1',
	'credentials' => [
		'key' => 'YOUR-AWS-S3-KEY-HERE',
		'secret' => 'YOUR-SECRET-HERE'
	]
]);

// Configure the S3 adapter instance through the StorageManager
StorageManager::config('S3', [
	'adapterOptions' => array(
		$S3Client,
		'YOUR-BUCKET-NAME-HERE', // Bucket
		[],
		true
	),
	'adapterClass' => '\Gaufrette\Adapter\AwsS3',
	'class' => '\Gaufrette\Filesystem'
]);
```

If you did everything right you can now run this command from your app:

```sh
bin/cake storage store <some-file-to-store-here> --adapter S3
```

If you did everything right your should see some output like this:

If you're not familiar with the CakePHP shell and running into problems with the shell, not the plugin itself, please [read this](http://book.cakephp.org/3.0/en/console-and-shells.html) first!

```
File successfully saved!
UUID: ebb21e79-029d-441d-8f2e-d8c20ca8f5a9
Path: file_storage/18/ef/b4/ebb21e79029d441d8f2ed8c20ca8f5a9/<some-file-to-store-here>
```

**It is highly recommended to read the following sections to understand how this works.**

* [Included Event Listeners](../Documentation/Included-Event-Listeners.md)
* [Image Storage and Versioning](../Documentation/Image-Storage-And-Versioning.md)
* [Specific Adapter Configurations](../Documentation/Specific-Adapter-Configurations.md)

app/Config/bootstrap.php
------------------------

Now include the file_storage.php setup in your ```app/Config/bootstrap.php```

```php
include('file_storage.php');
```

Load the Helper
---------------

```php
namespace App\View;
class AppView extends View {
	public function initialize() {
		parent::initialize();
		$this->loadHelper('Burzum/FileStorage.Image');
	}
}
```

Theoretical model setup
-----------------------

```php
namespace App\Model\Table;

use Cake\ORM\Table;

class Products extends Table {
	public function initialize() {
		parent::initialize();
		$this->hasMany('ProductImages', [
			'className' => 'ProductImages',
			'foreignKey' => 'foreign_key',
			'conditions' => [
				'ProductImages.model' => 'ProductImage'
			]
		]);
		$this->hasMany('Documents', [
			'className' => 'FileStorage.FileStorage',
			'foreignKey' => 'foreign_key',
			'conditions' => [
				'Documents.model' => 'ProductDocument'
			]
		]);
	}
}
```

```php
namespace App\Model\Table;

use Burzum\FileStorage\Model\Table\ImageStorageTable;

class ProductImagesTable extends ImageStorageTable {
	public function uploadImage($productId, $entity) {
		$entity = $this->patchEntity($entity, [
			'adapter' => 'Local',
			'model' => 'ProductImage',
			'foreign_key' => $productId
		]);
		return $this->save($entity);
	}
	public function uploadDocument($productId, $entity) {
		$entity = $this->patchEntity($entity, [
			'adapter' => 'Local',
			'model' => 'ProductDocument',
			'foreign_key' => $productId
		]);
		return $this->save($entity);
	}
}
```

Products Controller
-------------------

```php
namespace App\Controller;

class ProductsController extends AppController {
	// Upload an image
	public function upload($productId = null) {
		$entity = $this->Products->ProductImages->newEntity();
		if ($this->request->is(['post', 'put'])) {
			$entity = $this->Products->ProductImages->patchEntity(
				$entity,
				$this->request->data
			);
			if ($this->Products->ProductImages->uploadImage($productId, $entity)) {
				$this->Flash->set(__('Upload successful!'));
			}
		}
		$this->set('productImage', $entity);
	}
}
```

Products Upload View
--------------------

View for the controller action above `Products/upload.ctp`:

```php
echo $this->Form->create($productImage, array(
	'type' => 'file'
));
echo $this->Form->file('file');
echo $this->Form->error('file');
echo $this->Form->submit(__('Upload'));
echo $this->Form->end();
```

Displaying the Images
---------------------

[Read about the Image helper](../Documentation/The-Image-Helper.md)
