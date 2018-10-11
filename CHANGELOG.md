# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/burzum/cakephp-file-storage/compare/2.0.0-rc1...2.0)
### Added

### Changed

### Fixed

## [2.0.0](https://github.com/burzum/cakephp-file-storage/releases/tag/2.0.0) - 2018-09-07
### Changed
- Added this change log
- Improved documentation

## [2.0.0-rc1](https://github.com/burzum/cakephp-file-storage/releases/tag/2.0.0-rc1) - 2018-09-07
### Changed
- Updated dependencies

## [2.0.0-beta2](https://github.com/burzum/cakephp-file-storage/releases/tag/2.0.0-beta2) - 2018-09-07
### Changed
- Upgraded to CakePHP 3.6
- Removed upload validation methods as they are part of the CakePHP core
- Increased the length of the extension field in the DB #157

## [2.0.0-beta1](https://github.com/burzum/cakephp-file-storage/releases/tag/2.0.0-beta1) - 2017-11-25
### Added
- Added Flysystem support to StorageManager
- Added pre- and post processing callbacks for image processing

### Changed
- Updated CI
- Updated dependencies
- Improved documentation
- Removed `UploadValidationBehavior`
- Removed the `ImageStorageTable` class
- Removed the `ImageStorage.beforeSave` event
- Removed the `ImageStorage.afterSave` event
- Removed the `ImageStorage.beforeDelete` event
- Removed the `ImageStorage.afterDelete` event
- Removed deprecated method calls
- `FileStorage::deleteOldFileOnSave()` is no longer called automatically in the `FileStorage::afterSave()` callback
- Renamed the DB field `file_storage.model` to `file_storage.identifier`
- Renamed The DB field `file_storage.adapter` to `file_storage.adapter_config`
- Refactored image processing

### Fixed
 - Fixing a bug in StorageManager
 - Fixed tests

## [1.2.1](https://github.com/burzum/cakephp-file-storage/releases/tag/1.2.1) - 2017-02-28
### Changed
- Refactored the shells

### Fixed
- Fixed issue with image version shell
- Fixed auto-rotating photos based on exif data #142

## [1.2.0](https://github.com/burzum/cakephp-file-storage/releases/tag/1.2.0) - 2017-02-16
### Changed
- Removed passed image parameter for auto rotate
- Updated documentation
- Updated access to properties to getters and setters
- Updated dependencies
- Refactored the storage manager

### Fixed
- Fixed passing the subject to the event object in `ImageVersionsTrait`
- Fixed issue with CakePHP 3.4
- Fixed tests

## [1.1.6](https://github.com/burzum/cakephp-file-storage/releases/tag/1.1.6) - 2016-06-14
### Added
- Added `LegacyPathBuilder` to rebuild the cakePHP 2.x version

### Changed
- Removed PHP 5.5 from Travis
- Updated documentation
- Made it possible to use a callable as hash for `randomPath()`

## [1.1.5](https://github.com/burzum/cakephp-file-storage/releases/tag/1.1.5) - 2016-06-13
### Changed
- Updated documentation
- Updated dependencies
- Set the primary key for the `FileStorageTable` instead of auto-detecting it

### Fixed
- Fixed S3 Path Builder
- Fixed "First arg must be a non empty string!" exception #112
- Changed the length of the mime type field #126

## [1.1.4](https://github.com/burzum/cakephp-file-storage/releases/tag/1.1.4) - 2016-01-21
### Added
- Added `BaseListener`
- Introduced a new shell command to store file via command line

### Changed
- Improved the Quick Start Tutorial
- Throw exception if listener can't work with a specific adapter

## [1.1.3](https://github.com/burzum/cakephp-file-storage/releases/tag/1.1.3) - 2016-01-14
### Added
- Added `fileToUploadArray` for uploadArray, kept uploadArray as alias
- Added new events to prepare for future changes

### Changed
- Improved the `BasePathBuilderTest`
- Improved the StorageException throwing

## [1.1.2](https://github.com/burzum/cakephp-file-storage/releases/tag/1.1.2) - 2016-01-11
### Fixed
- Fixed the broken `StorageUtils::normalizeGlobalFilesArray()`

## [1.1.1](https://github.com/burzum/cakephp-file-storage/releases/tag/1.1.1) - 2016-01-06
### Changed
- Updated Documentation
- Improved argument handling of the `StorageTrait`
- Refined the path building
- Improved tests

### Fixed
- Fixed an issue when saving again without adapter and model

## [1.1.0](https://github.com/burzum/cakephp-file-storage/releases/tag/1.1.0) - 2015-12-18
### Added
- PathBuilders have been introduced

### Changed
- The whole image processing system has been refactored
- Everything in `Burzum\FileStorage\Event` has been deprecated
- Everything in `Burzum\FileStorage\Lib` has been deprecated

## [1.0.0](https://github.com/burzum/cakephp-file-storage/releases/tag/1.0.0) - 2015-08-25
### Added
-  Initial stable release
