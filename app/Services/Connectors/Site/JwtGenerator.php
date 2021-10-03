<?php

namespace App\Services\Connectors\Site;

/**
 * Class JwtGenerator
 * @package App\Services\Connectors\Site
 */
class JwtGenerator
{
    private string $signKey = "";

    /**
     * @param string $key
     */
    public function __construct(string $key = "")
    {
        $this->setSignKey($key);
    }

    /**
     * @param string $key
     */
    private function setSignKey(string $key): void
    {
        $this->signKey = $key;
    }

    /**
     * @return string
     */
    private function getHeader(): string
    {
        return json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    }

    /**
     * @param string $base64UrlHeader
     * @param string $base64UrlPayload
     * @return string
     */
    private function makeSign(string $base64UrlHeader, string $base64UrlPayload): string
    {
        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->signKey, true);

        // Encode Signature to Base64Url String
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    }

    /**
     * @param array $payload
     * @return string
     */
    public function make(array $payload = []): string
    {
        $payload = json_encode($payload);

        // Encode Header to Base64Url String
        $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($this->getHeader()));

        // Encode Payload to Base64Url String
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = $this->makeSign($header, $payload);

        return implode(".", [$header, $payload, $signature]);
    }

    /**
     * @param string $jwt
     * @return array|null
     */
    public function read(string $jwt): ?array
    {
        list($header, $payload, $signature) = explode(".", $jwt);

        $sign = $this->makeSign($header, $payload);

        if ($sign !== $signature) {
            return null;
        }

        return json_decode(base64_decode($payload), true);
    }
}