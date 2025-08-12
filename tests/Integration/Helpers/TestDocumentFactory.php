<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Folder;
use Pimcore\Model\Document\Hardlink;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\Snippet;

final class TestDocumentFactory
{
    public static function simplePage(int $id, string $key = 'test_document_page'): Page
    {
        $page = new Page();
        $page->setId($id);
        $page->setKey($key);
        $page->setPublished(true);
        $page->setParentId(1);

        return $page;
    }

    public static function simpleSnippet(int $id, string $key = 'test_document_snippet'): Snippet
    {
        $snippet = new Snippet();
        $snippet->setId($id);
        $snippet->setKey($key);
        $snippet->setPublished(true);
        $snippet->setParentId(1);

        return $snippet;
    }

    public static function simpleEmail(int $id, string $key = 'test_document_email'): Email
    {
        $email = new Email();
        $email->setId($id);
        $email->setKey($key);
        $email->setPublished(true);
        $email->setParentId(1);

        return $email;
    }

    public static function simpleHardLink(int $id, string $key = 'test_document_hard_link'): Hardlink
    {
        $hardlink = new Hardlink();
        $hardlink->setId($id);
        $hardlink->setKey($key);
        $hardlink->setPublished(true);
        $hardlink->setParentId(1);

        return $hardlink;
    }

    public static function simpleFolder(int $id, string $key = 'test_document_folder'): Folder
    {
        $folder = new Folder();
        $folder->setId($id);
        $folder->setKey($key);
        $folder->setPublished(true);
        $folder->setParentId(1);

        return $folder;
    }
}
