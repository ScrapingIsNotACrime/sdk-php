<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

use Http\Discovery\Exception\NotFoundException as DiscoveryNotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use ScrapingIsNotACrime\Http\HttpClientFactory;
use ScrapingIsNotACrime\Http\HttpCore;

/**
 * The ScrapingIsNotACrime API client.
 *
 * Without $httpClient the SDK builds its own PSR-18 client (Symfony
 * HttpClient, else Guzzle) with $timeout per attempt and redirects never
 * followed. A client you pass is used as is: set its timeout and disable
 * redirects yourself.
 */
final class Client
{
    public readonly Resources\Instagram $instagram;
    public readonly Resources\Tiktok $tiktok;
    public readonly Resources\Youtube $youtube;
    public readonly Resources\Appstore $appstore;
    public readonly Resources\Github $github;
    public readonly Resources\Hackernews $hackernews;
    public readonly Resources\Bluesky $bluesky;
    public readonly Resources\Twitch $twitch;
    public readonly Resources\Linktree $linktree;

    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        float $timeout = 30.0,
        int $maxRetries = 2,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ) {
        $config = Config::resolve($apiKey, $baseUrl, $timeout, $maxRetries);
        $http = new HttpCore(
            $config,
            $httpClient ?? HttpClientFactory::create($timeout),
            self::requestFactory($requestFactory),
        );
        $this->instagram = new Resources\Instagram($http);
        $this->tiktok = new Resources\Tiktok($http);
        $this->youtube = new Resources\Youtube($http);
        $this->appstore = new Resources\Appstore($http);
        $this->github = new Resources\Github($http);
        $this->hackernews = new Resources\Hackernews($http);
        $this->bluesky = new Resources\Bluesky($http);
        $this->twitch = new Resources\Twitch($http);
        $this->linktree = new Resources\Linktree($http);
    }

    /**
     * @internal exposed for testing the discovery-failure path
     *
     * @param \Closure(): RequestFactoryInterface|null $find forces the discovery call (tests)
     */
    public static function requestFactory(?RequestFactoryInterface $given, ?\Closure $find = null): RequestFactoryInterface
    {
        if ($given !== null) {
            return $given;
        }
        $find ??= static fn(): RequestFactoryInterface => Psr17FactoryDiscovery::findRequestFactory();
        try {
            return $find();
        } catch (DiscoveryNotFoundException $e) {
            throw new \LogicException(
                'No PSR-17 request factory found: run "composer require nyholm/psr7", or pass requestFactory.',
                0,
                $e,
            );
        }
    }
}
