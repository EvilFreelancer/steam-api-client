# SteamAPI PHP client

> Note: This library is still under development and not yet production-ready.

The SteamAPI PHP Client is a PHP library for interacting with the Steam API.
It provides an easy way to fetch data from the Steam platform, such as user
profiles, games, and reviews.

## Installation

To install the SteamAPI PHP Client, simply use Composer:

```shell
composer require evilfreelancer/steam-api-client
```

## Usage

### Configuration

First, you need to create a Config object with your desired configuration:

```php
use SteamApi\Config;

$config = new Config();
$config->storeUri = 'https://store.steampowered.com';
$config->retries = 5;
$config->retryTimeoutMs = 1000;
```

Next, create an instance of the HttpClient class and pass your Config object:

```php
use SteamApi\HttpClient;

$httpClient = new HttpClient($config);
```

### Getting Curator Reviews

To get reviews for a specific curator, you can use the Curator class:

```php
// Replace with a valid curator ID
$curatorId = 31790204; // Games-4-Programmers
$curator = $httpClient->store->curator($curatorId);

// Get the total number of reviews for the curator
$totalCount = $curator->getTotalCount();

// Get a limited number of reviews (default: last 10 reviews)
$reviews = $curator->getReviews();

// Get all available reviews
$allReviews = $curator->allReviews();
```

## License

This library is released under the MIT License. See the [LICENSE](./LICENSE) file for details.
