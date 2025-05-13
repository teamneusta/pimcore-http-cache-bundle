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
