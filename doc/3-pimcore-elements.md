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

## Dependent Element Invalidation

When a Pimcore element is updated or deleted, other elements that reference it may also serve stale content.
For example, a document that embeds a data object will be outdated as soon as that object changes.

By default, the bundle only invalidates the cache tag of the element that was directly changed.
Dependent element invalidation — traversing Pimcore's dependency graph to also purge referencing elements — is **disabled by default** and must be opted in via configuration.

The dependency graph is one level deep: only elements that directly reference the changed element are invalidated, not transitive dependencies.

> **Note:** For a dependent element type to actually be invalidated, it must also be enabled in the main `elements` configuration. For example, setting `objects.invalidate_dependencies.types.documents: true` has no effect if `documents` is disabled — the cache tag will be silently dropped.

### Enable dependent invalidation for objects

The most common use case is invalidating documents and other objects that reference a changed data object.
The listed dependent types (`documents`, `objects`) must also be enabled in the `elements` configuration:

```yaml
neusta_pimcore_http_cache:
    elements:
        objects:
            invalidate_dependencies:
                enabled: true
                types:
                    documents: true  # invalidate documents that reference the object
                    objects: true    # invalidate objects that reference the object
                    assets: false    # leave assets out (default)
        documents: true  # must be enabled for document invalidation to take effect
```

### Enable dependent invalidation for assets

If an asset (e.g. an image) is referenced by objects or documents, those can be invalidated when the asset changes.
The listed dependent types must also be enabled in the `elements` configuration:

```yaml
neusta_pimcore_http_cache:
    elements:
        assets:
            invalidate_dependencies:
                enabled: true
                types:
                    objects: true    # invalidate objects that reference the asset
                    documents: true  # invalidate documents that reference the asset
        objects: true    # must be enabled for object invalidation to take effect
        documents: true  # must be enabled for document invalidation to take effect
```

### Enable dependent invalidation for documents

If a document is referenced by other elements (e.g. an object with a document relation field), those elements can be invalidated when the document changes.
The listed dependent types must also be enabled in the `elements` configuration:

```yaml
neusta_pimcore_http_cache:
    elements:
        documents:
            invalidate_dependencies:
                enabled: true
                types:
                    objects: true  # invalidate objects that reference the document
        objects: true  # must be enabled for object invalidation to take effect
```
