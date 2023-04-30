<?php

namespace SteamApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use SteamApi\Endpoints\Store;

class HttpClient
{
    private Client $client;
    private Config $config;
    public Store  $store;

    public function __construct(Config $config = null)
    {
        // If client not provided config, then use defaults
        if (null === $config) {
            $config = new Config();
        }

        // Init client and other stuff
        $this->client = new Client(['base_uri' => $config->storeUri]);
        $this->config = $config;
        $this->store  = new Store($this);
    }

    public function sendRequest(string $method, string $uri, array $options = []): ?array
    {
        for ($remainingRetries = $this->config->retries; $remainingRetries > 0; $remainingRetries--) {
            try {
                $response = $this->client->request($method, $uri, $options);
                return $this->handleResponse($response);
            } catch (GuzzleException|RequestException $exception) {
                if ($remainingRetries === 1) {
                    return $this->handleException($exception);
                }
                usleep($this->config->retryTimeoutMs * 1000);
            }
        }
        return null;
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body       = $response->getBody()->getContents();

        if ($statusCode >= 200 && $statusCode < 300) {
            return [
                'success'     => true,
                'status_code' => $statusCode,
                'body'        => $body,
            ];
        }

        return [
            'success'     => false,
            'status_code' => $statusCode,
            'body'        => $body,
        ];
    }

    private function handleException(RequestException $exception): array
    {
        $statusCode = $exception->getResponse() ? $exception->getResponse()->getStatusCode() : 0;

        return [
            'success'       => false,
            'status_code'   => $statusCode,
            'error_message' => $exception->getMessage(),
        ];
    }
}
