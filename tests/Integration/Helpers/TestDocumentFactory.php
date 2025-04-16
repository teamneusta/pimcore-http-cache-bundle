<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Folder;
use Pimcore\Model\Document\Hardlink;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\Snippet;

final class TestDocumentFactory
{
    public static function simplePage(): Page
    {
        $page = new Page();
        $page->setId(42);
        $page->setKey('test_document_page');
        $page->setPublished(true);
        $page->setParentId(1);

        return $page;
    }

    public static function simpleSnippet(): Snippet
    {
        $snippet = new Snippet();
        $snippet->setId(23);
        $snippet->setKey('test_document_snippet');
        $snippet->setPublished(true);
        $snippet->setParentId(1);

        return $snippet;
    }

    public static function simpleEmail(): Email
    {
        $email = new Email();
        $email->setId(17);
        $email->setKey('test_document_link');
        $email->setPublished(true);
        $email->setParentId(1);

        return $email;
    }

    public static function simpleHardLink(): Hardlink
    {
        $hardlink = new Hardlink();
        $hardlink->setId(33);
        $hardlink->setKey('test_document_hard_link');
        $hardlink->setPublished(true);
        $hardlink->setParentId(1);

        return $hardlink;
    }

    public static function simpleFolder(): Folder
    {
        $folder = new Folder();
        $folder->setId(97);
        $folder->setKey('test_document_folder');
        $folder->setPublished(true);
        $folder->setParentId(1);

        return $folder;
    }
}
