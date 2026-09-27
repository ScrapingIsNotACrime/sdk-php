<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\LinktreeProfile;

final class Linktree
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /linktree/profiles/{handle}: a Linktree profile with its links. */
    public function profile(string $handle): LinktreeProfile
    {
        return LinktreeProfile::fromArray($this->http->getObject(Routes::linktreeProfile($handle)));
    }
}
