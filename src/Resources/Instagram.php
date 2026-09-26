<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\InstagramContact;
use ScrapingIsNotACrime\Types\InstagramDownload;
use ScrapingIsNotACrime\Types\InstagramHighlight;
use ScrapingIsNotACrime\Types\InstagramHighlights;
use ScrapingIsNotACrime\Types\InstagramLatestPosts;
use ScrapingIsNotACrime\Types\InstagramMedia;
use ScrapingIsNotACrime\Types\InstagramMediaDetail;
use ScrapingIsNotACrime\Types\InstagramProfile;
use ScrapingIsNotACrime\Types\InstagramReel;
use ScrapingIsNotACrime\Types\InstagramShortcodeId;
use ScrapingIsNotACrime\Types\InstagramTimelinePage;

final class Instagram
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /instagram/profile/{username} — username without @. */
    public function profile(string $username): InstagramProfile
    {
        return InstagramProfile::fromArray($this->http->getObject(Routes::instagramProfile($username)));
    }

    /** GET /instagram/profile/{username}/contact — public business contact (email, phone, address). */
    public function contact(string $username): InstagramContact
    {
        return InstagramContact::fromArray($this->http->getObject(Routes::instagramContact($username)));
    }

    /** GET /instagram/profile/{username}/timeline/latest — the first page of posts. */
    public function latestPosts(string $username): InstagramLatestPosts
    {
        return InstagramLatestPosts::fromArray($this->http->getObject(Routes::instagramLatestPosts($username)));
    }

    /**
     * GET /instagram/profile/{username}/timeline — full history, cursor-paginated; count 1-50 (default 12).
     *
     * @return Page<InstagramMedia, InstagramTimelinePage>
     */
    public function posts(string $username, ?int $count = null, ?string $cursor = null): Page
    {
        /** @var Page<InstagramMedia, InstagramTimelinePage> */
        return $this->http->page(Routes::instagramPosts($username, $count, $cursor));
    }

    /** GET /instagram/profile/{username}/highlights. */
    public function highlights(string $username): InstagramHighlights
    {
        return InstagramHighlights::fromArray($this->http->getObject(Routes::instagramHighlights($username)));
    }

    /** GET /instagram/highlights/{highlightId}. */
    public function highlight(string $highlightId): InstagramHighlight
    {
        return InstagramHighlight::fromArray($this->http->getObject(Routes::instagramHighlight($highlightId)));
    }

    /** GET /instagram/profile/{username}/media/{mediaId}. */
    public function mediaById(string $username, string $mediaId): InstagramMediaDetail
    {
        return InstagramMediaDetail::fromArray($this->http->getObject(Routes::instagramMediaById($username, $mediaId)));
    }

    /** GET /instagram/media/{shortcode} — shortcode from instagram.com/p/{shortcode}/. */
    public function media(string $shortcode): InstagramMediaDetail
    {
        return InstagramMediaDetail::fromArray($this->http->getObject(Routes::instagramMedia($shortcode)));
    }

    /** GET /instagram/media/{shortcode}/download — assets[0] is the best primary asset. */
    public function download(string $shortcode): InstagramDownload
    {
        return InstagramDownload::fromArray($this->http->getObject(Routes::instagramDownload($shortcode)));
    }

    /** GET /instagram/media/{shortcode}/id. */
    public function shortcodeToId(string $shortcode): InstagramShortcodeId
    {
        return InstagramShortcodeId::fromArray($this->http->getObject(Routes::instagramShortcodeToId($shortcode)));
    }

    /** GET /instagram/media/id/{mediaId}. */
    public function idToShortcode(string $mediaId): InstagramShortcodeId
    {
        return InstagramShortcodeId::fromArray($this->http->getObject(Routes::instagramIdToShortcode($mediaId)));
    }

    /** GET /instagram/reels/{shortcode}. */
    public function reel(string $shortcode): InstagramReel
    {
        return InstagramReel::fromArray($this->http->getObject(Routes::instagramReel($shortcode)));
    }
}
