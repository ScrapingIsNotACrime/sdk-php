<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/profile/{username}/timeline/latest */
final readonly class InstagramLatestPosts implements FromArray
{
    /** @param list<InstagramLatestPostMedia> $medias */
    public function __construct(
        public int $count,
        public int $latestCount,
        public array $medias,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::int($data, 'count'),
            Read::int($data, 'latest_count'),
            Read::objects($data, 'medias', InstagramLatestPostMedia::class),
        );
    }
}
