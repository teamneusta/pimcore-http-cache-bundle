<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;
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

    /**
     * Indicates that this cache type is never considered empty.
     *
     * @return bool Always returns false.
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * Returns the full string value of the associated element type.
     *
     * @return string The identifier representing the element type.
     */
    public function identifier(): string
    {
        return $this->type->value;
    }
}
