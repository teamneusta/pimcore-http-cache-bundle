## Pimcore Elements

By default, the bundle handles cache tagging and invalidation for all Pimcore elements (assets, documents, objects) and custom cache
types. You can enable or disable cache handling for specific element types and classes in the configuration file.

### Assets

By default, all asset types except "folder" are enabled. You can disable specific asset types or disable assets
completely.

#### Disable specific asset types

Example configuration to disable the "archive" and "unknown" asset types:
```yaml
neusta_pimcore_http_cache:
    elements:
        assets:
            types:
                archive: false
                unknown: false
```
#### Disable assets completely
Example configuration to disable assets entirely:
```yaml
neusta_pimcore_http_cache:
    elements:
        assets: false
```

### Documents
By default, all document types except "email", "folder" and "hardlink" are enabled. You can disable specific document types or disable documents completely.

#### Disable specific document types
Example configuration to disable the "link" document type:
```yaml
neusta_pimcore_http_cache:
    elements:
        documents:
            types:
                link: false
```

#### Disable documents entirely

Example configuration to disable documents completely:
```yaml
neusta_pimcore_http_cache:
    elements:
        documents: false
```

### Objects
By default, all object types except "folder" are enabled. You can disable specific object types or disable objects completely. Also, you can enable or disable cache handling for specific data object classes.

#### Disable specific object types
Example configuration to disable the "variant" object type:
```yaml
neusta_pimcore_http_cache:
    elements:
        objects:
            types:
                variant: false
```

#### Disable objects entirely
Example configuration to disable objects completely:
```yaml
neusta_pimcore_http_cache:
    elements:
        objects: false
```

#### Enable or disable cache handling for specific data object classes
By default, all data object classes are enabled. You can enable or disable cache handling for specific data object classes.

Example configuration to disable cache handling for the "MyDataObjectClass" data object class:
```yaml
neusta_pimcore_http_cache:
    elements:
        objects:
            classes:
                MyDataObjectClass: false
```

