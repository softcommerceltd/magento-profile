## Changelog

### Version 1.4.4
- **Enhancement**: Applied a fix to profile menu items on listing pages.

### Version 1.4.3
- **Enhancement**: Added profile config data to event dispatcher for save before/after events.

### Version 1.4.2
- **Enhancement**: Add an option to include a menu with item that depends on URL and confirmation message for profile actions button [#25]

### Version 1.4.1
- **Enhancement**: Preserve an array key for context services in `SoftCommerce\Profile\Model\ServiceAbstract\Service::initServices` [#4]

### Version 1.4.0
- **Feature**: Introduced functionality to support UI form scoped data [#3]
- **Compatibility**: Introduced support for PHP 8.3 [#2]

### Version 1.3.6
- **Compatibility**: Apply a fix to compilation errors for modules that use type declaration with union types. [#11] https://github.com/softcommerceltd/mage2plenty-os/issues/11

### Version 1.3.5
- **Enhancement**: General code improvements.

### Version 1.3.4
- **Compatibility**: Add compatibility for Magento 2.4.6-p3 and Magento 2.4.7

### Version 1.3.3
- **Enhancement**: Applied changes to the styles for message text colours. [#1]

### Version 1.3.2
- **Compatibility**: Add compatibility for PHP 8.2 and Magento 2.4.6-p1

### Version 1.3.1
- **Enhancement**: Added process validation chain to allow simpler profile process interception.

### Version 1.3.0
- **Enhancement**: Added an option to enable / disable history logging per profile.
- **Enhancement**: [M2P-10] Added a performance improvement to profile history where messages are now saved in batches.

### Version 1.2.9
- **Enhancement**: Moved mass status action for schedules from `SoftCommerce_Profile` to `SoftCommerce_ProfileSchedule`

### Version 1.2.8
- **Compatibility**: Compatibility with Magento [OS/AC] 2.4.5 and PHP 8

### Version 1.2.7
- **Fix**: Applied a fix to `isDataSerialized` UI select argument, where element type select failed to retrieve values for serialised data type.

### Version 1.2.6
- **Enhancement**: Added new event `softcommerce_profile_config_save_before` to profile save action.

### Version 1.2.5
- **Enhancement**: Improvements to ACL rules.

### Version 1.2.4
- **Compatibility**: Compatibility with Magento Extension Quality Program (EQP).

### Version 1.2.3
- **Enhancement**: Changes to PDT.

### Version 1.2.2
- **Enhancement**: Added ability to change profile schedule within profile list page.

### Version 1.2.1
- **Compatibility**: Compatibility with PHP 8.x

### Version 1.2.0
- **Compatibility**: Compatibility with Magento Open Source 2.4.4 [#4]

### Version 1.0.1
- **Feature**: New module to handle Plenty Log services. [#3]
- **Compatibility**: Compatibility with Magento Open Source 2.3.5 - 2.4.3 [#2]
- **Enhancement**: Integration Tests [#1]

### Version 1.0.0
- **Feature**: [SCP-1] New module to handle multiple profile entities.
