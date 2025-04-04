<?php
declare(strict_types=1);

namespace App;

use Pimcore\Model\DataObject\TestDataObject;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetObjectController
{
    #[Route(path: '/get-object', name: 'get_object')]
    #[Cache(smaxage: 86400, public: true)]
    public function __invoke(int $id): Response
    {
        TestDataObject::getById($id);
        $response = new Response('Hello World');
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, [true]);

        $response->setContent('Your content here');
        $response->headers->set('Cache-Control', 'public, max-age=3600');

        return $response;
    }

}
