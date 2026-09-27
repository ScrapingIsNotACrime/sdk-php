<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Exception;

/** Any other status, a 2xx without the JSON envelope, a redirect, or data of an unexpected shape. */
final class ApiException extends ScrapingIsNotACrimeException {}
