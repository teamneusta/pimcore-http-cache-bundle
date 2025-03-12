<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

final class PurgeChecker implements PurgeCheckerInterface
{
    /**
     * @param array<string, bool> $types
     */
    public function __construct(
        private array $types,
    ) {
    }

    public function isEnabled(string $type): bool
    {
        return $this->types[$type] ?? true;
    }

    public function disable(string $type): void
    {
        $this->types[$type] = false;
    }

    public function enable(string $type): void
    {
        $this->types[$type] = true;
    }
}
