<?php

if (!function_exists('csrfToken')) {
    // Generate a new token or return existing token
    function csrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('isValidCsrfToken')) {
    // Check if the CSRF token is valid
    function isValidCsrfToken(string $token): bool
    {
        return ($token === csrfToken());
    }
}
