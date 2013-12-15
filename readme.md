FileStorage plugin for CakePHP 2.x
==================================

The **File Storage** plugin is giving you the possibility to store files in virtually and kind of storage backend. This plugin is wrapping the Gaufrette library (https://github.com/KnpLabs/Gaufrette) library in a CakePHP fashion and provides a simple way to use the storage adapters through the ```StorageManager``` class.

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

You can always write your own adapter or extend and overload existing ones.

[![Downloads](https://poser.pugx.org/burzum/FileStorage/d/total.png)](https://packagist.org/packages/CakeDC/search)
[![Latest Version](https://poser.pugx.org/burzum/FileStorage/v/stable.png)](https://packagist.org/packages/CakeDC/search)

Requirements
------------

 * CakePHP 2.0+
 * PHP 5.3+
 * Gaufrette Library (included as git submodule, just init it)
 * CakeDC Imagine Image processing plugin https://github.com/cakedc/imagine if you want to process and storage images

Documentation
-------------

For documentation, as well as tutorials, see the [Docs](Docs/Home.md) directory of this repository.

Support
-------

For bugs and feature requests, please use the [issues](https://github.com/burzum/FileStorage/issues) section of this repository.

## Support

For support and feature request, please visit the FileStorage issue page

https://github.com/burzum/FileStorage/issues

Contributing
------------

To contribute to this plugin please follow a few basic rules.

* Pull rewuests must be send to the ```develop``` branch.
* Contributions must follow the CakePHP coding standard.
* Unit tests are required.

License
-------

Copyright 2012 - 2014, Florian Krämer

Licensed under The MIT License
Redistributions of files must retain the above copyright notice.

Credits
-------

Thanks to Larry Masters and the CakeDC for the chance to work with a great team and Jitka Koukalová for her excelent advice in programming related questions she gave me some years ago. You guys and girls made me a better programmer! :)
