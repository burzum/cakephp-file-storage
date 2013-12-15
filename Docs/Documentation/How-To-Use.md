How to Use
==========

The StorageManager
------------------

To configure adapters use the ```StorageManager::config()``` method. First argument is the name of the config, second an array of options for that adapter. The options array keys can be different for each adapter, depending on the storage system it connects to.

```php
StorageManager::config('Local', array(
	'adapterOptions' => array(TMP, true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem')
);
````

To invoke a new instance using a before set configuration call.

```php
$Adapter = StorageManager::adapter('Local');
```

You can also call the adapter instances methods like this

```php
StorageManager::adapter('Local')->write($key, $data);
```

Alternatively you can pass a config array as first argument to get an instance using these settings that is not in the configuration.

To delete configs and by this the instance from the StorageManager call

```php
StorageManager::flush('Local');
```

If you want to flush *all* adapter configs and instances simply call it without the first argument.

How to Store an Uploaded File
-----------------------------

The basic idea of this plugin is that files are always handled as separate entities and are associated to other models.

So for example let's say you have a Report model and want to save a pdf to it, you would then create an association lile:

```php
public $hasOne = array(
	'PdfFile' => array(
		'className' => 'FileStorage.FileStorage',
		'foreignKey' => 'foreign_key'
	)
);
```

In your add.ctp or edit.ctp views you would add something like:

```php
echo $this->Form->input('Report.title');
echo $this->Form->input('PdfFile.file');
echo $this->Form->input('Report.description');
```

Now comes the crucial point of the whole implementation
-------------------------------------------------------

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

How to store an uploaded file II - using Events
-----------------------------------------------

The **FileStorage** plugin comes with a class that acts just as a listener to some of the events in this plugin. Take a look at Filestorage/Event/LocalImageProcessingLister.php

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

Important notes about the path the processor generates
------------------------------------------------------

The path stored to the db is NOT going to be the complete path it won't add the filename for a reason.

The filename is generated by the processor on the fly when adding/deleting/modifying images because the versions are built on the fly and not stored to the database. See ImageProcessingListener::_buildPath().

LocalImageProcessingListener **(DEPRECATED, use ImageProcessingListener)**
--------------------------------------------------------------------------

This adapter is still around for backward compatibility to not break some projects depending on it.

This listener will create versions of images if Configure::read('Media.imageSizes.' . $model); is not empty. If no processing operations for that model were specified it will just save the image.

The file and folder structure it will generate looks like that:

	basePath/images/xx/xx/xx/<uuid>/<uuid>.<extension>

Versioned images will look like that

	basePath/images/xx/xx/xx/<uuid>/<uuid>.<hash>.<extension>

basePath is the value from Configure::read('Media.basePath'), xx is a semi random alphanumerical value calculated based on the given file name.
