<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Exception;

use Neusta\Pimcore\HttpCacheBundle\CacheType;

final class InvalidArgumentException extends \InvalidArgumentException implements PimcoreHttpCacheException
{
    public static function becauseCacheTagIsEmpty(): self
    {
        return new self('Cache tag must not be empty.');
    }

    public static function becauseCacheTypeIsReserved(CacheType $type): self
    {
        return new self(\sprintf(
            'The cache type "%s" is reserved for Pimcore elements.',
            $type->toString(),
        ));
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
