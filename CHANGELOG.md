# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.4.5] - 2024-05-13
### Changed
- **Enhancement**: Included `servicepoint` type to account for `pakshop` facility.

## [1.4.4] - 2024-05-13
### Changed
- **Enhancement**: Applied a fix to profile menu items on listing pages.

## [1.4.3] - 2024-05-13
### Changed
- **Enhancement**: Added profile config data to event dispatcher for save before/after events.

## [1.4.2] - 2024-05-13
### Changed
- **Enhancement**: Add an option to include a menu with item that depends on URL and confirmation message for profile actions button [#25]

## [1.4.1] - 2024-05-13
### Changed
- **Enhancement**: Preserve an array key for context services in `SoftCommerce\Profile\Model\ServiceAbstract\Service::initServices` [#4]

## [1.4.0] - 2024-03-21
### Added
- Introduced functionality to support UI form scoped data [#3]
### Changed
- **Compatibility**: Introduced support for PHP 8.3 [#2]

## [1.3.6] - 2024-02-19
### Changed
- **Compatibility**: Apply a fix to compilation errors for modules that use type declaration with union types. [#11] https://github.com/softcommerceltd/mage2plenty-os/issues/11

## [1.3.5] - 2023-12-11
### Changed
- **Enhancement**: General code improvements.

## [1.3.4] - 2023-11-30
### Changed
- **Compatibility**: Add compatibility for Magento 2.4.6-p3 and Magento 2.4.7

## [1.3.3] - 2023-09-17
### Changed
- **Enhancement**: Applied changes to the styles for message text colours. [#1]

## [1.3.2] - 2023-06-24
### Changed
- **Compatibility**: Add compatibility for PHP 8.2 and Magento 2.4.6-p1

## [1.3.1] - 2023-02-12
### Changed
- **Enhancement**: Added process validation chain to allow simpler profile process interception.

## [1.3.0] - 2022-12-31
### Changed
- **Enhancement**: Added an option to enable / disable history logging per profile.
- **Enhancement**: [M2P-10] Added a performance improvement to profile history where messages are now saved in batches.

## [1.2.9] - 2022-11-28
### Changed
- **Enhancement**: Moved mass status action for schedules from `SoftCommerce_Profile` to `SoftCommerce_ProfileSchedule`

## [1.2.8] - 2022-11-10
### Changed
- **Compatibility**: Compatibility with Magento [OS/AC] 2.4.5 and PHP 8

## [1.2.7] - 2022-08-30
### Fixed
- Applied a fix to `isDataSerialized` UI select argument, where element type select failed to retrieve values for serialised data type.

## [1.2.6] - 2022-08-22
### Changed
- **Enhancement**: Added new event `softcommerce_profile_config_save_before` to profile save action.

## [1.2.5] - 2022-08-16
### Changed
- **Enhancement**: Improvements to ACL rules.

## [1.2.4] - 2022-07-22
### Changed
- **Compatibility**: Compatibility with Magento Extension Quality Program (EQP).

## [1.2.3] - 2022-07-03
### Changed
- **Enhancement**: Changes to PDT.

## [1.2.2] - 2022-06-25
### Changed
- **Enhancement**: Added ability to change profile schedule within profile list page.

## [1.2.1] - 2022-06-12
### Changed
- **Compatibility**: Compatibility with PHP 8.x

## [1.2.0] - 2022-06-08
### Changed
- **Compatibility**: Compatibility with Magento Open Source 2.4.4 [#4]

## [1.0.1] - 2022-06-10
### Added
- New module to handle Plenty Log services. [#3]
### Changed
- **Compatibility**: Compatibility with Magento Open Source 2.3.5 - 2.4.3 [#2]
- **Enhancement**: Integration Tests [#1]

## [1.0.0] - 2022-06-03
### Added
- [SCP-1] New module to handle multiple profile entities.

[Unreleased]: https://github.com/softcommerceltd/magento-profile/compare/v1.4.5...HEAD
[1.4.5]: https://github.com/softcommerceltd/magento-profile/compare/v1.4.4...v1.4.5
[1.4.4]: https://github.com/softcommerceltd/magento-profile/compare/v1.4.3...v1.4.4
[1.4.3]: https://github.com/softcommerceltd/magento-profile/compare/v1.4.2...v1.4.3
[1.4.2]: https://github.com/softcommerceltd/magento-profile/compare/v1.4.1...v1.4.2
[1.4.1]: https://github.com/softcommerceltd/magento-profile/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/softcommerceltd/magento-profile/compare/v1.3.6...v1.4.0
[1.3.6]: https://github.com/softcommerceltd/magento-profile/compare/v1.3.5...v1.3.6
[1.3.5]: https://github.com/softcommerceltd/magento-profile/compare/v1.3.4...v1.3.5
[1.3.4]: https://github.com/softcommerceltd/magento-profile/compare/v1.3.3...v1.3.4
[1.3.3]: https://github.com/softcommerceltd/magento-profile/compare/v1.3.2...v1.3.3
[1.3.2]: https://github.com/softcommerceltd/magento-profile/compare/v1.3.1...v1.3.2
[1.3.1]: https://github.com/softcommerceltd/magento-profile/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.9...v1.3.0
[1.2.9]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.8...v1.2.9
[1.2.8]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.7...v1.2.8
[1.2.7]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.6...v1.2.7
[1.2.6]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.5...v1.2.6
[1.2.5]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.4...v1.2.5
[1.2.4]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.3...v1.2.4
[1.2.3]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.2...v1.2.3
[1.2.2]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/softcommerceltd/magento-profile/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/softcommerceltd/magento-profile/compare/v1.0.1...v1.2.0
[1.0.1]: https://github.com/softcommerceltd/magento-profile/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/softcommerceltd/magento-profile/releases/tag/v1.0.0
