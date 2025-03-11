<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\TagDocumentListener;
use PHPUnit\Framework\TestCase;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TagDocumentListenerTest extends TestCase
{
    use ProphecyTrait;

    private TagDocumentListener $tagDocumentListener;

    /** @var ObjectProphecy<CacheActivator> */
    private $cacheActivator;

    /** @var ObjectProphecy<CacheTagCollector> */
    private $cacheTagCollector;

    protected function setUp(): void
    {
        $this->cacheActivator = $this->prophesize(CacheActivator::class);
        $this->cacheTagCollector = $this->prophesize(CacheTagCollector::class);
        $this->tagDocumentListener = new TagDocumentListener(
            $this->cacheActivator->reveal(),
            $this->cacheTagCollector->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_tag_elements_of_type_document(): void
    {
        $document = $this->prophesize(Document::class);
        $documentEvent = new DocumentEvent($document->reveal());

        $document->getType()->willReturn('page');
        $document->getId()->willReturn(42);
        $this->cacheActivator->isCachingActive()->willReturn(true);

        ($this->tagDocumentListener)($documentEvent);

        $this->cacheTagCollector->addTag(Argument::type(CacheTag::class))->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_should_not_tag_documents_of_type_folder(): void
    {
        $document = $this->prophesize(Document::class);
        $documentEvent = new DocumentEvent($document->reveal());

        $document->getType()->willReturn('folder');
        $document->getId()->willReturn(42);
        $this->cacheActivator->isCachingActive()->willReturn(true);

        ($this->tagDocumentListener)($documentEvent);

        $this->cacheTagCollector->addTag(Argument::type(CacheTag::class))->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_not_tag_documents_if_caching_is_not_active(): void
    {
        $document = $this->prophesize(Document::class);
        $documentEvent = new DocumentEvent($document->reveal());

        $this->cacheActivator->isCachingActive()->willReturn(false);

        ($this->tagDocumentListener)($documentEvent);

        $this->cacheTagCollector->addTag(Argument::type(CacheTag::class))->shouldNotHaveBeenCalled();
    }
}
