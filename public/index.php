<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Http\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

$dotenv = new Dotenv();
$dotenv->load(dirname(__DIR__) . '/.env');

$trustedProxies = trim((string) ($_ENV['TRUSTED_PROXIES'] ?? ''));
if ($trustedProxies !== '') {
    Request::setTrustedProxies(
        array_map('trim', explode(',', $trustedProxies)),
        Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PROTO
    );
}

$request = Request::createFromGlobals();

$kernel = new Kernel();
$response = $kernel->handle($request);
$response->send();
