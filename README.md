# Pimcore HTTP Cache Bundle

This bundle provides a simple way to handle cache invalidation for Pimcore elements.

## Documentation

You will find the detailed documentation in the following links:

* [Installation](doc/1-installation.md)
* [Configuration](doc/2-configuration.md)
* [Pimcore elements](doc/3-pimcore-elements.md)
* [Additional tags](doc/4-additional-tags.md)
* [Cancel caching behavior](doc/5-cancel-caching-behavior.md)
* [Custom cache types](doc/6-custom-cache-types.md)
* [Disabling caching behavior](doc/7-disable-caching-behavior.md)

## Contribution

Feel free to open issues for any bug, feature request, or other ideas.

Please remember to create an issue before creating large pull requests.

### Local Development

Build the Docker container with:

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
