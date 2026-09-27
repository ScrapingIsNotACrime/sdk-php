<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /twitch/profiles/{handle} */
final readonly class TwitchProfile implements FromArray
{
    /** @internal */
    public function __construct(
        public string $id,
        public string $login,
        public string $displayName,
        public string $description,
        public string $avatar,
        public int $followers,
        public bool $isPartner,
        public bool $isAffiliate,
        public string $createdAt,
        public bool $isLive,
        /** Current viewer count while live; null whenever isLive is false. */
        public ?int $liveViewers,
        /** Null when the channel has never broadcast (or the info is unavailable). */
        public ?TwitchBroadcast $lastBroadcast,
        public string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'login'),
            Read::string($data, 'display_name'),
            Read::string($data, 'description'),
            Read::string($data, 'avatar'),
            Read::int($data, 'followers'),
            Read::bool($data, 'is_partner'),
            Read::bool($data, 'is_affiliate'),
            Read::string($data, 'created_at'),
            Read::bool($data, 'is_live'),
            Read::nullableInt($data, 'live_viewers'),
            Read::object($data, 'last_broadcast', TwitchBroadcast::class),
            Read::string($data, 'url'),
        );
    }
}
