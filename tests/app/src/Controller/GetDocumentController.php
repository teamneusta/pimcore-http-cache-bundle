<?php declare(strict_types=1);

namespace App\Controller;

use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetDocumentController
{
    public function __invoke(Request $request): Response
    {
        $document = Document::getById($request->query->get('id'));

        if (!$document) {
            return new Response('Document not found', Response::HTTP_NOT_FOUND);
        }
        
        $message = \sprintf('Document with key: %s', $document->getKey());

        return (new Response($message))
            ->setSharedMaxAge(3600)
            ->setPublic();
    }
}
