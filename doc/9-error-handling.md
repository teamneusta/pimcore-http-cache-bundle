
## Error Handling

You may want to catch exceptions from the bundle; you can use the `PimcoreHttpCacheException` interface, which is implemented by all exceptions thrown by the bundle.

We also log exceptions thrown by the [FOSHttpCacheBundle](https://github.com/FriendsOfSymfony/FOSHttpCacheBundle/) on invalidation of cache tags.
