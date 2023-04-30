<?php

namespace SteamApi;

class Config
{
    public string $storeUri       = 'https://store.steampowered.com';
    public int    $retries        = 5;
    public int    $retryTimeoutMs = 1000;
}
