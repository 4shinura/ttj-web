<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

class AuthService
{
    /** Vérifie qu'un token est présent dans les cookies */
    public static function isLoggedIn(Request $request): bool
    {
        return $request->cookies->has('access_token');
    }

    /** Construit le header Authorization: Bearer pour l'API */
    public static function bearerHeaders(Request $request): array
    {
        return [
            'Authorization' => 'Bearer ' . $request->cookies->get('access_token'),
        ];
    }

    /** Stocke le token JWT dans un cookie HttpOnly */
    public static function buildCookie(string $token): Cookie
    {
        return Cookie::create('access_token')
            ->withValue($token)
            ->withExpires(time() + 3600)
            ->withPath('/')
            ->withSecure(false)         // true en production (HTTPS)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_STRICT);
    }

    /** Décode le payload du JWT (sans vérifier la signature côté client) */
    public static function jwt_decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        $payload = json_decode(base64_decode($parts[1]), true);
        return is_array($payload) ? $payload : null;
    }

    public static function getUsername($request)
    {
        if (!self::isLoggedIn($request)) {
            return null;
        }

        $token = $request->cookies->get('access_token');
        $payload = self::jwt_decode($token);

        return $payload['user']['nom'] . ' ' . $payload['user']['prenom'] ?? null;
    }

}