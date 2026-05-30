<?php

declare(strict_types=1);

namespace App\Exception;

class RateLimitExceededException extends \RuntimeException
{
    public function __construct(private readonly int $retryAfter)
    {
        parent::__construct(sprintf('Rate limit exceeded. Retry after %d seconds.', $retryAfter));
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
