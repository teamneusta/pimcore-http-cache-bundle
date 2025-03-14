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
Itegrate with code rabbit.

## Configuration

```yaml
neusta_pimcore_http_cache:
  # Enable/disable cache handling for certain element types
  # (tagging and banning when elements of these types change).
  asset: true
  document: true
  object: true
```

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
