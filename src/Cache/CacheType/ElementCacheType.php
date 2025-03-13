<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;

/**
 * @internal
 */
final class ElementCacheType
{
    public function __construct(
        private readonly ElementType $type,
    ) {
    }

    public static function isReserved(string $value): bool
    {
        static $prefixes;
        $prefixes ??= array_map(fn (string $value) => $value[0], array_column(ElementType::cases(), 'value'));

        return \in_array($value, $prefixes, true);
    }

    public function applyTo(string $tag): string
    {
        return $this->type->value[0] . $tag;
    }

    public function toString(): string
    {
        return $this->type->value;
    }
}
