## Configuration

This bundle is configured via the `neusta_pimcore_http_cache` key in your `config/packages/neusta_pimcore_http_cache.yaml` file.

```yaml
neusta_pimcore_http_cache:
    # Enable/disable cache handling for certain element types
    elements:
        assets:
            # Available types: image, video, audio, document, archive, text, unknown, folder
            # By default, every type except "folder" is enabled
            types:
                archive: false
                unknown: false

            # Invalidate dependent elements when an asset changes (disabled by default).
            # Note: a dependent element type must also be enabled above for invalidation to take effect.
            invalidate_dependent_elements:
                enabled: true
                types:
                    objects:              # fine-grained: invalidate objects, but with exclusions
                        enabled: true
                        types:
                            folder: false         # skip object folders
                        classes:
                            MyIgnoredClass: false # skip this class
                    documents:            # fine-grained: invalidate documents, but with exclusions
                        enabled: true
                        types:
                            link: false           # skip link documents

            # Or disable assets completely (mutually exclusive with the options above)
            enabled: false

        documents:
            # Available types: page, snippet, link, hardlink, email, folder
            # By default, every type except "email", "folder" and "hardlink" is enabled
            types:
                link: false

            # Invalidate dependent elements when a document changes (disabled by default).
            # Note: a dependent element type must also be enabled above for invalidation to take effect.
            invalidate_dependent_elements:
                enabled: true
                types:
                    objects:              # fine-grained: invalidate objects, but with exclusions
                        enabled: true
                        types:
                            folder: false         # skip object folders
                        classes:
                            MyIgnoredClass: false # skip this class

            # Or disable documents completely (mutually exclusive with the options above)
            enabled: false

        objects:
            # Available types: object, variant, folder
            # By default, every type except "folder" is enabled
            types:
                variant: false

            # By default, every data object class is enabled
            classes:
                MyDataObjectClass: false

            # Invalidate dependent elements when an object changes (disabled by default).
            # Note: a dependent element type must also be enabled above for invalidation to take effect.
            invalidate_dependent_elements:
                enabled: true
                types:
                    objects:              # fine-grained: invalidate objects, but with exclusions
                        enabled: true
                        types:
                            folder: false         # skip object folders
                            variant: false        # skip variants
                        classes:
                            MyIgnoredClass: false # skip this class
                    documents:            # fine-grained: invalidate documents, but with exclusions
                        enabled: true
                        types:
                            link: false           # skip link documents
                    assets:               # fine-grained: invalidate assets, but with exclusions
                        enabled: true
                        types:
                            folder: false         # skip asset folders

            # Or disable data objects completely (mutually exclusive with the options above)
            enabled: false

    # Enable/disable cache handling for custom cache types
    # Note that custom types MUST be defined (and enabled) here to be tagged/invalidated!
    cache_types:
        someType: true
        otherType: false
```
