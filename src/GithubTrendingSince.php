<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

/** The period of GitHub's trending list. */
enum GithubTrendingSince: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
}
