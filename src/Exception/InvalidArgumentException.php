<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Exception;

final class InvalidArgumentException extends \InvalidArgumentException implements PimcoreHttpCacheException
{
    public static function becauseEmptyCacheTypeIsNotAllowed(): self
    {
        return new self('Cache type must not be empty.');
    }

    public static function becauseCacheTypeIsReserved(string $cacheType): self
    {
        return new self(\sprintf('The cache type "%s" is reserved for Pimcore elements.', $cacheType));
    }

    public static function becauseCacheTypeIsEmpty(): self
    {
        return new self('The cache type must not be empty.');
    }

    public static function becauseElementHasNoId(): self
    {
        return new self('The given element has no id.');
    }
}
