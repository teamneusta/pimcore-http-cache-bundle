## Profiling

For easier debugging, you can enable the Symfony Profiler.
This bundle provides an easy way to view the cache tags used in the response headers.
Additionally, you can inspect the bundle’s configuration.

### Enabling the Symfony Profiler

The Symfony Profiler is enabled by default in the `dev` environment. If you need to enable it in another environment, ensure the following bundle is registered in your `config/bundles.php`:

```php
Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
