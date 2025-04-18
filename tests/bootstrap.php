<?php declare(strict_types=1);

use Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore;

include dirname(__DIR__) . '/vendor/autoload.php';

DG\BypassFinals::enable();

const PIMCORE_CLASS_DEFINITION_DIRECTORY = __DIR__ . '/app/config/pimcore/classes/';

BootstrapPimcore::bootstrap(
    PIMCORE_PROJECT_ROOT: __DIR__ . '/app',
    KERNEL_CLASS: TestKernel::class,
);
