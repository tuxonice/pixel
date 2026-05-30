<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\RateLimitExceededException;

class RateLimiter
{
    public function __construct(
        private readonly string $storageDir,
        private readonly int $maxRequests,
        private readonly int $windowSeconds,
    ) {}

    public function check(string $ip): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        $file = $this->storageDir . '/' . md5($ip) . '.json';
        $now  = time();

        $fp = fopen($file, 'c+');
        if ($fp === false) {
            return;
        }

        flock($fp, LOCK_EX);

        $data = [];
        $content = stream_get_contents($fp);

        if ($content !== false && $content !== '') {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $windowStart = $now - $this->windowSeconds;
        $data = array_filter($data, fn(int $ts) => $ts > $windowStart);
        $data = array_values($data);

        if (count($data) >= $this->maxRequests) {
            flock($fp, LOCK_UN);
            fclose($fp);

            $oldestTimestamp = min($data);
            $retryAfter = ($oldestTimestamp + $this->windowSeconds) - $now;

            throw new RateLimitExceededException(max(1, $retryAfter));
        }

        $data[] = $now;

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));

        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
