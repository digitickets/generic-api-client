<?php

namespace GenericApiClient;

use GenericApiClient\Exceptions\MalformedApiResponseException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class JsonParser
{
    public static function parseJsonResponse(ResponseInterface $response): array
    {
        try {
            return self::jsonDecode($response->getBody(), true);
        } catch (InvalidArgumentException $e) {
            // Re-throwing the same exception but containing the response so the problem can be debugged.
            throw new MalformedApiResponseException(
                $response,
                rtrim($e->getMessage(), '.').". Response body:\n".$response->getBody(),
                $e->getCode(),
                $e
            );
        }
    }

    public static function jsonDecode(string $json, bool $associative = false): array
    {
        $data = json_decode($json, $associative);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                'json_decode error: '.json_last_error_msg()
            );
        }

        return $data;
    }
}
