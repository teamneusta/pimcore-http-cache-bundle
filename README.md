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
