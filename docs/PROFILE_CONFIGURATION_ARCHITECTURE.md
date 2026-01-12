# Profile Configuration Architecture

**Last Updated:** December 26, 2024

## Overview

The `SoftCommerce_Profile` module provides a flexible, extensible configuration architecture for managing profile entities in Magento 2. This system enables domain modules (like item, customer, order, stock) to define their own profile types with custom configuration, while sharing common infrastructure for storage, retrieval, and UI rendering.

## Core Concepts

### Profile Entity

A **profile** is a configurable entity that defines synchronization behavior between Magento and external systems. Each profile has:

- **Entity ID**: Unique identifier
- **Name**: Human-readable profile name
- **Type ID**: Determines the profile's behavior (e.g., `plenty_item_export`, `plenty_customer_import`)
- **Status**: Active/inactive state

**Database Table:** `softcommerce_profile_entity`

```sql
CREATE TABLE softcommerce_profile_entity (
    entity_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    type_id VARCHAR(255),
    status SMALLINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Profile Configuration

Profile configuration is stored per-profile with scope support (default/website/store):

**Database Table:** `softcommerce_profile_config`

```sql
CREATE TABLE softcommerce_profile_config (
    entity_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT,                 -- References profile entity_id
    scope VARCHAR(8) DEFAULT 'default',
    scope_id INT DEFAULT 0,
    path VARCHAR(255),
    value TEXT
);
```

**Key Properties:**
- `parent_id`: Links configuration to a specific profile
- `scope`: Configuration scope (`default`, `website`, `store`)
- `scope_id`: Website or store ID (0 for default scope)
- `path`: Configuration path (e.g., `plenty_item_export/media_config/is_active`)
- `value`: Configuration value (may be serialized for complex data)

---

## Profile Type Definition

Profile types are defined via XML files that follow a schema validation pattern.

### Schema Definition (`profile_type.xsd`)

```xml
<xs:complexType name="profileTypes">
    <xs:attribute name="typeId" type="xs:string" use="required"/>
    <xs:attribute name="label" type="xs:string" use="required"/>
    <xs:attribute name="router" type="xs:string" use="required"/>
    <xs:attribute name="queueRouter" type="xs:string"/>
    <xs:attribute name="crontabGroup" type="xs:string" use="required"/>
</xs:complexType>
```

### Profile Type XML Example (`profile_type.xml`)

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:SoftCommerce_Profile:etc/profile_type.xsd">
    <profile
        typeId="plenty_item_export"
        label="Product Export [PlentyONE]"
        router="plenty_item/export"
        queueRouter="plenty_item/exportQueue"
        crontabGroup="plenty_profile"
    />
    <profile
        typeId="plenty_item_import"
        label="Product Import [PlentyONE]"
        router="plenty_item/import"
        queueRouter="plenty_item/importQueue"
        crontabGroup="plenty_profile"
    />
</config>
```

### Attribute Definitions

| Attribute | Purpose |
|-----------|---------|
| `typeId` | Unique identifier for the profile type. Used in config paths and UI component naming |
| `label` | Display name shown in admin UI |
| `router` | Controller route for profile execution |
| `queueRouter` | Controller route for queue-based execution |
| `crontabGroup` | Cron group for scheduled executions |

---

## Configuration Retrieval Architecture

### Class Hierarchy

```
ConfigInterface (SoftCommerce\Profile\Model\Config)
    └── ConfigModel (Base class for all config models)
            └── MediaConfig (Domain-specific config)
            └── ItemConfig (Domain-specific config)
            └── OrderConfig (Domain-specific config)
            └── ...
```

### ConfigModel Base Class

Location: `SoftCommerce\Profile\Model\Config\ConfigModel`

The `ConfigModel` class provides the foundation for all configuration retrieval:

```php
class ConfigModel extends DataObject implements ConfigInterface
{
    public function getConfig(
        string $xmlPath,
        $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): mixed {
        $indexKey = $this->getIndexKey($xmlPath, $store, $scope);
        if (!$this->hasData($indexKey)) {
            $this->setData(
                $indexKey,
                $this->configScope->get($this->getProfileId(), $xmlPath, $scope, $store)
            );
        }
        return $this->getData($indexKey);
    }

    public function getTypeId(): string
    {
        if (!isset($this->typeIdInMemory[$this->getProfileId()])) {
            $this->typeIdInMemory[$this->getProfileId()] =
                $this->getProfileTypeId->execute($this->getProfileId());
        }
        return $this->typeIdInMemory[$this->getProfileId()];
    }
}
```

### Key Methods

| Method | Purpose |
|--------|---------|
| `getConfig($path)` | Retrieves configuration value for the current profile |
| `getTypeId()` | Returns the profile's type ID (e.g., `plenty_item_export`) |
| `getProfileId()` | Returns the current profile's entity ID |
| `getConfigDataSerialized($path)` | Retrieves and unserializes array/object config values |

---

## The Type ID Pattern

### How `getTypeId()` Enables Shared Interfaces

The `getTypeId()` method is the cornerstone of the shared configuration pattern. It allows **the same config interface** to be used for both import and export profiles, with the type ID automatically differentiating the configuration paths.

### Example: MediaConfigInterface

```php
interface MediaConfigInterface extends ConfigInterface
{
    // Config path constants (without typeId prefix)
    public const XML_PATH_IS_ACTIVE = '/media_config/is_active';
    public const XML_PATH_CAN_DELETE_IMAGES = '/media_config/can_delete_images';

    public function isActive(): bool;
    public function canDeleteProductImageDifference(): bool;
}
```

### Example: MediaConfig Implementation

```php
class MediaConfig extends ConfigModel implements MediaConfigInterface
{
    public function isActive(): bool
    {
        // getTypeId() returns 'plenty_item_export' or 'plenty_item_import'
        // Full path becomes: 'plenty_item_export/media_config/is_active'
        return (bool) $this->getConfig($this->getTypeId() . self::XML_PATH_IS_ACTIVE);
    }

    public function canDeleteProductImageDifference(): bool
    {
        // Full path becomes: 'plenty_item_export/media_config/can_delete_images'
        return (bool) $this->getConfig($this->getTypeId() . self::XML_PATH_CAN_DELETE_IMAGES);
    }
}
```

### How It Works

```
Profile A (type: plenty_item_export)
    └── MediaConfig::isActive()
            └── getConfig('plenty_item_export/media_config/is_active')
                    └── Returns: true

Profile B (type: plenty_item_import)
    └── MediaConfig::isActive()
            └── getConfig('plenty_item_import/media_config/is_active')
                    └── Returns: false
```

**Benefits:**
1. **Code Reuse**: Same class handles both import and export configuration
2. **Type Safety**: Interface contract ensures consistent method signatures
3. **Automatic Path Resolution**: Type ID is resolved at runtime based on profile
4. **Scope Inheritance**: Works with default/website/store scopes

---

## Configuration Scope System

### ConfigScope Class

Location: `SoftCommerce\Profile\Model\Config\ConfigScope`

Handles configuration retrieval with scope hierarchy and caching:

```php
class ConfigScope implements ConfigScopeInterface
{
    public function get(
        int $profileId,
        ?string $path = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ): mixed {
        // Retrieves config with scope fallback
        // store -> website -> default
    }
}
```

### Scope Hierarchy

```
Store (scope_id = store_id)
    └── Website (scope_id = website_id)
            └── Default (scope_id = 0)
```

When retrieving configuration, the system checks:
1. Store-level config (if store scope requested)
2. Website-level config (if website scope or store fallback)
3. Default-level config (always checked as fallback)

### ConfigScopeWriter

Location: `SoftCommerce\Profile\Model\Config\ConfigScopeWriter`

Handles configuration persistence:

```php
class ConfigScopeWriter implements ConfigScopeWriterInterface
{
    public function save(
        int $profileId,
        string $path,
        mixed $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void {
        // Serializes arrays automatically
        // Updates existing or inserts new config
    }
}
```

---

## UI Component Integration

### Form Data Provider

Location: `SoftCommerce\Profile\Ui\DataProvider\Profile\Form\ProfileDataProvider`

The data provider uses a modifier pool pattern for extensibility:

```php
class ProfileDataProvider extends AbstractDataProvider
{
    public function getData(): array
    {
        $this->data = parent::getData();
        $this->generateConfigData();

        // Apply modifiers from pool
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }
}
```

### ProfileConfigData Modifier

Location: `SoftCommerce\Profile\Ui\DataProvider\Profile\Form\Modifier\ProfileConfigData`

This modifier:
1. Loads configuration data for the current profile
2. Applies scope-based visibility (enable/disable fields)
3. Handles "Use Default" checkbox state
4. Deserializes complex data types

```php
class ProfileConfigData extends AbstractModifier
{
    public const DATA_SOURCE = 'profile_config';

    public function modifyData(array $data): array
    {
        $model = $this->registryLocator->getProfile();
        $configData = $this->configScope->get();

        // Map config data to form structure
        $data[$model->getEntityId()][self::DATA_SOURCE] = $configData;

        return $data;
    }
}
```

### UI Component Naming Convention

Form components follow this naming pattern:

```
profile_{typeId}_form.xml
```

Examples:
- `profile_plenty_item_export_form.xml`
- `profile_plenty_item_import_form.xml`
- `profile_plenty_customer_export_form.xml`

### Form Structure

```xml
<form xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">

    <fieldset name="profile_config">
        <fieldset name="media_config">
            <field name="is_active">
                <argument name="data" xsi:type="array">
                    <item name="config" xsi:type="array">
                        <item name="scopeLabel" xsi:type="string">[STORE]</item>
                        <!-- Configuration for the field -->
                    </item>
                </argument>
            </field>
        </fieldset>
    </fieldset>
</form>
```

### Config Path Mapping

The form fieldset/field structure maps directly to config paths:

```
Form: profile_config/media_config/is_active
Path: {typeId}/media_config/is_active
```

---

## Dependency Injection Configuration

### Core Preferences

```xml
<config>
    <!-- Profile Entity -->
    <preference for="SoftCommerce\Profile\Api\Data\ProfileInterface"
                type="SoftCommerce\Profile\Model\Profile"/>
    <preference for="SoftCommerce\Profile\Api\ProfileRepositoryInterface"
                type="SoftCommerce\Profile\Model\ProfileRepository"/>

    <!-- Configuration -->
    <preference for="SoftCommerce\Profile\Model\Config\ConfigScopeInterface"
                type="SoftCommerce\Profile\Model\Config\ConfigScope"/>
    <preference for="SoftCommerce\Profile\Model\Config\ConfigScopeWriterInterface"
                type="SoftCommerce\Profile\Model\Config\ConfigScopeWriter"/>

    <!-- Type Resolution -->
    <preference for="SoftCommerce\Profile\Model\GetProfileTypeIdInterface"
                type="SoftCommerce\Profile\Model\GetProfileTypeId"/>

    <!-- Profile Types Config -->
    <preference for="SoftCommerce\Profile\Model\ProfileTypes\ConfigInterface"
                type="SoftCommerce\Profile\Model\ProfileTypes\Config"/>
</config>
```

### Virtual Type for Profile Type Reader

```xml
<virtualType name="SoftCommerce\Profile\Model\ProfileTypes\Config\Reader\Virtual"
             type="Magento\Framework\Config\Reader\Filesystem">
    <arguments>
        <argument name="converter" xsi:type="object">
            SoftCommerce\Profile\Model\ProfileTypes\Config\Converter
        </argument>
        <argument name="schemaLocator" xsi:type="object">
            SoftCommerce\Profile\Model\ProfileTypes\Config\SchemaLocator\Virtual
        </argument>
        <argument name="fileName" xsi:type="string">profile_type.xml</argument>
    </arguments>
</virtualType>
```

---

## Creating a New Config Type

### Step 1: Define the Interface

```php
namespace Vendor\Module\Model\Config;

use SoftCommerce\Profile\Model\Config\ConfigInterface;

interface MyConfigInterface extends ConfigInterface
{
    public const XML_PATH_MY_OPTION = '/my_config/my_option';
    public const XML_PATH_ANOTHER_OPTION = '/my_config/another_option';

    public function getMyOption(): string;
    public function getAnotherOption(): bool;
}
```

### Step 2: Implement the Config Class

```php
namespace Vendor\Module\Model\Config;

use SoftCommerce\Profile\Model\Config\ConfigModel;

class MyConfig extends ConfigModel implements MyConfigInterface
{
    public function getMyOption(): string
    {
        return (string) $this->getConfig(
            $this->getTypeId() . self::XML_PATH_MY_OPTION
        );
    }

    public function getAnotherOption(): bool
    {
        return (bool) $this->getConfig(
            $this->getTypeId() . self::XML_PATH_ANOTHER_OPTION
        );
    }
}
```

### Step 3: Register via DI

```xml
<config>
    <preference for="Vendor\Module\Model\Config\MyConfigInterface"
                type="Vendor\Module\Model\Config\MyConfig"/>
</config>
```

### Step 4: Add UI Component Fields

```xml
<fieldset name="my_config">
    <settings>
        <label translate="true">My Configuration</label>
    </settings>

    <field name="my_option" formElement="input">
        <argument name="data" xsi:type="array">
            <item name="config" xsi:type="array">
                <item name="scopeLabel" xsi:type="string">[STORE]</item>
            </item>
        </argument>
        <settings>
            <label translate="true">My Option</label>
            <dataType>text</dataType>
            <dataScope>my_option</dataScope>
        </settings>
    </field>
</fieldset>
```

---

## Best Practices

### 1. Always Use Type ID Prefix

```php
// Correct
return $this->getConfig($this->getTypeId() . self::XML_PATH_MY_OPTION);

// Incorrect - will not work correctly
return $this->getConfig(self::XML_PATH_MY_OPTION);
```

### 2. Define Constants for Paths

```php
// Define in interface for clarity and reusability
public const XML_PATH_IS_ACTIVE = '/media_config/is_active';
```

### 3. Use Appropriate Return Types

```php
public function isActive(): bool
{
    return (bool) $this->getConfig($this->getTypeId() . self::XML_PATH_IS_ACTIVE);
}

public function getLimit(): int
{
    return (int) $this->getConfig($this->getTypeId() . self::XML_PATH_LIMIT) ?: 100;
}
```

### 4. Handle Serialized Data

```php
public function getMappingConfig(): array
{
    return $this->getConfigDataSerialized(
        $this->getTypeId() . self::XML_PATH_MAPPING_CONFIG
    );
}
```

### 5. Extend ConfigInterface for Type Safety

```php
interface MediaConfigInterface extends ConfigInterface
{
    // This ensures all config classes have access to base methods
}
```

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Admin UI                                  │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │  profile_plenty_item_export_form.xml                        ││
│  │  ├── profile_config (fieldset)                              ││
│  │  │   ├── media_config (fieldset)                            ││
│  │  │   │   ├── is_active (field)                              ││
│  │  │   │   └── can_delete_images (field)                      ││
│  │  │   └── item_config (fieldset)                             ││
│  │  │       └── ...                                            ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Data Provider Layer                            │
│  ┌─────────────────┐    ┌─────────────────────────────────────┐ │
│  │ProfileDataProvider│──▶│ Modifier Pool                      │ │
│  └─────────────────┘    │ ├── ProfileEntityData               │ │
│                         │ ├── ProfileConfigData  ◀────────────┼─┤
│                         │ └── ...                              │ │
│                         └─────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Configuration Layer                            │
│  ┌───────────────┐      ┌─────────────────┐                     │
│  │  ConfigScope  │◀─────│  ConfigModel    │                     │
│  │  (Retrieval)  │      │  (Base Class)   │                     │
│  └───────┬───────┘      └────────┬────────┘                     │
│          │                       │                               │
│          ▼                       ▼                               │
│  ┌───────────────┐      ┌─────────────────┐                     │
│  │GetConfigData  │      │  MediaConfig    │                     │
│  │  (Resource)   │      │  (Domain)       │                     │
│  └───────────────┘      └─────────────────┘                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Database Layer                               │
│  ┌──────────────────────────┐  ┌──────────────────────────────┐ │
│  │ softcommerce_profile_    │  │ softcommerce_profile_config  │ │
│  │ entity                   │  │                              │ │
│  │ ├── entity_id            │  │ ├── entity_id                │ │
│  │ ├── name                 │  │ ├── parent_id ───────────────┼─┤
│  │ ├── type_id              │  │ ├── scope                    │ │
│  │ └── status               │  │ ├── scope_id                 │ │
│  └──────────────────────────┘  │ ├── path                     │ │
│                                │ └── value                    │ │
│                                └──────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## Related Documentation

- **Profile Extension Patterns**: See `SoftCommerce_PlentyProfile` module documentation for patterns on extending profiles for external system integration
- **Setup System Architecture**: For module-specific setup documentation, see the `docs/` folder in each profile module
