Migrating from CakePHP 2
========================

* The plugin doesn't any longer use the configure namespace `Media` but instead uses now the more appropriate namespace `FileStorage`.
* The plugin is not using the CakeDC Migrations plugin any more but the official CakePHP Migrations plugin. However, the CakeDC migration files are left in place and might be supported in the future as well. But the primary choice for migrations is now the offical plugin.
* `Lib\Utility\FileStorageUtils` has been moved to `Lib\FileStorageUtils`.
* `FileStorageTable::fileExtension()` has been removed, use `pathinfo($path, PATHINFO_EXTENSION)` instead.
* `FileStorageTable::stripUuid()` has been removed, use events to handle the file saving and `AbstractStorageEventListener::stripDashes()`.
* `FileStorageTable::tmpFile()` has been removed, use events to handle the file saving and `AbstractStorageEventListener::createTmpFile()`.
* `FileStorageTable::tmpFile()` has been moved to `AbstractStorageEventListener::fsPath()`, use events to handle the file saving.
* `ImageStorageTable::hashOperations()` has been removed, use `FileStorageUtils::hashOperations()`.
* `ImageStorageTable::generateHashes()` has been removed, use `FileStorageUtils::generateHashes()`.
* `ImageStorageTable::ksortRecursive()` has been removed, use `FileStorageUtils::ksortRecursive()`.
* Former `UploadValidatorBehavior::uploadArray()` has been moved to `FileStorageUtils::uploadArray()`.