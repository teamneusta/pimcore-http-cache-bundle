## Configuration

This bundle is configured via the `neusta_pimcore_http_cache` key in your `config/packages/neusta_pimcore_http_cache.yaml` file.

```yaml
neusta_pimcore_http_cache:
    # Enable/disable cache handling for certain element types
    elements:
        assets:
            # By default, every type except "folder" is enabled
            types:
                archive: false
                unknown: false
                
            # Unless you disable assets completely
            enabled: false
            
        documents:
            # By default, every type except "email", "folder" and "hardlink" is enabled
            types:
                link: false
                
            # Unless you disable documents completely
            enabled: false
            
        objects:
            # By default, every type except "folder" is enabled
            types:
                variant: false
            
            # By default, every data object class is enabled
            classes:
                MyDataObjectClass: false

            # Unless you disable data objects completely
            enabled: false

    # Enable/disable cache handling for custom cache types
    # Note that custom types MUST be defined (and enabled) here to be tagged/invalidated!
    cache_types:
        someType: true
        otherType: false
```
