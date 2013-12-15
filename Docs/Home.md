Home
====

The **File Storage** plugin is giving you the possibility to store files in virtually and kind of storage backend. This plugin is wrapping the Gaufrette library (https://github.com/KnpLabs/Gaufrette) library in a CakePHP fashion and provides a simple way to use the storage adapters through the ```StorageManager``` class.

Storage adapters are an unified interface that allow you to store file data to your local file system, in memory, in a database or into a zip file and remote systems. There is a database table keeping track of what you stored were.

[See this list of included storage adapters.](Docs/Documentation/List-of-included-Adapters.md)

You can always write your own adapter or extend and overload existing ones.

Requirements
------------

 * CakePHP 2.0+
 * PHP 5.3+
 * Gaufrette Library (included as git submodule or composer dependency)

Optional but required for image processing:

 * CakeDC Imagine Image processing plugin https://github.com/cakedc/imagine if you want to process and storage images

Documentation
-------------

* [Installation](Documentation/Installation.md)
* [Database Setup](Documentation/Database-Setup.md)
* [How to Use it](Documentation/How-To-Use.md)
* [Image Storage and Versioning](Documentation/Image-Storage-And-Versioning.md)
* [Specific Adapter Configurations](Documentation/Specific-Adapter-Configurations.md)

Tutorials
---------

* [Quick Start](Tutorials/Quick-Start.md)
