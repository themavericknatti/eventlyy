<?php

namespace Ace\Middlewares;

use Ace\Application;
use Ace\Middleware;

class RateLimitMiddleware extends Middleware
{
    private int $limit = 120; // 120 requests
    private int $window = 60; // per 60 seconds

    protected function run(): void
    {
        // Skip rate limiting in CLI
        if (php_sapi_name() === 'cli') {
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $cacheDir = Application::$ROOT_DIR . '/storage/cache/ratelimits';
        
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir . '/rl_' . md5($ip) . '.txt';
        $now = time();
        $count = 1;
        $windowStart = $now;

        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content !== false && str_contains($content, ':')) {
                [$savedCount, $savedTime] = explode(':', trim($content), 2);
                $savedCount = (int)$savedCount;
                $savedTime = (int)$savedTime;

                if (($now - $savedTime) < $this->window) {
                    $count = $savedCount + 1;
                    $windowStart = $savedTime;
                }
            }
        }

        if ($count > $this->limit) {
            http_response_code(429);
            header('Retry-After: ' . max(1, $this->window - ($now - $windowStart)));
            throw new \Exception("Too Many Requests. Please wait a moment before trying again.", 429);
        }

        @file_put_contents($file, "{$count}:{$windowStart}", LOCK_EX);

        // Probabilistic lightweight garbage collection (1 in 500 requests)
        if (mt_rand(1, 500) === 1) {
            $files = @glob($cacheDir . '/rl_*.txt') ?: [];
            foreach ($files as $f) {
                if (($now - @filemtime($f)) > ($this->window * 2)) {
                    @unlink($f);
                }
            }
        }
    }
}

