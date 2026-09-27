<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /tiktok/profile/{username} */
final readonly class TiktokProfile implements FromArray
{
    /** @internal */
    public function __construct(
        public string $id,
        public string $username,
        public string $nickname,
        public string $bio,
        public string $bioLink,
        public string $avatar,
        public string $secUid,
        public int $followers,
        public int $following,
        public int $hearts,
        public int $videos,
        public bool $isPrivate,
        public bool $isVerified,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'username'),
            Read::string($data, 'nickname'),
            Read::string($data, 'bio'),
            Read::string($data, 'bio_link'),
            Read::string($data, 'avatar'),
            Read::string($data, 'sec_uid'),
            Read::int($data, 'followers'),
            Read::int($data, 'following'),
            Read::int($data, 'hearts'),
            Read::int($data, 'videos'),
            Read::bool($data, 'is_private'),
            Read::bool($data, 'is_verified'),
        );
    }
}
