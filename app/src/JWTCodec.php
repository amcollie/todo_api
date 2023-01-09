<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\InvalidSignatureException;
use Exception;

class JWTCodec
{
    public function __construct(private string $secretKey) {}

    public function jwtEncode(array $payload): string
    {
        $header = $this->base64UrlEncode(
            json_encode([
                'typ' => 'JWT',
                'alg' => 'HS256'
            ])
        );

        $payload = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->base64UrlEncode(
            hash_hmac(
                'sha256', 
                $header . '.' . $payload, 
                $this->secretKey,
                true
            )
        );

        return "{$header}.{$payload}.{$signature}";

    }

    public function jwtDecode(string $access_token): array
    {
        if (
            preg_match(
                '/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/', 
                $access_token, 
                $matches
            ) !== 1
        ) {
            throw new Exception('invalid token format');
        }

        $signature = $this->base64UrlEncode(
            hash_hmac(
                'sha256', 
                $matches['header'] . '.' . $matches['payload'], 
                $this->secretKey,
                true
            )
        );

        $signatureFromToken = $this->base64UrlDecode($matches['signature']);
        if (!hash_equals($signature, $signatureFromToken)) {
            throw new InvalidSignatureException();
        }

        $payload = json_decode(
            $this->base64UrlDecode($matches['payload']),
            true
        );

        return $payload;
    }

    private function base64UrlEncode(string $text): string
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    private function base64UrlDecode(string $text): string
    {
        return base64_decode(
            str_replace(
                ['-', '_'],
                ['+', '/'],
                $text
            )
        );
    }
}