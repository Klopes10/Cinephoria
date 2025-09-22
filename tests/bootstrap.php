<?php
// tests/bootstrap.php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Forcer l'env "test" AVANT de charger .env (pour que Dotenv lise .env.test)
$_SERVER['APP_ENV']  = $_ENV['APP_ENV']  = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';

// Charger .env, puis .env.local, puis .env.test, puis .env.test.local (ordre Symfony)
if (class_exists(Dotenv::class)) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// (Optionnel) valeurs par défaut si non définies dans .env.test
$_SERVER['TRUSTED_HOSTS']   = $_SERVER['TRUSTED_HOSTS']   ?? '^.*$';
$_SERVER['TRUSTED_PROXIES'] = $_SERVER['TRUSTED_PROXIES'] ?? '127.0.0.1,REMOTE_ADDR';
