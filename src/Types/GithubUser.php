<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A user in a followers/following page. */
final readonly class GithubUser implements FromArray
{
    public function __construct(
        public string $username,
        public int $id,
        public string $avatar,
        public string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'username'),
            Read::int($data, 'id'),
            Read::string($data, 'avatar'),
            Read::string($data, 'url'),
        );
    }
}
