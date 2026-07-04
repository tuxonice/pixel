<?php

declare(strict_types=1);

namespace App\Tests\Exception;

use App\Exception\RateLimitExceededException;
use PHPUnit\Framework\TestCase;

class RateLimitExceededExceptionTest extends TestCase
{
    public function testMessageContainsRetryAfter(): void
    {
        $e = new RateLimitExceededException(42);

        $this->assertStringContainsString('42', $e->getMessage());
    }

    public function testGetRetryAfter(): void
    {
        $e = new RateLimitExceededException(30);

        $this->assertSame(30, $e->getRetryAfter());
    }

    public function testIsRuntimeException(): void
    {
        $e = new RateLimitExceededException(10);

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }
}
