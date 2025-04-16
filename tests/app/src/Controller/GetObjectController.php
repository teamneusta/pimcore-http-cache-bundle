<?php declare(strict_types=1);

namespace App\Controller;

use Pimcore\Model\DataObject\TestDataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetObjectController
{
    public function __invoke(Request $request): Response
    {
        if (!$object = TestDataObject::getById($request->query->get('id'))) {
            return new Response('Object not found', Response::HTTP_NOT_FOUND);
        }

        return (new Response($object->getContent()))
            ->setSharedMaxAge(3600)
            ->setPublic();
    }
}
