<?php declare(strict_types=1);

namespace App\Controller;

use Pimcore\Model\DataObject\TestDataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateObjectController
{
    public function __invoke(Request $request): Response
    {
        if (!$object = TestDataObject::getById($request->query->getInt('id'))) {
            return new Response('Object not found', Response::HTTP_NOT_FOUND);
        }

        $object->setContent($request->request->getString('content'))->save();

        return new Response('Object updated');
    }
}
