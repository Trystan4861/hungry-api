<?php
/**
 * Firebase JWT (JSON Web Tokens) - Simplified Version
 */

class JWT
{
    public static function encode($payload, $key)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt, $key)
    {
        $jwtParts = explode('.', $jwt);
        if (count($jwtParts) !== 3) {
            throw new Exception('Invalid JWT format');
        }
        $signature = str_replace(['-', '_'], ['+', '/'], $jwtParts[2]);
        $decodedSignature = base64_decode($signature);
        $expectedSignature = hash_hmac('sha256', $jwtParts[0] . "." . $jwtParts[1], $key, true);
        if (!hash_equals($decodedSignature, $expectedSignature)) {
            throw new Exception('Invalid signature');
        }
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $jwtParts[1])), true);
        return $payload;
    }
}