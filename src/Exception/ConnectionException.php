<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Exception;

/** Network failure or timeout (retried); getPrevious() is the client's exception. */
final class ConnectionException extends ScrapingIsNotACrimeException {}
