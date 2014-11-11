Migrating from CakePHP 2
========================

* The plugin does not longer use the configure namespace `Media` but instead uses now the more appropriate namespace `FileStorage`.
* Lib\Utility\FileStorageUtils has been moved to Lib\FileStorageUtils.
* `ImageStorage::hashOperations()`, `ImageStorage::generateHashes()` and `ImageStorage::ksortRecursive()` were moved into the `Lib\FileStorageUtils` class.
* `FileStorageTable::fileExtension()` has been removed, use `pathinfo($path, PATHINFO_EXTENSION)` instead.
* `FileStorageTable::stripUuid()` has been removed, use events to handle the file saving and `AbstractStorageEventListener::stripDashes()`.
* `FileStorageTable::tmpFile()` has been removed, use events to handle the file saving and `AbstractStorageEventListener::createTmpFile()`.
* `FileStorageTable::tmpFile()` has been moved to `AbstractStorageEventListener::fsPath()`, use events to handle the file saving.