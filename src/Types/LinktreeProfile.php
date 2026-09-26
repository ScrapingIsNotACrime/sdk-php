<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /linktree/profiles/{handle} */
final readonly class LinktreeProfile implements FromArray
{
    /** @param list<LinktreeLink> $links */
    public function __construct(
        public string $username,
        public string $title,
        public string $description,
        public string $avatar,
        public bool $isVerified,
        public string $url,
        public array $links,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'username'),
            Read::string($data, 'title'),
            Read::string($data, 'description'),
            Read::string($data, 'avatar'),
            Read::bool($data, 'is_verified'),
            Read::string($data, 'url'),
            Read::objects($data, 'links', LinktreeLink::class),
        );
    }
}
