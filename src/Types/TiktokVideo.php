<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /tiktok/video/{videoId} — no documented example yet; refined from the smoke test. */
final readonly class TiktokVideo implements FromArray
{
    /** @param array<string, mixed> $extra */
    public function __construct(
        public ?string $id,
        public array $extra,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        $id = $data['id'] ?? null;
        $extra = $data;
        $resolvedId = null;
        if (is_string($id)) {
            $resolvedId = $id;
            unset($extra['id']);
        }

        return new self($resolvedId, $extra);
    }
}
