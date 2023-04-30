<?php

namespace SteamApi\Endpoints;

use SteamApi\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class Curator
{
    private int        $curatorId;
    private HttpClient $httpClient;
    private Crawler    $crawler;

    public function __construct(int $curatorId, HttpClient $httpClient)
    {
        $this->curatorId  = $curatorId;
        $this->httpClient = $httpClient;
        $this->crawler    = new Crawler();
    }

    private function cleanUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        $query     = [];

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }

        return $parsedUrl['scheme'] . '://' . $parsedUrl['host']
            . $parsedUrl['path'] . '?curator_clanid=' . $query['curator_clanid'];
    }

    /**
     * @throws \JsonException
     */
    private function parseRecommendations($content): array
    {
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        $html = $json['results_html'];
        $this->crawler->clear();
        $this->crawler->addHtmlContent($html);

        $reviewElements = $this->crawler->filter('.recommendation');
        $reviews        = [];
        foreach ($reviewElements as $reviewElement) {
            $reviewCrawler = new Crawler($reviewElement);
            $review        = [];

            // Type of review
            $reviewType = $reviewCrawler->filter('.recommendation_type_ctn span')->first()->text();
            if (str_starts_with($reviewType, 'Recommended')) {
                $review['review'] = 'Recommended';
            } elseif (str_starts_with($reviewType, 'Informational')) {
                $review['review'] = 'Informational';
            } elseif (str_starts_with($reviewType, 'Not Recommended')) {
                $review['review'] = 'Not Recommended';
            } else {
                $review['review'] = 'Unknown';
            }

            // Additional information
            $review['description'] = trim($reviewCrawler->filter('.recommendation_desc')->text());
            $review['url']         = $this->cleanUrl($reviewCrawler->filter('.store_capsule')->attr('href'));

            // Details about price
            $discountBlock      = $reviewCrawler->filter('.discount_block');
            $discountBlockClass = $discountBlock->attr('class');
            if ($discountBlock->count()) {
                // TODO: Extract currency and price, probably with help of Money library
                // No discount, just a price
                if (!str_contains($discountBlockClass, 'empty') && str_contains($discountBlockClass, 'no_discount')) {
                    $review['final_price'] = $reviewCrawler->filter('.discount_final_price')->text();
                }
                // If with discount
                if ($discountBlock->filter('.discount_pct')->count()) {
                    $review['final_price']      = $reviewCrawler->filter('.discount_final_price')->text();
                    $review['original_price']   = $reviewCrawler->filter('.discount_original_price')->text();
                    $discountText               = $reviewCrawler->filter('.discount_pct')->text();
                    $review['discount_percent'] = str_replace(['-', '%'], '', $discountText);
                }
            }

            // Push to array of reviews
            $reviews[] = $review;
        }

        return $reviews;
    }

    /**
     * Get limited amount ff reviews, of just a last ten reviews by default
     *
     * @throws \JsonException
     */
    public function getReviews(int $start = 0, int $count = 10): ?array
    {
        // Small fix, because Steam API doesn't allow to get more than 10 records per request
        if ($count > 10) {
            $count = 10;
        }

        $uri      = "/curator/{$this->curatorId}/ajaxgetfilteredrecommendations/render/?query=&start=$start&count=$count&dynamic_data=&tagids=&sort=recent&app_types=&curations=&reset=false";
        $response = $this->httpClient->sendRequest('GET', $uri);

        if ($response['success']) {
            return $this->parseRecommendations($response['body']);
        }

        return null;
    }

    /**
     * @throws \JsonException
     */
    public function getTotalCount(): int
    {
        $uri      = "/curator/{$this->curatorId}/ajaxgetfilteredrecommendations/render/?query&start=0&count=1&reset=false";
        $response = $this->httpClient->sendRequest('GET', $uri);

        if ($response['success']) {
            $json = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
            return $json['total_count'] ?? 0;
        }

        return 0;
    }

    /**
     * Extract all reviews, which API can return
     *
     * @throws \JsonException
     */
    public function allReviews(): array
    {
        $reviews    = [];
        $totalCount = $this->getTotalCount();
        $count      = 10;

        for ($start = 0; $start < $totalCount; $start += $count) {
            $recommendations = $this->getReviews($start, $count);
            if (empty($recommendations)) {
                break;
            }
            $reviews[] = $recommendations;
        }

        return array_merge(...$reviews);
    }
}
