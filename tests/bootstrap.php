<?php
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    // usePutenv(true) => %env()% peut lire les env réelles (celles injectées par <server ...>)
    (new Dotenv())->usePutenv(true)->bootEnv($envFile, 'test');
}
