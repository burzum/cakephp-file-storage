# Changes of the FileStorage plugin

List of changes done to the plugin versions

## 0.3.2

* Fix: Removed model FileStorage::$createVersions property, instead of creating no versions no file at all was saved. As replacement for FileStorage::$createVersions the LocalImageStorageListener won't create any revisions if it can find any configuration for the given model. This caused a notice before and further issues.
* Fix: The event ImageStorage.beforeSave was not triggered
* Fix: StorageManager::config($configName) now returns the correct config instead of always active
* Change: The / that was prepended in the ImageHelper::imageUrl has been move to the LocalImageProcessingListener because the / won't be used by all, mostly external, adapters
* Feature: Adding a new ImageProcessingListener that works with Amazon S3 and Local adapters, it can be pretty simple enhanced, let me know or do a PR with your changes for another adapter

## Change log before 0.3.2

Not available.
