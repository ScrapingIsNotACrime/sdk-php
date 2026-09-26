<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

/** One of Hacker News' story lists. */
enum HackernewsFeed: string
{
    case Top = 'top';
    case New = 'new';
    case Best = 'best';
    case Ask = 'ask';
    case Show = 'show';
    case Job = 'job';
}
