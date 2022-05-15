<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Altinn.class.php';
require __DIR__ . '/Model.class.php';
require __DIR__ . '/BAM.class.php';
require __DIR__ . '/Database.class.php';
require __DIR__ . '/config.php';

use Curl\Curl;
use Altinn\Altinn;
use Dotenv\Dotenv;
use Model\Message;
use BAM\BAM;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('ALTINN_API_URL', 'ALTINN_API_KEY', 'ALTINN_API_CLIENT_CERT', 'ALTINN_API_CLIENT_KEY', 'ALTINN_API_CLIENT_KEYPWD', 'ALTINN_API_CLIENT_USER', 'ALTINN_API_CLIENT_PASS')->notEmpty();

//$orgno = "974799030";
$sykepenger ="4751";
$inntektsmeldinger = "4936";

$altinn = new Altinn();
$altinn->authenticate();


foreach (ALTINN_ORG_NOS as $orgno) {
// # get sykepenger
    $messages = $altinn->getMessageList($orgno, $sykepenger);

    foreach ($messages as $message) {
        $altinn->getAttachment($orgno, $message->MessageId);
    }

// # get inntektsmeldinger
    $messages2 = $altinn->getMessageList($orgno, $inntektsmeldinger);

    foreach ($messages2 as $message2) {
        $altinn->getForm($orgno, $message2->MessageId);
    }

}
echo "\n";
