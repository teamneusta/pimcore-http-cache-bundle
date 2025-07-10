<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Pimcore\Model\Element\ElementInterface;

/**
 * @implements \IteratorAggregate<int, CacheTag>
 */
final class CacheTags implements \IteratorAggregate
{
    /**
     * @var array<string, CacheTag>
     */
    public readonly array $tags;

    /**
     * Initializes a new CacheTags collection from the provided CacheTag objects, indexing them by their tag string.
     *
     * @param CacheTag ...$tags One or more CacheTag instances to include in the collection.
     */
    public function __construct(CacheTag ...$tags)
    {
        $indexedTags = [];
        foreach ($tags as $tag) {
            $indexedTags[$tag->tag] = $tag;
        }

        $this->tags = $indexedTags;
    }

    /**
     * Creates a CacheTags instance from a single tag string.
     *
     * @param string $tag The cache tag string.
     * @param CacheType|null $type Optional cache type for the tag.
     * @return self A CacheTags instance containing the specified tag.
     */
    public static function fromString(string $tag, ?CacheType $type = null): self
    {
        return new self(CacheTag::fromString($tag, $type));
    }

    /**
     * @param list<string> $tags
     */
    public static function fromStrings(array $tags, ?CacheType $type = null): self
    {
        return new self(...array_map(fn ($tag) => CacheTag::fromString($tag, $type), $tags));
    }

    public static function fromElement(ElementInterface $element): self
    {
        return new self(CacheTag::fromElement($element));
    }

    /**
     * @param list<ElementInterface> $elements
     */
    public static function fromElements(array $elements): self
    {
        return new self(...array_map(CacheTag::fromElement(...), $elements));
    }

    /**
     * Returns an iterator over the collection of `CacheTag` objects.
     *
     * @return \ArrayIterator<int, CacheTag> An iterator for traversing the cache tags.
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator(array_values($this->tags));
    }

    /**
     * Returns a new CacheTags instance containing the current tags and additional tags or collections.
     *
     * Accepts one or more CacheTag objects or CacheTags instances, merging all tags into a new collection.
     *
     * @param CacheTag|self ...$tags Additional tags or CacheTags collections to include.
     * @return self A new CacheTags instance with the combined tags.
     */
    public function with(CacheTag|self ...$tags): self
    {
        $newTags = $this->tags;
        foreach ($tags as $tag) {
            if ($tag instanceof self) {
                $newTags = [...$newTags, ...$tag->tags];
            } else {
                $newTags[] = $tag;
            }
        }

        return new self(...$newTags);
    }

    public function withoutDisabled(CacheTagChecker $checker): self
    {
        return new self(...array_filter($this->tags, $checker->isEnabled(...)));
    }

    /**
     * Returns a naturally sorted list of tag strings from the collection.
     *
     * @return list<string> The sorted tag strings.
     */
    public function toArray(): array
    {
        $tags = array_map(
            static fn (CacheTag $tag): string => $tag->toString(), array_values($this->tags),
        );

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
}
