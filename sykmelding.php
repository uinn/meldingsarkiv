<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/altinn.class.php';
require __DIR__ . '/config.php';

use Curl\Curl;
use Altinn\Altinn;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('ALTINN_API_URL', 'ALTINN_API_KEY', 'ALTINN_API_CLIENT_CERT', 'ALTINN_API_CLIENT_KEY', 'ALTINN_API_CLIENT_KEYPWD', 'ALTINN_API_CLIENT_USER', 'ALTINN_API_CLIENT_PASS')->notEmpty();

// $_ENV['ALTINN_API_URL'];
// $_ENV['ALTINN_API_KEY'];

// $_ENV["ALTINN_API_CLIENT_CERT"];
// $_ENV['ALTINN_API_CLIENT_KEY'];
// $_ENV['ALTINN_API_CLIENT_KEYPWD'];

// $_ENV['ALTINN_API_CLIENT_USER'];
// $_ENV['ALTINN_API_CLIENT_PASS'];

// #############

//$altinn = new Altinn();
//$altinn->authenticate();

//$result = $altinn->getMessages();

echo $_ENV['ALTINN_API_CLIENT_CERT'];
echo "\n";
echo ALTINN_API_CLIENT_KEY;
echo "\n";
