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

```php
use Aws\S3;
use Cake\Event\EventManager;
use Burzum\FileStorage\Lib\FileStorageUtils;

// Attach the S3 Listener to the global CakeEventManager
$listener = new S3StorageListener();
EventManager::instance()->on($listener);

// Attach the Image Processing Listener to the global CakeEventManager
$listener = new ImageProcessingListener();
EventManager::instance()->on($listener);

Configure::write('FileStorage', array(
	// Configure image versions on a per model base
	'imageSizes' => array(
		'ProductImage' => array(
			'large' => array(
				'thumbnail' => array(
					'mode' => 'inbound',
					'width' => 800,
					'height' => 800)),
			'medium' => array(
				'thumbnail' => array(
					'mode' => 'inbound',
					'width' => 200,
					'height' => 200
				)
			),
			'small' => array(
				'thumbnail' => array(
					'mode' => 'inbound',
					'width' => 80,
					'height' => 80
				)
			)
		)
	)
));

// This is very important! The hashes are needed to calculate the image versions!
FileStorageUtils::generateHashes();

// Optional, lets use the AwsS3 adapter here instead of local
$S3Client = \Aws\S3\S3Client::factory(array(
	'key' => 'YOUR-KEY',
	'secret' => 'YOUR-SECRET'
));

// Configure the Gaufrette adapter through the StorageManager
StorageManager::config('S3Image', array(
	'adapterOptions' => array(
		$S3Client,
		'YOUR-BUCKET-NAME',
		array(),
		true
	),
	'adapterClass' => '\Gaufrette\Adapter\AwsS3',
	'class' => '\Gaufrette\FileSystem')
);
```

app/Config/bootstrap.php
------------------------

Now include the file_storage.php setup in your ```app/Config/bootstrap.php```

```php
include('file_storage.php');
```

Theoretical model setup
-----------------------

```php
namespace App\Model\Table;

use Cake\ORM\Table;

class Products extends Table {
	public function initialize() {
		parent::initialize();
		$this->hasMany('Images', [
			'className' => 'ProductImages',
			'foreignKey' => 'foreign_key',
			'conditions' => [
				'Documents.model' => 'ProductImage'
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
	public function uploadImage($productId, $data) {
		$data['adapter'] = 'Local';
		$data['model'] = 'ProductImage',
		$data['foreign_key'] = $productId;
		$entity = $this->newEntity($data);
		return $this->save($data);
	}
	public function uploadDocument($productId, $data) {
		$data['adapter'] = 'Local';
		$data['model'] = 'ProductDocument',
		$data['foreign_key'] = $productId;
		$entity = $this->newEntity($data);
		return $this->save($data);
	}
}
```

Products Controller
-------------------

```php
class ProductsController extends ApController {
	// Upload an image
	public function upload($productId = null) {
		if (!$this->request->is('get')) {
			if ($this->Products->Images->upload($productId, $this->request->data)) {
				$this->Session->set(__('Upload successful!');
			}
		}
	}
}
```

Products View
-------------

```php
echo $this->Form->create($productImage, array(
	'type' => 'file'
));
echo $this->Form->file('file');
echo $this->Form->error('file');
echo $this->Form->submit(__('Upload'));
echo $this->Form->end();
```