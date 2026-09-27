<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /appstore/reviews */
final readonly class AppstoreReviewPage implements FromArray
{
    /**
     * @internal
     *
     * @param list<AppstoreReview> $reviews
     */
    public function __construct(
        public string $appId,
        public string $country,
        public int $page,
        public array $reviews,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'appId'),
            Read::string($data, 'country'),
            Read::int($data, 'page'),
            Read::objects($data, 'reviews', AppstoreReview::class),
        );
    }
}
