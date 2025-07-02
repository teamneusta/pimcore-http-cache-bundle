<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use Pimcore\Model\Element\ElementInterface;

/**
 * @implements \IteratorAggregate<int, CacheTag>
 */
final class CacheTags implements \IteratorAggregate
{
    /**
     * @var list<CacheTag>
     */
    private readonly array $tags;

    /**
     * @no-named-arguments
     */
    public function __construct(CacheTag ...$tags)
    {
        $this->tags = $tags;
    }

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
     * @return \ArrayIterator<int, CacheTag>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tags);
    }

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
     * @return list<string>
     */
    public function toArray(): array
    {
        $tags = array_map(static fn (CacheTag $tag): string => $tag->toString(), $this->tags);
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
