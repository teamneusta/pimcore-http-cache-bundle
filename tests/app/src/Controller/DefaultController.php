<?php declare(strict_types=1);

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Response;

final class DefaultController extends FrontendController
{
    public function defaultAction(): Response
    {
        $message = \sprintf('Document with key: %s', $this->document?->getKey());

        return (new Response($message))
            ->setSharedMaxAge(3600)
            ->setPublic();
    }
}
