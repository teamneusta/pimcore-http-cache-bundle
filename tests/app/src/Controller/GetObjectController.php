<?php
declare(strict_types=1);

namespace App\Controller;

use Pimcore\Model\DataObject\TestDataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetObjectController
{
    #[Route(path: '/get-object', name: 'get_object')]
    #[Cache(smaxage: 3600, public: true)]
    public function __invoke(Request $request): Response
    {
        TestDataObject::getById($request->query->get('id'));

        return new Response('Hello World');
    }
}
