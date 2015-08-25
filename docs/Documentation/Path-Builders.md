Path Builders
=============

Path builders are classes that are used to build the storage paths for a file based on the information coming from the `file_storage` table.

A path builder *should but doesn't have to* build a unique path per entity based on all the data available in the entity.

They implement at least these methods:

 * filename
 * path
 * fullPath
 * url
 
Each of them will take a `FileStorage` entity as first argument. Based on that entity it will generate a path depending on the logic implemented in the path builder.

The reason for this is to separate or share, just as needed, the path building logic between different storage systems. For example S3 differs in it's first part of the path, it's using a bucket while locally you usually have something like a base path instead of the bucket. 

If you want to change the way your files are saved extend the `BasePathBuilder` class.

BasePathBuilder
---------------

This is the path builder all other BP's should inherit from. But if you like to write your very own BP you're free to implement it from the ground up but you'll have to use the PathBuilderInterface.

The BasePathBuilder comes with a set of configuration options:

```php
[
	'stripUuid' => true,
	'pathPrefix' => '',
	'pathSuffix' => '',
	'filePrefix' => '',
	'fileSuffix' => '',
	'preserveFilename' => false,
	'preserveExtension' => true,
	'uuidFolder' => true,
	'randomPath' => 'sha1'
]
```