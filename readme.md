# FileStorage plugin for CakePHP 2.x

I would call the status of this plugin now beta.

The code should be in a usebale state, feel free to play with it and give feedback, I will appreciate it!

I'll improve it further and try to automate it a little more, you will know what I mean if you read the Usage section of this document.

## Requirements

 * CakePHP 2.x
 * PHP 5.3+
 * Gaufrette Library (included as git submodule, just init it)
 * CakeDC Imagine Image processing plugin https://github.com/cakedc/imagine if you want to process and storage images

## Installation

To be able to simply autoload Gaufrette load the plugin with bootstrap enabled. The bootstrap file will register the SPL classloader.

	CakePlugin::load('FileStorage', array('bootstrap' => true));

This plugin depends on the Gaufrette library (https://github.com/KnpLabs/Gaufrette), init the submodule, the plugin depends on it.

	git submodule update --init

If you want to use S3 upload Gaufrette has also submodules to initialize. Here is the whole story to get everything initialized:

	cd YOUR-APP-FOLDER
	git submodule add git://github.com/burzum/FileStorage.git Plugin/FileStorage
	git submodule update --init
	cd Plugin/FileStorage
	git submodule update --init

If you do not want to add it as submodule just clone it instead of doing submodule add

	git clone git://github.com/burzum/FileStorage.git

and follow the rest of the steps

## Usage

### Configuration

You can configure as many file storage adapters as you want with different settings via Configure:

	Configure::write('FileStorage.adapters', array(
		'Local' => array(
			'adapterOptions' => array(TMP, true),
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem')));

The FileStorage model which is using the StorageManager class will auto load them into the StorageManager configuration.

### StorageManager

	StorageManager::config('Local',	array(
		'adapterOptions' => array(TMP, true),
		'adapterClass' => '\Gaufrette\Adapter\Local',
		'class' => '\Gaufrette\Filesystem'));

To invoke a new instance using a configuration call:

	StorageManager::adapter('Local');

Alternativly you can pass a config array as first argument to get an instance using these settings that is not in the configuration.

To delete configs and by this the instance from the StorageManager call

	StorageManager::flush('Local');

If you want to flush *all* adapter configs and instances simply call it without the first argument.

### How to store an uploaded file

The basic idea of this plugin is that files are always handled as separate entities and are associated to other models.

So for example let's say you have a Report model and want to save a pdf to it, you would then create an association lile:

	public $hasOne = array(
		'PdfFile' => array(
			'className' => 'FileStorage.FileStorage',
			'foreignKey' => 'foreign_key'));

In your add/edit report you would have something like:

	echo $this->Form->input('Report.title');
	echo $this->Form->input('PdfFile.file');
	echo $this->Form->input('Report.description');

#### Now comes the crucial point of the whole implementation:

Because of to many different requirements and personal preferences out there the plugin is *not* automatically storing the file. You'll have to customize it a little but its just a matter for a few lines.

Lets go by this scenario inside the report model, assuming there is an add() method:

	$this->create()
	if ($this->save($data)) {
		$key = 'your-file-name';
		if (StorageManager::adapter('Local')->write(, file_get_contents($this->data['PdfFile']['tmp_name']))) {
			$this->data['PdfFile']['foreignKey'] = $this->getLastInsertId();
			$this->data['PdfFile']['model'] = 'Report';
			$this->data['PdfFile']['path'] = $key;
		}
	}

#### Why is it done like this? 

Because every developer might want to store the file at a different point or apply other operations on the file before or after it is store. Based on different circumstances you might want to save an associated file even before you created the record its going to get attached to, in other scenarios like in this documentation you want to do it after.

The $key is also a key aspect of it: Different adapters might expect a different key. A key for the Local adapter of Gaufrette is usally a path and a file name under which the data gets stored. Thats also the reason why you use `file_get_contents()` instead of simply passing the tmp path as its.

It is up to you how you want to generate the key and build your path.

### Image Versioning

You can set up automatic image processing for the FileStorage.Image model. To make the magic happen you have to use the Image model (it extends the FileStorage model) for image file saving.

All you need to do is basically use the image model and configure versions on a per model basis. When you save an Image model record it is important to have the 'model' field filled so that the script can find the correct versions for that model.

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

Calling generateHashes is important, it will create the hash values for each versioned image and store them in Media.imageHashes in the configuration.

If you do not want to have the script generated the hashes each time its execute it is up to you to store it persistant. This plugin just provides you the tools.

Image files will end up wherever you have configured your base path 

	/ModelName/51/21/63/4c0f128f91fc48749662761d407888cc/4c0f128f91fc48749662761d407888cc.jpg

The versioned image files will be in the same folder, which is the id of the record, as the original image and have the truncated hash of the version attached but before the extension.

	/ModelName/51/21/63/4c0f128f91fc48749662761d407888cc/4c0f128f91fc48749662761d407888cc.f91fsc.jpg

You should smylink your image root folder to APP/webroot/images for example to avoid that images go through php and are send directly instead.

## Support

For support and feature request, please visit the FileStorage issue page

https://github.com/burzum/FileStorage/issues

## License

Copyright 2012, Florian Krämer

Licensed under The MIT License
Redistributions of files must retain the above copyright notice.

## Credits

Thanks to Larry Masters and the CakeDC for the chance to work with a great team and Jitka Koukalová for her excelent advice in programming related questions she gave me some years ago. You guys and girls made me a better programmer! :)
