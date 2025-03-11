<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

final class PurgeChecker implements PurgeCheckerInterface
{
    public const TYPE_ASSET = 'asset';
    public const TYPE_OBJECT = 'object';
    public const TYPE_DOCUMENTS = 'document';
    /**
     * @var array<string,bool>
     */
    private const DEFAULT_TYPES = [self::TYPE_ASSET => true, self::TYPE_OBJECT => true, self::TYPE_DOCUMENTS => true];

    /**
     * @var array<string,bool>
     */
    private array $types;

    /**
     * @param array<string,bool> $types
     */
    public function __construct(array $types = self::DEFAULT_TYPES)
    {
        $this->types = $types;
    }

    public function isEnabled(string $type): bool
    {
        return $this->types[$type] ?? true;
    }

    public function disable(string $type): void
    {
        $this->types[$type] = false;
    }

    public function enable(string $type): void
    {
        $this->types[$type] = true;
    }
}
