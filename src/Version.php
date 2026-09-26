<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

use Composer\InstalledVersions;

/** @internal */
final class Version
{
    public const PACKAGE = 'scrapingisnotacrime/sdk';

    public static function current(): string
    {
        if (!class_exists(InstalledVersions::class) || !InstalledVersions::isInstalled(self::PACKAGE)) {
            return 'dev';
        }
        $version = InstalledVersions::getPrettyVersion(self::PACKAGE);
        if ($version === null || $version === '' || str_starts_with($version, 'dev-')) {
            return 'dev';
        }

        return ltrim($version, 'v');
    }

    public static function userAgent(): string
    {
        return 'scrapingisnotacrime-php/' . self::current();
    }
}
