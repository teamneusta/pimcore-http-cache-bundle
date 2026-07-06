<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\EventListener;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Model\Document;
use Pimcore\Routing\DataObjectRoute;
use Pimcore\Routing\DocumentRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 */
final class HttpCacheScopeListener
{
    public function __construct(
        private readonly CacheScope $cacheScope,
        private readonly ResponseTagger $responseTagger,
        private readonly PimcoreContextResolver $contextResolver,
        private readonly DocumentResolver $documentResolver,
        private readonly bool $collectFromRequest = false,
        private readonly bool $tagFallbackDocument = false,
    ) {
    }

    #[AsEventListener(priority: 1024)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->collectFromRequest || !$event->isMainRequest() || !$this->isCacheable($event->getRequest())) {
            return;
        }

        $this->cacheScope->enable();
    }

    #[AsEventListener]
    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->collectFromRequest) {
            if (!$this->isCacheable($request)) {
                return;
            }

            $this->cacheScope->enable();
        }

        $route = $request->attributes->get(DynamicRouter::ROUTE_KEY);
        $routeElement = match (true) {
            $route instanceof DocumentRoute => $route->getDocument(),
            $route instanceof DataObjectRoute => $route->getObject(),
            default => null,
        };

        if (null !== $routeElement) {
            $this->responseTagger->tag(CacheTags::fromElement($routeElement));
        }

        if (!$routeElement instanceof Document && $this->tagFallbackDocument) {
            $content = $this->documentResolver->getDocument($request);

            // If the route itself didn't resolve to a Document, the $content can only be a fallback document
            // (e.g., the nearest document by path for a custom route), rather than something that the route matched.
            if ($content instanceof Document) {
                $this->responseTagger->tag(CacheTags::fromElement($content));
            }
        }
    }

    private function isCacheable(Request $request): bool
    {
        if (!$request->isMethodCacheable()) {
            return false;
        }

        if ($this->contextResolver->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)) {
            return false;
        }

        return true;
    }
}
