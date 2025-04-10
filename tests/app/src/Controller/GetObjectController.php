<?php declare(strict_types=1);

namespace App\Controller;

use Pimcore\Model\DataObject\TestDataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;

final class GetObjectController
{
    #[Cache(smaxage: 3600, public: true)]
    public function __invoke(Request $request): Response
    {
        $object = TestDataObject::getById($request->query->get('id'));

        return new Response($object?->getContent());
    }
}
