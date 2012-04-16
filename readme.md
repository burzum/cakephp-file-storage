# FileStorage plugin for CakePHP 2.x

This is work in progress, the code might and very likely will change for some more time.

The code should be now in a usebale state, feel free to play with it and give feedback, I will appreciate it!

## Requirements

 * CakePHP 2.x
 * PHP 5.3+
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
	cd Plugin/FileStorage/Vendor/Gaufrette
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

To invoke a new instance using a configuration call:

	$FileStorage->storageAdapter('Local');

Alternativly you can pass a config array as first argument.

### Image Versioning

You can set up automatic image processing for the FileStorage.Image model.

All you need to do is basically use the image model and configure versions on a per model basis. When you save an Image model record it is important to have the 'model' field filled so that the script can find the correct versions for that model.

	Configure::write('Media', array(
		'imageSizes' => array(
			'GalleryImage' => array(
				'c50' => array(
					'crop' => array(
						'width' => 50, 'height' => 50)),
				't120' => array(
					'thumbnail' => array(
						'width' => 120, 'height' => 120))),
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
	ClassRegistry::init('FileStorage.Image')->generateHashes();

Calling generateHashes is important, it will create the hash values for each versioned image and store them in Media.imageHashes in the configuration.

If you do not want to have the script generated the hashes each time its execute it is up to you to store it persistant. This plugin just provides you the tools.

## Support

For support and feature request, please visit the FileStorage issue page

https://github.com/burzum/FileStorage/issues

## License

Copyright 2012, Florian Krämer

Licensed under The MIT License
Redistributions of files must retain the above copyright notice.

## Credits

Thanks to Larry Masters and the CakeDC for the chance to work with a great team and Jitka Koukalová for her excelent advice in programming related questions she gave me some years ago. You guys and girls made me a better programmer! :)