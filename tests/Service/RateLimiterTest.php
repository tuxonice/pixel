<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\RateLimitExceededException;
use App\Service\RateLimiter;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private string $storageDir;

    protected function setUp(): void
    {
        $this->storageDir = sys_get_temp_dir() . '/pixel_rate_' . uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->storageDir)) {
            foreach (glob($this->storageDir . '/*') as $file) {
                unlink($file);
            }

            rmdir($this->storageDir);
        }
    }

    public function testAllowsRequestsUnderLimit(): void
    {
        $limiter = new RateLimiter($this->storageDir, 5, 60);

        for ($i = 0; $i < 5; $i++) {
            $limiter->check('127.0.0.1');
        }

        $this->assertTrue(true);
    }

    public function testThrowsWhenLimitExceeded(): void
    {
        $limiter = new RateLimiter($this->storageDir, 3, 60);

        $this->expectException(RateLimitExceededException::class);

        for ($i = 0; $i < 4; $i++) {
            $limiter->check('127.0.0.1');
        }
    }

    public function testDifferentIpsHaveSeparateLimits(): void
    {
        $limiter = new RateLimiter($this->storageDir, 2, 60);

        $limiter->check('10.0.0.1');
        $limiter->check('10.0.0.1');
        $limiter->check('10.0.0.2');
        $limiter->check('10.0.0.2');

        $this->assertTrue(true);
    }

    public function testCreatesStorageDirIfMissing(): void
    {
        $this->assertDirectoryDoesNotExist($this->storageDir);

        $limiter = new RateLimiter($this->storageDir, 5, 60);
        $limiter->check('127.0.0.1');

        $this->assertDirectoryExists($this->storageDir);
    }

    public function testRetryAfterIsPositive(): void
    {
        $limiter = new RateLimiter($this->storageDir, 1, 60);
        $limiter->check('127.0.0.1');

        try {
            $limiter->check('127.0.0.1');
            $this->fail('Expected RateLimitExceededException');
        } catch (RateLimitExceededException $e) {
            $this->assertGreaterThan(0, $e->getRetryAfter());
        }
    }

    public function testCleanupStaleFilesRemovesOldFilesOnly(): void
    {
        mkdir($this->storageDir, 0755, true);

        $staleFile = $this->storageDir . '/stale.json';
        $freshFile = $this->storageDir . '/fresh.json';

        file_put_contents($staleFile, '[]');
        file_put_contents($freshFile, '[]');
        touch($staleFile, time() - 120);
        touch($freshFile, time());

        $limiter = new RateLimiter($this->storageDir, 100, 60);

        $reflection = new \ReflectionMethod($limiter, 'cleanupStaleFiles');
        $reflection->setAccessible(true);
        $reflection->invoke($limiter);

        $this->assertFileDoesNotExist($staleFile);
        $this->assertFileExists($freshFile);

        unlink($freshFile);
    }
}
