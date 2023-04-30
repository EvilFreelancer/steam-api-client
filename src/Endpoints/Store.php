<?php

namespace SteamApi\Endpoints;

use SteamApi\HttpClient;

class Store
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function curator(int $curatorId): Curator
    {
        return new Curator($curatorId, $this->httpClient);
    }
}
