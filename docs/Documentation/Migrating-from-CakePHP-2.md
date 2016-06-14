# Migrating from CakePHP 2

Here is a list of things that have changed:

* The plugin doesn't any longer use the configuration namespace `Media` but instead uses now the more appropriate namespace `FileStorage`.
* The plugin is not using the CakeDC Migrations plugin any more but [the official CakePHP Migrations plugin](https://github.com/cakephp/migrations).
* `Lib\Utility\StorageUtils` has been moved to `Storage\StorageUtils`.
* `FileStorageTable::fileExtension()` has been removed, use `pathinfo($path, PATHINFO_EXTENSION)` instead.
* `FileStorageTable::stripUuid()` has been removed, use events to handle the file saving and `AbstractStorageEventListener::stripDashes()`.
* `FileStorageTable::tmpFile()` has been removed, use events to handle the file saving and `AbstractStorageEventListener::createTmpFile()`.
* `FileStorageTable::tmpFile()` has been moved to `AbstractStorageEventListener::fsPath()`, use events to handle the file saving.
* `ImageStorageTable::hashOperations()` has been removed, use `StorageUtils::hashOperations()`.
* `ImageStorageTable::generateHashes()` has been removed, use `StorageUtils::generateHashes()`.
* `ImageStorageTable::ksortRecursive()` has been removed, use `StorageUtils::ksortRecursive()`.
* Former `UploadValidatorBehavior::uploadArray()` has been moved to `StorageUtils::uploadArray()`.
