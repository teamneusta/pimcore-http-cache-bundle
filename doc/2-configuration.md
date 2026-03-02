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

            # Invalidate dependent elements when an asset changes (disabled by default).
            # Note: a dependent element type must also be enabled above for invalidation to take effect.
            invalidate_dependencies:
                enabled: true
                types:
                    objects: true
                    documents: true

            # Or disable assets completely (mutually exclusive with the options above)
            enabled: false

        documents:
            # By default, every type except "email", "folder" and "hardlink" is enabled
            types:
                link: false

            # Invalidate dependent elements when a document changes (disabled by default).
            # Note: a dependent element type must also be enabled above for invalidation to take effect.
            invalidate_dependencies:
                enabled: true
                types:
                    objects: true

            # Or disable documents completely (mutually exclusive with the options above)
            enabled: false

        objects:
            # By default, every type except "folder" is enabled
            types:
                variant: false

            # By default, every data object class is enabled
            classes:
                MyDataObjectClass: false

            # Invalidate dependent elements when an object changes (disabled by default).
            # Note: a dependent element type must also be enabled above for invalidation to take effect.
            invalidate_dependencies:
                enabled: true
                types:
                    objects: true
                    documents: true
                    assets: true

            # Or disable data objects completely (mutually exclusive with the options above)
            enabled: false

    # Enable/disable cache handling for custom cache types
    # Note that custom types MUST be defined (and enabled) here to be tagged/invalidated!
    cache_types:
        someType: true
        otherType: false
```
