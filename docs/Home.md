Home
====

The **File Storage** plugin is giving you the possibility to store files in virtually any kind of storage backend. This plugin is wrapping the [Gaufrette](https://github.com/KnpLabs/Gaufrette) library in a CakePHP fashion and provides a simple way to use the storage adapters through the [StorageManager](../Lib/StorageManager.php) class.

[See this list of included storage adapters.](Docs/Documentation/List-of-included-Adapters.md)

Storage adapters are an unified interface that allow you to store file data to your local file system, in memory, in a database or into a zip file and remote systems. There is a database table keeping track of what you stored were. You can always write your own adapter or extend and overload existing ones.

Documentation
-------------

* [Requirements](Documentation/Requirements.md)
* [Installation](Documentation/Installation.md)
* [How it works](Documentation/How-it-works.md)
* [How to Use it](Documentation/How-To-Use.md)
* [The Storage Manager](Documentation/The-Storage-Manager.md)
* [Included Event Listeners](Documentation/Included-Event-Listeners.md)
* [Legacy Event Listeners](Documentation/Legacy-Event-Listeners.md)
* [Path Builders](Documentation/Path-Builders.md)
* [Getting a file path and URL](Documentation/Getting-a-File-Path-and-URL.md)
* [Adapter Configurations](Documentation/Specific-Adapter-Configurations.md)
  * [Local Filesystem](Documentation/Specific-Adapter-Configurations.md#local-filesystem)
  * [Amazon S3](Documentation/Specific-Adapter-Configurations.md#amazons3---awss3-adapter)
  * [Amazon S3 (Legacy)](Documentation/Specific-Adapter-Configurations.md#amazons3---amazons3-adapter-legacy)
  * [OpenCloud (Rackspace)](Documentation/Specific-Adapter-Configurations.md#opencloud-rackspace)
  * [Azure](Documentation/Specific-Adapter-Configurations.md#azure)
* Image processing
  * [Image Storage and Versioning](Documentation/Image-Storage-And-Versioning.md)
  * [The Image Version Shell](Documentation/The-Image-Version-Shell.md)
  * [The Image Helper](Documentation/The-Image-Helper.md)

Tutorials
---------

* [Quick Start](Tutorials/Quick-Start.md)
* [Replacing Files](Tutorials/Replacing-Files.md)
