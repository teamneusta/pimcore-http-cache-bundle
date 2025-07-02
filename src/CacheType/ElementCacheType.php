<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheType;

use Neusta\Pimcore\HttpCacheBundle\CacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;

final class ElementCacheType implements CacheType
{
    public function __construct(
        public readonly ElementType $type,
    ) {
    }

    /**
     * @internal
     */
    public static function isReserved(string $value): bool
    {
        static $prefixes;
        $prefixes ??= array_map(fn (string $value) => $value[0], array_column(ElementType::cases(), 'value'));

        return \in_array($value, $prefixes, true);
    }

    public function applyTo(string $tag): string
    {
        return $this->toString() . $tag;
    }

    public function toString(): string
    {
        return $this->type->value[0];
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
