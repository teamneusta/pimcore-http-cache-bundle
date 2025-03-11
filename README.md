# Pimcore HTTP Cache Bundle

## Installation

1.  **Require the bundle**

    ```shell
    composer require teamneusta/pimcore-http-cache-bundle
    ```

2.  **Enable the bundle**

    Add the Bundle to your `config/bundles.php`:

    ```php
    Neusta\Pimcore\HttpCacheBundle\NeustaPimcoreHttpCacheBundle::class => ['all' => true],
    ```

## Usage

TODO

## Configuration

```yaml
neusta_pimcore_http_cache:
  # Enables or disables CachePurgeSubscriber, CacheTagSubscriber. When enabled these listeners will add
  # x-key tags to http responses and send invalidation requests when pimcore objects change. You normally
  # should leave these subscribers enabled in order to make use of tagging and banning of pimcore objects.
  # If you need to customize tagging and banning functionality, or you want to opt out of tagging and banning
  # completely, set this option to `false`.
  # Default is: `true`
  # listeners: false
```

## Contribution

Feel free to open issues for any bug, feature request, or other ideas.

Please remember to create an issue before creating large pull requests.

### Local Development

Build the inline Dockerfile with:

```shell
docker compose build
```

To develop on your local machine, the vendor dependencies are required.

```shell
bin/composer install
```

We use composer scripts for our main quality tools. They can be executed via the `bin/composer` file as well.

```shell
bin/composer cs:fix
bin/composer phpstan
bin/composer tests
```
