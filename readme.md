# FileStorage plugin for CakePHP 2.x

This is work in progress, the code might and very likely will change for some more time.

The code should be now in a usebale state, feel free to play with it and give feedback, I will appreciate it!

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
