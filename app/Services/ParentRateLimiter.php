<?php
namespace Alprof\Services;

use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

final class ParentRateLimiter
{
    private RateLimiterFactory $factory;

    public function __construct()
    {
        $this->factory = new RateLimiterFactory(
            [
                'id' => 'parent_portal',
                'policy' => 'sliding_window',
                'limit' => 10,
                'interval' => 900,
            ],
            new InMemoryStorage()
        );
    }

    public function consume(string $key): bool
    {
        $limiter = $this->factory->create($key);
        $limit = $limiter->consume(1);
        return $limit->isAccepted();
    }

    public function remaining(string $key): int
    {
        $limiter = $this->factory->create($key);
        return $limiter->consume(1)->getRemainingTokens() + 1;
    }
}
