<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Pimcore\Model\Element\ElementInterface;

/**
 * @implements \IteratorAggregate<int, CacheTag>
 */
final class CacheTags implements \IteratorAggregate
{
    /**
     * @var CacheTag[]
     */
    public array $tags;

    public function __construct(CacheTag ...$tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param ElementInterface[] $elements
     */
    public static function fromElements(array $elements): self
    {
        return new self(...array_map(CacheTag::fromElement(...), $elements));
    }

    /**
     * @return \ArrayIterator<int, CacheTag>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tags);
    }

    public function addTag(CacheTag $tags): void
    {
        $this->tags[] = $tags;
    }

    public function addTags(CacheTags $tags): void
    {
        $this->tags = \array_merge($tags->tags, $this->tags->tags);
    }

    public function withoutDisabled(CacheTagChecker $checker): self
    {
        return new self(...array_filter($this->tags, $checker->isEnabled(...)));
    }

    /**
     * @return string[]
     */
    public function toArray(bool $types = false): array
    {
        $tags = array_map(static fn (CacheTag $tag): string => $tag->toString(), $this->tags);

        natsort($tags);

        return !$types ? $tags : array_map(static fn (string $tag, int $index): array => [
            'tag' => $tag,
            'type' => $this->tags[$index]->type,
        ], $tags, array_keys($tags));
    }

    public function toString(): string
    {
        return implode(',', $this->toArray());
    }

    public function isEmpty(): bool
    {
        return 0 === \count($this->tags);
    }
}
