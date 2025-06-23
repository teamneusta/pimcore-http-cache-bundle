<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Adapter\FOSHttpCache;

use FOS\HttpCache\CacheInvalidator as FosCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\CacheInvalidatorAdapter;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class CacheInvalidatorAdapterTest extends TestCase
{
    use ProphecyTrait;

    private CacheInvalidatorAdapter $subject;

    /** @var ObjectProphecy<FosCacheInvalidator> */
    private $fosCacheInvalidator;

    protected function setUp(): void
    {
        $this->fosCacheInvalidator = $this->prophesize(FosCacheInvalidator::class);
        $this->subject = new CacheInvalidatorAdapter(
            $this->fosCacheInvalidator->reveal(),
        );
    }

    /**
     * @test
     */
    public function invalidateTags_should_not_invalidate_tags_when_tags_are_empty(): void
    {
        $tags = new CacheTags();

        $this->subject->invalidate($tags);

        $this->fosCacheInvalidator->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidateTags_should_invalidate_tags(): void
    {
        $tags = new CacheTags(CacheTag::fromString('tag1'), CacheTag::fromString('tag2'));

        $this->subject->invalidate($tags);

        $this->fosCacheInvalidator->invalidateTags(['tag1', 'tag2'])->shouldHaveBeenCalledOnce();
    }
}
