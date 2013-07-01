# FileStorage plugin for CakePHP 2.x

This plugin is giving you the possibility to store files in virtually and kind of storage backend. This plugin is wrapping the Gaufrette library (https://github.com/KnpLabs/Gaufrette) library in a CakePHP fashion and provides a simple way to use the storage adapters through the StorageManager class.

Storage adapters are an unified interface that allow you to store file data to your local file system, in memory, in a database or into a zip file and remote systems. There is a database table keeping track of what you stored were.

Included storage adapters through the Gaufrette vendor lib are:

 * Local File System
 * Amazon S3
 * ACL Aware Amazon S3
 * Mogile FS
 * Rackspace Cloudfiles
 * Zip File
 * Ftp
 * Sftp
 * In Memory
 * Grid FS
 * Apc
 * Doctrine DBAL

You can always write your own adaper or extend and overload existing ones.

## Requirements

 * CakePHP 2.x
 * PHP 5.3+
 * Gaufrette Library (included as git submodule, just init it)
 * CakeDC Imagine Image processing plugin https://github.com/cakedc/imagine if you want to process and storage images

## Installation

To be able to simply autoload Gaufrette load the plugin with bootstrap enabled. The bootstrap file will register the SPL classloader.

	CakePlugin::load('FileStorage', array('bootstrap' => true));

You also need to setup the plugin database using either the schema shell:

    cake schema create --plugin FileStorage

or the CakeDC Migrations plugin (http://github.com/CakeDC/migrations):

    cake Migrations.migration run all --plugin FileStorage

This plugin depends on the Gaufrette library (https://github.com/KnpLabs/Gaufrette), init the submodule, the plugin depends on it.

	git submodule update --init

If you want to use S3 upload Gaufrette has also submodules to initialize. Here is the whole story to get everything initialized:

	cd YOUR-APP-FOLDER
	git submodule add git://github.com/burzum/FileStorage.git Plugin/FileStorage
	git submodule update --init --recursive

If you do not want to add it as submodule just clone it instead of doing submodule add

	git clone git://github.com/burzum/FileStorage.git

and follow the rest of the steps

## Usage

### StorageManager

To configure adapters use the StorageManager::config method. First argument is the name of the config, second an array of options for that adapter

```php
StorageManager::config('Local', array(
	'adapterOptions' => array(TMP, true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem'));
````

To invoke a new instance using a before set configuration call:

	$Adapter = StorageManager::adapter('Local');

You can also call the adapter instances methods like this

```php
StorageManager::adapter('Local')->write($key, $data);
```

Alternativly you can pass a config array as first argument to get an instance using these settings that is not in the configuration.

To delete configs and by this the instance from the StorageManager call

```php
StorageManager::flush('Local');
```

If you want to flush *all* adapter configs and instances simply call it without the first argument.

### How to store an uploaded file

The basic idea of this plugin is that files are always handled as separate entities and are associated to other models.

So for example let's say you have a Report model and want to save a pdf to it, you would then create an association lile:

```php
public $hasOne = array(
	'PdfFile' => array(
		'className' => 'FileStorage.FileStorage',
		'foreignKey' => 'foreign_key'));
```

In your add/edit report you would have something like:

```php
echo $this->Form->input('Report.title');
echo $this->Form->input('PdfFile.file');
echo $this->Form->input('Report.description');
```

#### Now comes the crucial point of the whole implementation:

Because of to many different requirements and personal preferences out there the plugin is *not* automatically storing the file. You'll have to customize it a little but its just a matter for a few lines.

Lets go by this scenario inside the report model, assuming there is an add() method:

```php
$this->create();
if ($this->save($data)) {
	$key = 'your-file-name';
	if (StorageManager::adapter('Local')->write($key, file_get_contents($this->data['PdfFile']['file']['tmp_name']))) {
		$this->data['PdfFile']['foreign_key'] = $this->getLastInsertId();
		$this->data['PdfFile']['model'] = 'Report';
		$this->data['PdfFile']['path'] = $key;
		$this->data['PdfFile']['adapter'] = 'Local';
	}
}
```

Later, when you want to delete the file, for example in the beforeDelete() or afterDelete() callback of your Report model, you'll know the adapter you have used to store the attached PdfFile and can get an instance of this adapter configuration using the StorageManager. By having the path or key available you can then simply call:

```php
StorageManager::adapter($data['PdfFile']['adapter'])->delete($data['PdfFile']['path']);
```

Insted of doing all of this in the model that has the files associated to it you can also simply extend the FileStorage model from the plugin and add your storage logic there and use that model for your association.

#### How to store an uploaded file II - using Events

The plugin comes with a class that acts just as a listener to some of the events in this plugin. Take a look at Filestorage/Event/LocalImageProcessingLister.php

This class will listen to all the ImageStorage events and save the uploaded image and then create the versions for that image and storage adapter.

It is important to understand that each storage adapter requires a different handling. You can not threat a local file the same as a file you store in a cloud service. The interface that this plugin and Gaufrette provide is the same but not the internals.

So if you want to store a file using Amazon S3 you would have to store it, create all the versions of that image locally and then upload each of them and then delete the local temp files.

When you create a new listener it is important that you check the model field and the event subject object if it matches what you expect. Using the event system you could create any kind of storage and upload behavior without inheriting or touching the model code. Just write a listener class and attach it to the global CakeEventManager.

#### List of events

 * ImageVersion.createVersion
 * ImageVersion.removeVersion
 * ImageStorage.beforeSave
 * ImageStorage.afterSave
 * ImageStorage.beforeDelete
 * ImageStorage.afterDelete
 * FileStorage.beforeSave
 * FileStorage.afterSave
 * FileStorage.afterDelete

#### Why is it done like this? 

Because every developer might want to store the file at a different point or apply other operations on the file before or after it is store. Based on different circumstances you might want to save an associated file even before you created the record its going to get attached to, in other scenarios like in this documentation you want to do it after.

The $key is also a key aspect of it: Different adapters might expect a different key. A key for the Local adapter of Gaufrette is usally a path and a file name under which the data gets stored. Thats also the reason why you use `file_get_contents()` instead of simply passing the tmp path as its.

It is up to you how you want to generate the key and build your path.

I recommend you to read the Gaufrette documentation for the read() and write() methods of the adapters.

### Image Versioning

You can set up automatic image processing for the FileStorage.Image model. To make the magic happen you have to use the Image model (it extends the FileStorage model) for image file saving.

All you need to do is basically use the image model and configure versions on a per model basis. When you save an Image model record it is important to have the 'model' field filled so that the script can find the correct versions for that model.

```php
Configure::write('Media', array(
	'imageSizes' => array(
		'GalleryImage' => array(
			'c50' => array(
				'crop' => array(
					'width' => 50, 'height' => 50)),
			't120' => array(
				'thumbnail' => array(
					'width' => 120, 'height' => 120)),
			't800' => array(
				'thumbnail' => array(
					'width' => 800, 'height' => 600))),
		'User' => array(
			'c50' => array(
				'crop' => array(
					'width' => 50, 'height' => 50)),
			't150' => array(
				'crop' => array(
					'width' => 150, 'height' => 150))),
		)
	)
);
App::uses('ClassRegistry', 'Utility');
ClassRegistry::init('FileStorage.Image')->generateHashes();
```

Calling generateHashes is important, it will create the hash values for each versioned image and store them in Media.imageHashes in the configuration.

If you do not want to have the script to generate the hashes each time its execute it is up to you to store it persistant. This plugin just provides you the tools.

Image files will end up wherever you have configured your base path 

	/ModelName/51/21/63/4c0f128f91fc48749662761d407888cc/4c0f128f91fc48749662761d407888cc.jpg

The versioned image files will be in the same folder, which is the id of the record, as the original image and have the truncated hash of the version attached but before the extension.

	/ModelName/51/21/63/4c0f128f91fc48749662761d407888cc/4c0f128f91fc48749662761d407888cc.f91fsc.jpg

You should smylink your image root folder to APP/webroot/images for example to avoid that images go through php and are send directly instead.

#### Extending and changing image versioning

It is possible to totally change the way image versions are created.

## Specific Addapter Configuration

Gaufrette does not come with a lot detail about what exactly some adapters expect so here is a list to help you with that.

But you should not blindly copy and paste that code, get an understanding of the storage service you want to use before!

### OpenCloud (Rackspace)

Get the SDK from here http://github.com/rackspace/php-opencloud and add it to your class autoloader

```php
define('RAXSDK_SSL_VERIFYHOST', 0);
define('RAXSDK_SSL_VERIFYPEER', 0);

$connection = new \OpenCloud\Rackspace(
	'https://lon.identity.api.rackspacecloud.com/v2.0/', // Rackspace Auth URL
	array(
		'username' => 'YOUR-USERNAME',
		'apiKey' => 'YOUR-API-KEY'
	)
);

// LON (London) or DFW (Dallas)
$objstore = $connection->ObjectStore('cloudFiles', 'LON');

StorageManager::config('OpenCloudTest', array(
	'adapterOptions' => array(
		$objstore,
		'test1',
	),
	'adapterClass' => '\Gaufrette\Adapter\OpenCloud',
	'class' => '\Gaufrette\Filesystem')
);
```

### AmazonS3

Get the SDK from here http:// github.com/amazonwebservices/aws-sdk-for-php and load the sdk.class.php file from where ever you cloned the SDK.

```php
require_once(APP . 'Vendor' . DS . 'AwsSdk' . DS . 'sdk.class.php');
CFCredentials::set(array(
	'production' => array(
		'certificate_authority' => true,
		'key' => 'YOUR-KEY',
		'secret' => 'YOUR-SECRET')
	)
);
$s3 = new AmazonS3();

StorageManager::config('S3', array(
	'adapterOptions' => array(
		$s3,
		'YOUR-BUCKET-HERE'),
	'adapterClass' => '\Gaufrette\Adapter\AmazonS3',
	'class' => '\Gaufrette\Filesystem'));
```

## Included Event Listeners

### LocalFileStorageListener

The file and folder structure it will generate looks like that:

	basePath/files/xx/xx/xx/<uuid>/<uuid>.<extension>

### ImageProcessingListener

This listener will create versions of images if Configure::read('Media.imageSizes.' . $model); is not empty. If no processing operations for that model were specified it will just save the image.

This adapter replaces LocalImageProcessingListener and currently supports the Local and AmazonS3 adapter.

The file and folder structure it will generate looks like that:

	basePath/images/xx/xx/xx/<uuid>/<uuid>.<extension>

Versioned images will look like that

	basePath/images/xx/xx/xx/<uuid>/<uuid>.<hash>.<extension>

 * For the Local adapter basePath is the value from Configure::read('Media.basePath').
 * For AmazonS3 the basePath will be the bucket and Amazon S3 URL prefix.

xx is a semi random alphanumerical value calculated based on the given file name if the Local adapter was used

#### Important notes about the path the processor generates

The path stored to the db is NOT going to be the complete path it won't add the filename for a reason.

The filename is generated by the processor on the fly when adding/deleting/modifying images because the versions are built on the fly and not stored to the database. See ImageProcessingListener::_buildPath().

### LocalImageProcessingListener (DEPRECATED, use ImageProcessingListener)

This adapter is still around for backward compatibility to not break some projects depending on it.

This listener will create versions of images if Configure::read('Media.imageSizes.' . $model); is not empty. If no processing operations for that model were specified it will just save the image.

The file and folder structure it will generate looks like that:

	basePath/images/xx/xx/xx/<uuid>/<uuid>.<extension>

Versioned images will look like that

	basePath/images/xx/xx/xx/<uuid>/<uuid>.<hash>.<extension>

basePath is the value from Configure::read('Media.basePath'), xx is a semi random alphanumerical value calculated based on the given file name.

## Support

For support and feature request, please visit the FileStorage issue page

https://github.com/burzum/FileStorage/issues

## Contributions

Please send pull request to `develop` branch.

## License

Copyright 2012, Florian Krämer

Licensed under The MIT License
Redistributions of files must retain the above copyright notice.

## Credits

Thanks to Larry Masters and the CakeDC for the chance to work with a great team and Jitka Koukalová for her excelent advice in programming related questions she gave me some years ago. You guys and girls made me a better programmer! :)
