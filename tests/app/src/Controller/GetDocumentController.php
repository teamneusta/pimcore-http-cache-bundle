<?php declare(strict_types=1);

namespace App\Controller;

use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

final class GetDocumentController
{
    #[Route(path: '/get-document', name: 'get_document')]
    #[Cache(smaxage: 3600, public: true)]
    public function __invoke(Request $request): Response
    {
        $document = Document::getById($request->query->get('id'));

        return new Response(
            \sprintf('Document with key: %s', $document?->getKey()),
        );
    }
}
