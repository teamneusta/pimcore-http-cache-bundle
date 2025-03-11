<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\PurgeChecker;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class CacheInvalidatorTest extends TestCase
{
    use ProphecyTrait;

    private CacheInvalidator $cacheInvalidator;

    /** @var ObjectProphecy<CacheActivator> */
    private $cacheActivator;

    /** @var ObjectProphecy<PurgeChecker> */
    private $purgeChecker;

    /** @var ObjectProphecy<CacheManager> */
    private $cacheManager;

    protected function setUp(): void
    {
        $this->cacheActivator = $this->prophesize(CacheActivator::class);
        $this->purgeChecker = $this->prophesize(PurgeChecker::class);
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheInvalidator = new CacheInvalidator(
            $this->cacheActivator->reveal(),
            $this->purgeChecker->reveal(),
            $this->cacheManager->reveal(),
        );
    }

    /**
     * @test
     */
    public function invalidateElement_should_invalidate_element_for_given_type(): void
    {
        $element = $this->prophesize(ElementInterface::class);
        $element->getId()->willReturn(42);
        $tag = CacheTag::fromElement($element->reveal());

        $this->cacheActivator->isCachingActive()->willReturn(true);
        $this->purgeChecker->isEnabled(ElementType::Asset->value)->willReturn(true);

        $this->cacheInvalidator->invalidateElement($element->reveal(), ElementType::Asset);

        $this->cacheManager->invalidateTags([$tag->toString()])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function invalidateElement_should_not_invalidate_element_when_caching_is_not_active(): void
    {
        $element = $this->prophesize(ElementInterface::class);

        $this->cacheActivator->isCachingActive()->willReturn(false);

        $this->cacheInvalidator->invalidateElement($element->reveal(), ElementType::Asset);

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidateElementTags_should_invalidate_tags_for_given_type(): void
    {
        $document1 = $this->prophesize(Document::class);
        $document2 = $this->prophesize(Document::class);

        $document1->getId()->willReturn(42);
        $document2->getId()->willReturn(43);
        $tags = CacheTags::fromElements([
            $document1->reveal(),
            $document2->reveal(),
        ]);
        $this->cacheActivator->isCachingActive()->willReturn(true);
        $this->purgeChecker->isEnabled(ElementType::Asset->value)->willReturn(true);

        $this->cacheInvalidator->invalidateElementTags($tags, ElementType::Asset);

        $this->cacheManager->invalidateTags([
            CacheTag::fromElement($document1->reveal())->toString(),
            CacheTag::fromElement($document2->reveal())->toString(),
        ])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function invalidateElementTags_should_not_invalidate_tags_when_caching_is_not_active(): void
    {
        $document1 = $this->prophesize(Document::class);
        $document2 = $this->prophesize(Document::class);

        $document1->getId()->willReturn(42);
        $document2->getId()->willReturn(43);
        $tags = CacheTags::fromElements([
            $document1->reveal(),
            $document2->reveal(),
        ]);
        $this->cacheActivator->isCachingActive()->willReturn(false);

        $this->cacheInvalidator->invalidateElementTags($tags, ElementType::Document);

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidateElementTags_should_not_invalidate_tags_when_tags_are_empty(): void
    {
        $tags = new CacheTags();

        $this->cacheActivator->isCachingActive()->willReturn(true);
        $this->purgeChecker->isEnabled(ElementType::Document->value)->willReturn(true);

        $this->cacheInvalidator->invalidateElementTags($tags, ElementType::Document);

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidateTags_should_invalidate_tags(): void
    {
        $tags = new CacheTags(new CacheTag('tag1'), new CacheTag('tag2'));

        $this->cacheActivator->isCachingActive()->willReturn(true);

        $this->cacheInvalidator->invalidateTags($tags);

        $this->cacheManager->invalidateTags(['tag1', 'tag2'])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function invalidateTags_should_not_invalidate_tags_when_caching_is_not_active(): void
    {
        $tags = new CacheTags(new CacheTag('tag1'), new CacheTag('tag2'));

        $this->cacheActivator->isCachingActive()->willReturn(false);

        $this->cacheInvalidator->invalidateTags($tags);

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidateTags_should_not_invalidate_tags_when_tags_are_empty(): void
    {
        $tags = new CacheTags();

        $this->cacheActivator->isCachingActive()->willReturn(true);

        $this->cacheInvalidator->invalidateTags($tags);

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }
}
