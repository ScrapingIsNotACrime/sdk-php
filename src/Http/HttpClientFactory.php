<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Http;

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;

/**
 * Builds the SDK's own PSR-18 client: the SDK timeout, and redirects never
 * followed (the X-Api-Key header would be forwarded to another host).
 *
 * @internal
 */
final class HttpClientFactory
{
    /** @param 'symfony'|'guzzle'|'discovery'|null $prefer forces one branch (tests) */
    public static function create(float $timeout, ?string $prefer = null): ClientInterface
    {
        $symfony = class_exists(\Symfony\Component\HttpClient\Psr18Client::class) && class_exists(\Symfony\Component\HttpClient\HttpClient::class);
        if (($prefer === null && $symfony) || $prefer === 'symfony') {
            return new \Symfony\Component\HttpClient\Psr18Client(\Symfony\Component\HttpClient\HttpClient::create([
                'timeout' => $timeout,
                'max_duration' => $timeout,
                'max_redirects' => 0,
            ]));
        }
        if (($prefer === null && class_exists(\GuzzleHttp\Client::class)) || $prefer === 'guzzle') {
            return new \GuzzleHttp\Client([
                'timeout' => $timeout,
                'connect_timeout' => $timeout,
                'allow_redirects' => false,
                'http_errors' => false,
            ]);
        }
        try {
            // The discovered client keeps its own timeout and redirect settings.
            return Psr18ClientDiscovery::find();
        } catch (NotFoundException $e) {
            throw new \LogicException(
                'No PSR-18 HTTP client found: run "composer require symfony/http-client" (or guzzlehttp/guzzle), or pass httpClient.',
                0,
                $e,
            );
        }
    }
}
