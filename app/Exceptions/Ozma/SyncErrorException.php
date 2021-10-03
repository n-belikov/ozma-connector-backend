<?php

namespace App\Exceptions\Ozma;

use Exception;
use Psr\Http\Message\ResponseInterface;

class SyncErrorException extends Exception
{
    public static function fromResponse(string $message, ResponseInterface $response, array $values): SyncErrorException
    {
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

        $json = json_decode($response->getBody()->getContents(), true);
        if (is_array($json)) {
            $json = json_encode($json, $flags);
        } else {
            $json = $response->getBody()->getContents();
        }

        return new static(
            "[{$response->getStatusCode()}] Error: {$message}" . PHP_EOL . "Response: " . PHP_EOL . $json . PHP_EOL . "Values: " . PHP_EOL . json_encode($values, $flags)
        );
    }
}
