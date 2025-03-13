<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTypeChecker;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class CacheInvalidatorTest extends TestCase
{
    use ProphecyTrait;

    private CacheInvalidator $cacheInvalidator;

    /** @var ObjectProphecy<CacheActivator> */
    private $cacheActivator;

    /** @var ObjectProphecy<CacheTypeChecker> */
    private $typeChecker;

    /** @var ObjectProphecy<CacheManager> */
    private $cacheManager;

    protected function setUp(): void
    {
        $this->cacheActivator = $this->prophesize(CacheActivator::class);
        $this->typeChecker = $this->prophesize(CacheTypeChecker::class);
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheInvalidator = new CacheInvalidator(
            $this->cacheActivator->reveal(),
            $this->typeChecker->reveal(),
            $this->cacheManager->reveal(),
        );
    }

    /**
     * @test
     */
    public function invalidateElement_should_invalidate_element_for_given_type(): void
    {
        $element = $this->prophesize(Asset::class);
        $element->getId()->willReturn(42);
        $tag = CacheTag::fromElement($element->reveal());

        $this->cacheActivator->isCachingActive()->willReturn(true);
        $this->typeChecker->isEnabled(CacheType::fromString(ElementType::Asset->value))->willReturn(true);

        $this->cacheInvalidator->invalidateElement($element->reveal());

        $this->cacheManager->invalidateTags([$tag->toString()])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function invalidateElement_should_not_invalidate_element_when_caching_is_not_active(): void
    {
        $element = $this->prophesize(Asset::class);
        $element->getId()->willReturn(42);

        $this->cacheActivator->isCachingActive()->willReturn(false);

        $this->cacheInvalidator->invalidateElement($element->reveal());

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidateTags_should_invalidate_tags_for_given_type(): void
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
        $this->typeChecker->isEnabled(CacheType::fromString(ElementType::Document->value))->willReturn(true);

        $this->cacheInvalidator->invalidateTags($tags);

        $this->cacheManager->invalidateTags([
            CacheTag::fromElement($document1->reveal())->toString(),
            CacheTag::fromElement($document2->reveal())->toString(),
        ])->shouldHaveBeenCalledOnce();
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

    /**
     * @test
     */
    public function invalidateTags_should_invalidate_tags(): void
    {
        $tags = new CacheTags(CacheTag::fromString('tag1'), CacheTag::fromString('tag2'));

        $this->cacheActivator->isCachingActive()->willReturn(true);

        $this->cacheInvalidator->invalidateTags($tags);

        $this->cacheManager->invalidateTags(['tag1', 'tag2'])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function invalidateTags_should_not_invalidate_tags_when_caching_is_not_active(): void
    {
        $tags = new CacheTags(CacheTag::fromString('tag1'), CacheTag::fromString('tag2'));

        $this->cacheActivator->isCachingActive()->willReturn(false);

        $this->cacheInvalidator->invalidateTags($tags);

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }
}
