# Change Log

## 0.1.5 - 2017-02-16

### Fixed

- Performance improvements by avoid using `uniqid()`. 

## 0.1.5 - 2017-02-14

### Fixed

- Support for non-readable streams. This fix was needed because flaws in Guzzle, Zend and Slims implementations of PSR-7. 

## 0.1.4 - 2016-12-31

### Added

- Added support for resetting the builder

## 0.1.3 - 2016-12-22

### Added

- Added `CustomMimetypeHelper` to allow you to configure custom mimetypes. 

### Changed

- Using regular expression instead of `basename($filename)` because basename is depending on locale.

## 0.1.2 - 2016-08-31

### Added

- Support for Outlook msg files. 

## 0.1.1 - 2016-08-10

### Added

- Support for Apple passbook. 

## 0.1.0 - 2016-07-19

### Added

- Initial release
