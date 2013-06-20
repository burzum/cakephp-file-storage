# Changes of the FileStorage plugin

List of changes done to the plugin versions

## 0.3.2

* Fix: Removed model FileStorage::$createVersions property, instead of creating no versions no file at all was saved. As replacement for FileStorage::$createVersions the LocalImageStorageListener won't create any revisions if it can find any configuration for the given model. This caused a notice before and further issues.
* Fix: The event ImageStorage.beforeSave was not triggered

## Changes before 0.3.2

Not available.
