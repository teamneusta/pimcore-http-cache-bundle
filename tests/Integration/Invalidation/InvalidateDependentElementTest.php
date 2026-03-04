<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Pimcore\Model\DataObject\TestObject;
use Pimcore\Model\Document\Page;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class InvalidateDependentElementTest extends ConfigurableKernelTestCase
{
    use ArrangeCacheTest;
    use ProphecyTrait;
    use ResetDatabase;

    /** @var ObjectProphecy<CacheManager> */
    private ObjectProphecy $cacheManager;

    private TestObject $sourceObject;

    private Page $dependentPage;

    private TestObject $dependentObject;

    protected function setUp(): void
    {
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheManager->invalidateTags(Argument::any())->willReturn($this->cacheManager->reveal());
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());

        $this->sourceObject = self::arrange(static fn () => TestObjectFactory::simpleObject(100, 'source_object')->save());
        $this->dependentPage = self::arrange(fn () => TestDocumentFactory::simplePage(200, 'dep_page', $this->sourceObject)->save());
        $this->dependentObject = self::arrange(fn () => TestObjectFactory::simpleObject(300, 'dep_object', [$this->sourceObject])->save());
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'invalidate_dependent_elements' => [
                    'enabled' => true,
                    'types' => [
                        'documents' => true,
                    ],
                ],
            ],
            'documents' => true,
        ],
    ])]
    public function dependent_document_is_invalidated_when_source_object_is_updated(): void
    {
        $this->sourceObject->setContent('Updated content')->save();

        $this->cacheManager->invalidateTags(['o' . $this->sourceObject->getId()])->shouldHaveBeenCalledOnce();
        $this->cacheManager->invalidateTags(['d' . $this->dependentPage->getId()])->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'invalidate_dependent_elements' => [
                    'enabled' => true,
                    'types' => [
                        'documents' => true,
                    ],
                ],
            ],
            'documents' => [
                'types' => [
                    'page' => false,  // globally disable page type
                ],
            ],
        ],
    ])]
    public function dependent_document_is_not_invalidated_when_document_type_is_globally_disabled(): void
    {
        $this->sourceObject->setContent('Updated content')->save();

        $this->cacheManager->invalidateTags(['o' . $this->sourceObject->getId()])->shouldHaveBeenCalledOnce();
        $this->cacheManager->invalidateTags(['d' . $this->dependentPage->getId()])->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'invalidate_dependent_elements' => [
                    'enabled' => true,
                    'types' => [
                        'documents' => [
                            'enabled' => true,
                            'types' => [
                                'page' => false,  // disable page in dependent config
                            ],
                        ],
                    ],
                ],
            ],
            'documents' => true,
        ],
    ])]
    public function dependent_document_is_not_invalidated_when_page_type_is_disabled_in_dependent_config(): void
    {
        $this->sourceObject->setContent('Updated content')->save();

        $this->cacheManager->invalidateTags(['o' . $this->sourceObject->getId()])->shouldHaveBeenCalledOnce();
        $this->cacheManager->invalidateTags(['d' . $this->dependentPage->getId()])->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'invalidate_dependent_elements' => [
                    'enabled' => true,
                    'types' => [
                        'objects' => [
                            'enabled' => true,
                            'classes' => [
                                'TestObject' => false,  // disable this class in dependent config
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ])]
    public function dependent_object_is_not_invalidated_when_class_is_disabled_in_dependent_config(): void
    {
        $this->sourceObject->setContent('Updated content')->save();

        $this->cacheManager->invalidateTags(['o' . $this->sourceObject->getId()])->shouldHaveBeenCalledOnce();
        $this->cacheManager->invalidateTags(['o' . $this->dependentObject->getId()])->shouldNotHaveBeenCalled();
    }
}
