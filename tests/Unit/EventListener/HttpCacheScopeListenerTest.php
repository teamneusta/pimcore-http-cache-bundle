<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\EventListener;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use Neusta\Pimcore\HttpCacheBundle\EventListener\HttpCacheScopeListener;
use PHPUnit\Framework\TestCase;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Document;
use Pimcore\Routing\DataObjectRoute;
use Pimcore\Routing\DocumentRoute;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class HttpCacheScopeListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<CacheScope> */
    private ObjectProphecy $cacheScope;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $responseTagger;

    /** @var ObjectProphecy<PimcoreContextResolver> */
    private ObjectProphecy $contextResolver;

    /** @var ObjectProphecy<DocumentResolver> */
    private ObjectProphecy $documentResolver;

    /** @var ObjectProphecy<HttpKernelInterface> */
    private ObjectProphecy $kernel;

    protected function setUp(): void
    {
        $this->cacheScope = $this->prophesize(CacheScope::class);
        $this->responseTagger = $this->prophesize(ResponseTagger::class);
        $this->contextResolver = $this->prophesize(PimcoreContextResolver::class);
        $this->documentResolver = $this->prophesize(DocumentResolver::class);
        $this->kernel = $this->prophesize(HttpKernelInterface::class);
    }

    /**
     * @test
     */
    public function onKernelRequest_does_not_activate_caching_when_collecting_from_request_is_disabled(): void
    {
        $subject = $this->createSubject(collectFromRequest: false);

        $subject->onKernelRequest($this->createRequestEvent(new Request()));

        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
        $this->contextResolver->matchesPimcoreContext(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelRequest_does_not_activate_caching_for_sub_request(): void
    {
        $subject = $this->createSubject(collectFromRequest: true);

        $subject->onKernelRequest($this->createRequestEvent(new Request(), HttpKernelInterface::SUB_REQUEST));

        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
        $this->contextResolver->matchesPimcoreContext(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelRequest_does_not_activate_caching_for_non_cacheable_method(): void
    {
        $subject = $this->createSubject(collectFromRequest: true);

        $subject->onKernelRequest($this->createRequestEvent(Request::create('/', 'POST')));

        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
        $this->contextResolver->matchesPimcoreContext(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelRequest_does_not_activate_caching_for_admin_context(): void
    {
        $subject = $this->createSubject(collectFromRequest: true);
        $request = new Request();

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(true);

        $subject->onKernelRequest($this->createRequestEvent($request));

        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelRequest_activates_caching_for_cacheable_main_request_outside_admin_context(): void
    {
        $subject = $this->createSubject(collectFromRequest: true);
        $request = new Request();

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $subject->onKernelRequest($this->createRequestEvent($request));

        $this->cacheScope->enable()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_does_nothing_for_sub_request(): void
    {
        $subject = $this->createSubject();

        $subject->onKernelController($this->createControllerEvent(new Request(), HttpKernelInterface::SUB_REQUEST));

        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
        $this->responseTagger->tag(Argument::any())->shouldNotHaveBeenCalled();
        $this->documentResolver->getDocument(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_does_not_activate_or_tag_for_non_cacheable_method_when_collecting_from_controller(): void
    {
        $subject = $this->createSubject(collectFromRequest: false);

        $subject->onKernelController($this->createControllerEvent(Request::create('/', 'POST')));

        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
        $this->responseTagger->tag(Argument::any())->shouldNotHaveBeenCalled();
        $this->documentResolver->getDocument(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_does_not_activate_or_tag_for_admin_context_when_collecting_from_controller(): void
    {
        $subject = $this->createSubject(collectFromRequest: false);
        $request = new Request();

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(true);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
        $this->responseTagger->tag(Argument::any())->shouldNotHaveBeenCalled();
        $this->documentResolver->getDocument(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_activates_caching_when_collecting_from_controller(): void
    {
        $subject = $this->createSubject(collectFromRequest: false);
        $request = new Request();

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $this->documentResolver
            ->getDocument($request)
            ->willReturn(null);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->cacheScope->enable()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_does_not_activate_caching_when_already_collecting_from_request(): void
    {
        $subject = $this->createSubject(collectFromRequest: true);
        $request = new Request();

        $this->documentResolver
            ->getDocument($request)
            ->willReturn(null);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->contextResolver->matchesPimcoreContext(Argument::cetera())->shouldNotHaveBeenCalled();
        $this->cacheScope->enable()->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_tags_document_from_document_route(): void
    {
        $subject = $this->createSubject();
        $document = $this->createDocument(123);
        $route = $this->prophesize(DocumentRoute::class);
        $request = new Request();

        $request->attributes->set(DynamicRouter::ROUTE_KEY, $route->reveal());

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $route
            ->getDocument()
            ->willReturn($document);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->responseTagger
            ->tag(Argument::that(static fn (CacheTags $tags): bool => ['d123'] === $tags->toArray()))
            ->shouldHaveBeenCalled();

        $this->documentResolver->getDocument(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_tags_object_from_data_object_route(): void
    {
        $subject = $this->createSubject();
        $object = $this->createDataObject(456);
        $route = $this->prophesize(DataObjectRoute::class);
        $request = new Request();

        $request->attributes->set(DynamicRouter::ROUTE_KEY, $route->reveal());

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $route
            ->getObject()
            ->willReturn($object);

        $this->documentResolver
            ->getDocument($request)
            ->willReturn(null);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->responseTagger
            ->tag(Argument::that(static fn (CacheTags $tags): bool => ['o456'] === $tags->toArray()))
            ->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_does_not_tag_fallback_document_when_disabled(): void
    {
        $subject = $this->createSubject(tagFallbackDocument: false);
        $object = $this->createDataObject(456);
        $route = $this->prophesize(DataObjectRoute::class);
        $request = new Request();

        $request->attributes->set(DynamicRouter::ROUTE_KEY, $route->reveal());

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $route
            ->getObject()
            ->willReturn($object);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->responseTagger
            ->tag(Argument::that(static fn (CacheTags $tags): bool => ['o456'] === $tags->toArray()))
            ->shouldHaveBeenCalled();

        $this->documentResolver->getDocument(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_tags_fallback_document_when_route_element_is_not_document(): void
    {
        $subject = $this->createSubject(tagFallbackDocument: true);
        $object = $this->createDataObject(456);
        $fallbackDocument = $this->createDocument(789);
        $route = $this->prophesize(DataObjectRoute::class);
        $request = new Request();

        $request->attributes->set(DynamicRouter::ROUTE_KEY, $route->reveal());

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $route
            ->getObject()
            ->willReturn($object);

        $this->documentResolver
            ->getDocument($request)
            ->willReturn($fallbackDocument);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->responseTagger
            ->tag(Argument::that(static fn (CacheTags $tags): bool => ['o456'] === $tags->toArray()))
            ->shouldHaveBeenCalled();

        $this->responseTagger
            ->tag(Argument::that(static fn (CacheTags $tags): bool => ['d789'] === $tags->toArray()))
            ->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_tags_only_fallback_document_when_route_is_unknown(): void
    {
        $subject = $this->createSubject(tagFallbackDocument: true);
        $fallbackDocument = $this->createDocument(789);
        $request = new Request();

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $this->documentResolver
            ->getDocument($request)
            ->willReturn($fallbackDocument);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->responseTagger
            ->tag(Argument::that(static fn (CacheTags $tags): bool => ['d789'] === $tags->toArray()))
            ->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function onKernelController_does_not_tag_anything_when_route_is_unknown_and_no_fallback_document_exists(): void
    {
        $subject = $this->createSubject(tagFallbackDocument: true);
        $request = new Request();

        $this->contextResolver
            ->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)
            ->willReturn(false);

        $this->documentResolver
            ->getDocument($request)
            ->willReturn(null);

        $subject->onKernelController($this->createControllerEvent($request));

        $this->responseTagger->tag(Argument::any())->shouldNotHaveBeenCalled();
    }

    private function createSubject(
        bool $collectFromRequest = false,
        bool $tagFallbackDocument = false,
    ): HttpCacheScopeListener {
        return new HttpCacheScopeListener(
            $this->cacheScope->reveal(),
            $this->responseTagger->reveal(),
            $this->contextResolver->reveal(),
            $this->documentResolver->reveal(),
            $collectFromRequest,
            $tagFallbackDocument,
        );
    }

    private function createRequestEvent(
        Request $request,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): RequestEvent {
        return new RequestEvent($this->kernel->reveal(), $request, $requestType);
    }

    private function createControllerEvent(
        Request $request,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): ControllerEvent {
        return new ControllerEvent($this->kernel->reveal(), static fn () => null, $request, $requestType);
    }

    private function createDocument(int $id): Document
    {
        $document = $this->prophesize(Document::class);
        $document->getId()->willReturn($id);

        return $document->reveal();
    }

    private function createDataObject(int $id): Concrete
    {
        $object = $this->prophesize(Concrete::class);
        $object->getId()->willReturn($id);

        return $object->reveal();
    }
}
