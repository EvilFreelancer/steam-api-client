<?php

require_once __DIR__ . '/../vendor/autoload.php';

$httpClient = new \SteamApi\HttpClient();

$curatorId = 31790204; // Games-4-Programmers

$filteredReviews = $httpClient->store->curator($curatorId)->getReviews(210, 10);
dump($filteredReviews);

$allReviews = $httpClient->store->curator($curatorId)->allReviews();
dump($allReviews);
