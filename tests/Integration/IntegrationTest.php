<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration;

use Pimcore\Test\KernelTestCase;

final class IntegrationTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_can_boot_the_kernel(): void
    {
        static::bootKernel();

        $this->expectNotToPerformAssertions();
    }
}
