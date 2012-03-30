# FileStorage plugin for CakePHP 2.x

This is work in progress!

## Installation

To be able to simple autoload Gaufrette load the plugin with bootstrap enabled. The bootstrap file will register the SPL classloader.

	CakePlugin::load('FileStorage', array('bootstrap' => true));

This plugin depends on the Gaufrette library (https://github.com/KnpLabs/Gaufrette), init the submodule, the plugin depends on it.

	git submodule update --init

## Requirements

 * CakePHP 2.x
 * PHP 5.3+
 * CakeDC Imagine Image processing plugin https://github.com/cakedc/imagine if you want to process and storage images
