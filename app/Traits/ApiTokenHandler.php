<?php

namespace App\Traits;

trait ApiTokenHandler
{
    protected function getToken(string $service): string
    {
        return config("api-services.{$service}.token");
    }

    protected function getApiUrl(string $service): string
    {
        return config("api-services.{$service}.url");
    }
}
