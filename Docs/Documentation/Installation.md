Installation
============

Using Git
---------

Go into your project folders root and add the submodule.

	git submodule add git://github.com/burzum/FileStorage.git Plugin/FileStorage

This plugin depends on the Gaufrette library (https://github.com/KnpLabs/Gaufrette), init the submodule, the plugin depends on it.

	cd app/Plugin/FileStorage
	git submodule update --init

If you want to use S3 upload Gaufrette has also submodules to initialize. Here is the whole story to get everything initialized:

	cd YOUR-APP-FOLDER
	git submodule add git://github.com/burzum/FileStorage.git Plugin/FileStorage
	git submodule update --init --recursive

If you do not want to add it as submodule just clone it instead of doing submodule add

	cd app/Plugin/FileStorage
	git clone git://github.com/burzum/FileStorage.git

It is **not** recommended to just clone it but instead setting it up as submodule.

Using Composer
--------------

Assuming your app folder is called app add this to your projects root folder in composer.js.

```js
{
	"config": {
		"vendor-dir": "app/Vendor/",
		"preferred-install": "source"
	},
	"require": {
		"burzum/FileStorage": "master",
		"knplabs/gaufrette": "*"
	},
	"extra": {
		"installer-paths": {
			"app/Plugin/FileStorage": ["burzum/FileStorage"],
		}
	}
}
```

CakePHP Bootstrap
-----------------

To be able to simply autoload Gaufrette load the plugin with bootstrap enabled. The bootstrap file will register the SPL classloader.

	CakePlugin::load('FileStorage', array('bootstrap' => true));
