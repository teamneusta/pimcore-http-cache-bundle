<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Pimcore\Model\Element\ElementInterface;

/**
 * @implements \IteratorAggregate<CacheTag>
 */
final class CacheTags implements \IteratorAggregate
{
    /**
     * @var CacheTag[]
     */
    private array $tags;

    public function __construct(CacheTag ...$tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param ElementInterface[] $elements
     */
    public static function fromElements(array $elements): self
    {
        return new self(...array_map([CacheTag::class, 'fromElement'], $elements));
    }

    /**
     * @return \ArrayIterator<int, CacheTag>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tags);
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        $tags = array_map(static fn (CacheTag $tag): string => $tag->asString(), $this->tags);
        natsort($tags);

        return $tags;
    }

    public function toString(): string
    {
        return implode(',', $this->toArray());
    }

    public function isEmpty(): bool
    {
        return 0 === \count($this->tags);
    }

    public function add(CacheTag $tag): void
    {
        $this->tags[] = $tag;
    }
}
