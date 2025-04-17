<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache;

use FOS\HttpCache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class CacheTagCollectorTest extends TestCase
{
    use ProphecyTrait;

    private CacheActivator $cacheActivator;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $responseTagger;

    /** @var ObjectProphecy<CacheTagChecker> */
    private ObjectProphecy $cacheTagChecker;

    private CacheTagCollector $cacheTagCollector;

    protected function setUp(): void
    {
        $this->cacheActivator = new CacheActivator();
        $this->cacheTagChecker = $this->prophesize(CacheTagChecker::class);
        $this->responseTagger = $this->prophesize(ResponseTagger::class);
        $this->cacheTagCollector = new CacheTagCollector(
            $this->cacheActivator,
            $this->cacheTagChecker->reveal(),
            $this->responseTagger->reveal(),
        );
    }

    /**
     * @test
     */
    public function addTag_adds_tag_when_caching_is_active(): void
    {
        $tag = CacheTag::fromString('foo');

        $this->cacheActivator->activateCaching();
        $this->cacheTagChecker->isEnabled($tag)->willReturn(true);

        $this->cacheTagCollector->addTag($tag);

        $this->responseTagger->addTags([$tag->toString()])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function addTag_does_not_add_tag_when_caching_is_deactivated(): void
    {
        $tag = CacheTag::fromString('foo');

        $this->cacheActivator->deactivateCaching();
        $this->cacheTagChecker->isEnabled($tag)->willReturn(true);

        $this->cacheTagCollector->addTag($tag);

        $this->responseTagger->addTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function addTag_does_not_add_tag_when_tag_type_is_disabled(): void
    {
        $tag = CacheTag::fromString('foo');

        $this->cacheActivator->activateCaching();
        $this->cacheTagChecker->isEnabled($tag)->willReturn(false);

        $this->cacheTagCollector->addTag($tag);

        $this->responseTagger->addTags([])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function addTags_adds_tags_when_caching_is_active(): void
    {
        $tag1 = CacheTag::fromString('foo');
        $tag2 = CacheTag::fromString('bar');

        $this->cacheActivator->activateCaching();
        $this->cacheTagChecker->isEnabled($tag1)->willReturn(true);
        $this->cacheTagChecker->isEnabled($tag2)->willReturn(true);

        $this->cacheTagCollector->addTags(new CacheTags($tag1, $tag2));

        $this->responseTagger->addTags([$tag1->toString(), $tag2->toString()])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function addTags_does_not_add_tags_when_caching_is_deactivated(): void
    {
        $tag1 = CacheTag::fromString('foo');
        $tag2 = CacheTag::fromString('bar');

        $this->cacheActivator->deactivateCaching();
        $this->cacheTagChecker->isEnabled($tag1)->willReturn(true);
        $this->cacheTagChecker->isEnabled($tag2)->willReturn(true);

        $this->cacheTagCollector->addTags(new CacheTags($tag1, $tag2));

        $this->responseTagger->addTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function addTags_does_not_add_tags_when_tag_type_is_disabled(): void
    {
        $tag1 = CacheTag::fromString('foo');
        $tag2 = CacheTag::fromString('bar');

        $this->cacheActivator->activateCaching();
        $this->cacheTagChecker->isEnabled($tag1)->willReturn(false);
        $this->cacheTagChecker->isEnabled($tag2)->willReturn(false);

        $this->cacheTagCollector->addTags(new CacheTags($tag1, $tag2));

        $this->responseTagger->addTags([])->shouldHaveBeenCalledOnce();
    }
}
