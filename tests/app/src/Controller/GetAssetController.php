<?php declare(strict_types=1);

namespace App\Controller;

use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;

final class GetAssetController
{
    #[Cache(smaxage: 3600, public: true)]
    public function __invoke(Request $request): Response
    {
        $asset = Asset::getById($request->query->get('id'));

        return new Response($asset?->getData());
    }
}
