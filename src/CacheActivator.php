<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;

final class CacheActivator
{
    private bool $isCachingActive = true;

    public function __construct(
        private readonly \Closure $responseTagger,
    ) {
    }

    public function isCachingActive(): bool
    {
        return $this->isCachingActive;
    }

    public function activateCaching(): void
    {
        $this->isCachingActive = true;
    }

    public function deactivateCaching(): void
    {
        $this->isCachingActive = false;
    }

    /**
     * @template T
     *
     * @param \Closure(): (T|\Generator<array-key, CacheTag|CacheTags, null, T>) $fn
     *
     * @return T
     */
    public function withoutAutomaticTagging(\Closure $fn): mixed
    {
        $previous = $this->isCachingActive;
        $this->isCachingActive = false;

        $tags = new CacheTags();

        try {
            $result = $fn();

            if ($result instanceof \Generator) {
                $position = 0;
                foreach ($result as $key => $value) {
                    if (!$value instanceof CacheTag && !$value instanceof CacheTags) {
                        throw new \LogicException(\sprintf(
                            'Invalid yielded value at position %d (key: %s): Expected only "%s" or "%s", got "%s".',
                            $position,
                            \is_int($key) || \is_string($key) ? $key : get_debug_type($key),
                            CacheTag::class,
                            CacheTags::class,
                            get_debug_type($value),
                        ));
                    }

                    $tags = $tags->with($value);
                    ++$position;
                }
                $result = $result->getReturn();
            }
        } finally {
            $this->isCachingActive = $previous;
            ($this->responseTagger)()->tag($tags);
        }

        return $result;
    }
}
