# Changelog

All notable changes to this project will be documented in this file,
in reverse chronological order by release.

## [v1.2.1](https://github.com/zaphyr-org/router/compare/1.2.0...1.2.1) [2024-02-09]

### Fixed:

* The `getRoutes` method of the Router class now also takes group routes into account
* Route condition attributes now correctly overwrite group condition attributes

## [v1.2.0](https://github.com/zaphyr-org/router/compare/1.1.2...1.2.0) [2024-02-08]

### New:
* Added `getRoutes` method to Router class
* Added matched route to the request attributes

## [v1.1.2](https://github.com/zaphyr-org/router/compare/1.1.1...1.1.2) [2023-11-11]

### New:
* Added `.vscode/` to gitignore file

### Changed:
* Improved unit tests and moved tests to "Unit" or "Integration" directory

### Removed:
* Removed phpstan-phpunit from composer require-dev

## [v1.1.1](https://github.com/zaphyr-org/router/compare/1.1.0...1.1.1) [2023-10-24]

### Changed:
* Changed visibility to `protected` for `tearDown` and `setUp` methods in unit tests

### Fixed:
* Resolved [#1](https://github.com/zaphyr-org/router/issues/1); `scheme` and `host` parameters in the RouteConditionTrait are set to `null` by default
* Resolved [#2](https://github.com/zaphyr-org/router/issues/2); Moved route condition handling to Dispatcher class

## [v1.1.0](https://github.com/zaphyr-org/router/compare/1.0.0...1.1.0) [2023-10-13]

### New:
* Added `setMiddleware` method and marked `setMiddlewares` method as deprecated

### Fixed:
* Removed .dist from phpunit.xml in .gitattributes export-ignore

### Deprecated:
* Attributes `$middlewares` param will be renamed to `$middleware` in v2.0

## v1.0.0 [2023-09-20]

### New:
* First stable release version
