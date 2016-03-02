FileStorage Plugin for CakePHP
==============================

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/travis/burzum/cakephp-file-storage/1.1.svg?style=flat-square)](https://travis-ci.org/burzum/cakephp-file-storage)
[![Coverage Status](https://img.shields.io/coveralls/burzum/cakephp-file-storage/1.1.svg?style=flat-square)](https://coveralls.io/r/burzum/cakephp-file-storage)
[![Code Quality](https://img.shields.io/scrutinizer/g/burzum/cakephp-file-storage/1.1.svg?style=flat-square)](https://coveralls.io/r/burzum/cakephp-file-storage)

**If you're upgrading from CakePHP 2.x please read [the migration guide](docs/Documentation/Migrating-from-CakePHP-2.md).**

The **File Storage** plugin is giving you the possibility to upload and store files in virtually any kind of storage backend. This plugin is wrapping the [Gaufrette](https://github.com/KnpLabs/Gaufrette) library in a CakePHP fashion and provides a simple way to use the storage adapters through the [StorageManager](src/Storage/StorageManager.php) class.

Storage adapters are an unified interface that allow you to store file data to your local file system, in memory, in a database or into a zip file and remote systems. There is a database table keeping track of what you stored where. You can always write your own adapter or extend and overload existing ones.

[Please donate if you like it!](https://pledgie.com/campaigns/29682)
------------------------------

Already thought of how many hours development time this plugin saved you already? It would be *awesome* if you don't mind sharing some of your success by donating a small amount! Thank you.

How it works
------------

The whole plugin is build with clear [Separation of Concerns (SoC)](https://en.wikipedia.org/wiki/Separation_of_concerns) in mind: A file is *always* an entry in the `file_storage` table from the app perspective. The table is the *reference* to the real place of where the file is stored and keeps some meta information like mime type, filename, file hash (optional) and size as well. Storing the path to a file inside an arbitrary table along other data is considered as *bad practice* because it doesn't respect SoC from an architecture perspective but many people do it this way for some reason.

You associate the `file_storage` table with your model using the FileStorage or ImageStorage model from the plugin via hasOne, hasMany or HABTM. When you upload a file you save it to the FileStorage model through the associations, `Documents.file` for example. The FileStorage model dispatches then file storage specific events, the listeners listening to these events process the file and put it in the configured storage backend using adapters for different backends and build the storage path using a path builder class.

List of supported Adapters
--------------------------

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

 * PHP 5.4+
 * CakePHP 3.0
 * Gaufrette Library (included as composer dependency)

Optional but required for image processing:

 * The [Imagine Image processing plugin](https://github.com/burzum/cakephp-imagine-plugin) if you want to process and storag images.

Documentation
-------------

For documentation, as well as tutorials, see the [docs](docs/Home.md) directory of this repository.

Support
-------

For bugs and feature requests, please use the [issues](https://github.com/burzum/cakephp-file-storage/issues) section of this repository.

Contributing
------------

To contribute to this plugin please follow a few basic rules.

* Pull requests must be send to the branch that reflects the version you want to contribute to.
* Contributions must follow the [PSR2-**R** coding standard recommendation](https://github.com/php-fig-rectified/fig-rectified-standards).
* [Unit tests](http://book.cakephp.org/3.0/en/development/testing.html) are required.

License
-------

Copyright 2012 - 2016, Florian Krämer

Licensed under The MIT License
Redistributions of files must retain the above copyright notice.
