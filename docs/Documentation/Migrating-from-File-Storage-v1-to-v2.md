# Migrating from File Storage Version 1.x to Version 2.x

## List of changes

* The `ImageStorageTable` class has been removed.
* The `ImageStorage.beforeSave` event has been removed.
* The `ImageStorage.afterSave` event has been removed.
* The `ImageStorage.beforeDelete` event has been removed.
* The `ImageStorage.afterDelete` event has been removed.
* `FileStorage::deleteOldFileOnSave()` is no longer called automatically in the `FileStorage::afterSave()` callback
* The DB field `file_storage.model` has been renamed to `file_storage.identifier`.
* The DB field `file_storage.adapter` has been renamed to `file_storage.adapter_config`.

## Image Processing

The image processing has been completely refactored into the new pre- and post processing callbacks.

## Storage system
