
## Error Handling

You may want to catche exception from the bundle, for this you can use the `PimcoreHttpCacheException::class` class.
Interface implemented by all exceptions thrown by the bundle.

We also log exceptions thrown by Symfony  [Http Cache Bundle](https://github.com/FriendsOfSymfony/FOSHttpCacheBundle/) on invalidation of cache tags.
