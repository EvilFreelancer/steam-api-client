<?php

namespace SteamApi\Exceptions;

use Exception;

class EmptyResults extends Exception
{
    protected $message = 'Empty responce from remote server';
}
