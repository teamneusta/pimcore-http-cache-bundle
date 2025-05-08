<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class InvalidateDocumentTest extends ConfigurableKernelTestCase
{
    use ArrangeCacheTest;
    use ProphecyTrait;
    use ResetDatabase;

    /** @var ObjectProphecy<CacheManager> */
    private ObjectProphecy $cacheManager;

    private Document $document;

    private Document\Hardlink $hardlink;

    private Document\Email $email;

    private Document\Folder $folder;

    protected function setUp(): void
    {
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheManager->invalidateTags(Argument::any())->willReturn($this->cacheManager->reveal());
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());

        $this->document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());
        $this->hardlink = self::arrange(fn () => TestDocumentFactory::simpleHardLink()->save());
        $this->email = self::arrange(fn () => TestDocumentFactory::simpleEmail()->save());
        $this->folder = self::arrange(fn () => TestDocumentFactory::simpleFolder()->save());
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_invalidated_when_document_is_updated(): void
    {
        $this->document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(['d42'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_invalidated_when_document_is_deleted(): void
    {
        $this->document->delete();

        $this->cacheManager->invalidateTags(['d42'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_document_is_of_type_hardlink_on_update(): void
    {
        $this->hardlink->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_document_is_of_type_hardlink_on_delete(): void
    {
        $this->hardlink->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_document_is_of_type_email_on_update(): void
    {
        $this->email->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_document_is_of_type_email_on_delete(): void
    {
        $this->email->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_document_is_of_type_folder_on_update(): void
    {
        $this->folder->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_document_is_of_type_folder_on_delete(): void
    {
        $this->folder->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => [
                'types' => [
                    'page' => false,
                ],
            ],
        ],
    ])]
    public function response_is_not_invalidated_when_document_type_is_disabled_on_update(): void
    {
        $this->document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => [
                'types' => [
                    'page' => false,
                ],
            ],
        ],
    ])]
    public function response_is_not_invalidated_when_document_type_is_disabled_on_delete(): void
    {
        $this->document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function response_is_not_invalidated_when_documents_are_disabled_on_update(): void
    {
        $this->document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function response_is_not_invalidated_when_documents_are_disabled_on_delete(): void
    {
        $this->document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }
}
