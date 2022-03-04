<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Altinn.class.php';
require __DIR__ . '/Model.class.php';
require __DIR__ . '/config.php';

use Curl\Curl;
use Altinn\Altinn;
use Dotenv\Dotenv;
use Model\Message;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('ALTINN_API_URL', 'ALTINN_API_KEY', 'ALTINN_API_CLIENT_CERT', 'ALTINN_API_CLIENT_KEY', 'ALTINN_API_CLIENT_KEYPWD', 'ALTINN_API_CLIENT_USER', 'ALTINN_API_CLIENT_PASS')->notEmpty();

$orgno = "911032023";
$svccode ="4503";

$altinn = new Altinn();
$altinn->authenticate();

$messages = $altinn->getMessageList($orgno,$svccode);

foreach($messages as $message) {
    $altinn->getAttachment($orgno,$message->MessageId);
    //echo $message->MessageId."\n";
}

//$result = $altinn->getMessages();
//print_r($messages);
//echo $result;
echo "\n";
