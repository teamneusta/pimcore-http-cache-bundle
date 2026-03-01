<?php declare(strict_types=1);

namespace App\Controller;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WithoutAutomaticTaggingController
{
    public function __construct(
        private readonly CacheActivator $cacheActivator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $id = (int) $request->query->get('id');
        $shouldYield = $request->query->getBoolean('manual_tag', false);

        $asset = $this->cacheActivator->withoutAutomaticTagging(function () use ($id, $shouldYield) {
            $asset = Asset::getById($id);

            if ($shouldYield && $asset) {
                yield CacheTag::fromElement($asset);
            }

            return $asset;
        });

        if (!$asset) {
            return new Response('Asset not found', Response::HTTP_NOT_FOUND);
        }

        return (new Response($asset->getData()))
            ->setSharedMaxAge(3600)
            ->setPublic();
    }
}
