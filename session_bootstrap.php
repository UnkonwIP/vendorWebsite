<?php
// Ensure a consistent session across all admin pages.
if (session_status() !== PHP_SESSION_ACTIVE) {
    $cookieParams = session_get_cookie_params();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (!headers_sent()) {
        session_name('vendor_website_session');
    }

    session_start();
}
