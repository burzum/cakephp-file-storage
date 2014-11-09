Migrating from CakePHP 2
========================

* The plugin does not longer use the configure namespace `Media` but instead uses now the more appropriate namespace `FileStorage`.
* Lib\Utility\FileStorageUtils has been moved to Lib\FileStorageUtils
* ImageStorage::hashOperations(), ImageStorage::generateHashes() and ImageStorage::ksortRecursive() were moved into the Lib\FileStorageUtils class. 