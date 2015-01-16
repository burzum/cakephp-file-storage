FileStorage Plugin for CakePHP 2.x and 3.x
==========================================

**The 3.0 branch is still in development!** You can help updating the code (it's not that hard) or make a donation if you want to speed things up. The work done on this plugin is done voluntary by me in my *free* time. So please respect that I'm not in a hurry to get it updated ASAP.

---

The **File Storage** plugin is giving you the possibility to store files in virtually any kind of storage backend. This plugin is wrapping the [Gaufrette](https://github.com/KnpLabs/Gaufrette) library in a CakePHP fashion and provides a simple way to use the storage adapters through the [StorageManager](Lib/StorageManager.php) class.

Storage adapters are an unified interface that allow you to store file data to your local file system, in memory, in a database or into a zip file and remote systems. There is a database table keeping track of what you stored where. You can always write your own adapter or extend and overload existing ones.

 * Apc
 * Amazon S3
 * ACL Aware Amazon S3
 * Azure
 * Doctrine DBAL
 * Dropbox
 * Ftp
 * Grid FS
 * In Memory
 * Local File System
 * MogileFS
 * Open Cloud
 * Rackspace Cloudfiles
 * Sftp
 * Zip File

Requirements
------------

 * CakePHP 2.0+
 * PHP 5.3+
 * Gaufrette Library (included as git submodule or composer dependency)

Optional but required for image processing:

 * The [Imagine Image processing plugin](https://github.com/burzum/cakephp-imagine-plugin) if you want to process and storage images.

Documentation
-------------

For documentation, as well as tutorials, see the [docs](docs/Home.md) directory of this repository.

Support
-------

For bugs and feature requests, please use the [issues](https://github.com/burzum/FileStorage/issues) section of this repository.

Contributing
------------

To contribute to this plugin please follow a few basic rules.

* Pull requests must be send to the ```develop``` branch.
* Contributions must follow the [PSR2-**R** coding standard recommendation](https://github.com/php-fig-rectified/fig-rectified-standards).
* [Unit tests](http://book.cakephp.org/2.0/en/development/testing.html) are required.

License
-------

Copyright 2012 - 2015, Florian Krämer

Licensed under The MIT License
Redistributions of files must retain the above copyright notice.

Credits
-------

Thanks to Larry Masters and the [CakeDC](http://cakedc.com) for the chance to work with a great team and Jitka Koukalová for her excellent advice in programming related questions she gave me some years ago. You guys and girls made me a better programmer!
