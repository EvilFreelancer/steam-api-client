<?php

use PHPUnit\Framework\TestCase;
use SteamApi\Config;
use SteamApi\HttpClient;

class CuratorTest extends TestCase
{
    private HttpClient $httpClient;

    protected function setUp(): void
    {
        $config = new Config();
        $this->httpClient = new HttpClient($config);
    }

    public function testGetTotalCount(): void
    {
        $store = $this->httpClient->store;
        $curatorId = 123456; // Replace with a valid curator ID
        $curator = $store->curator($curatorId);

        $totalCount = $curator->getTotalCount();
        $this->assertIsInt($totalCount);
    }

    public function testGetReviews(): void
    {
        $store = $this->httpClient->store;
        $curatorId = 123456; // Replace with a valid curator ID
        $curator = $store->curator($curatorId);

        $reviews = $curator->getReviews();
        $this->assertIsArray($reviews);
    }

    public function testAllReviews(): void
    {
        $store = $this->httpClient->store;
        $curatorId = 123456; // Replace with a valid curator ID
        $curator = $store->curator($curatorId);

        $allReviews = $curator->allReviews();
        $this->assertIsArray($allReviews);
    }
}
