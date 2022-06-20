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

// Meldingskoder
$sykepenger ="4751";
$inntektsmeldinger = "4936";

$now = new DateTime();
$now->setTimezone(new \DateTimeZone('Europe/Oslo'));
$time = $now->format("Y-m-d H:i:s");

// Sjekker meldinger fra siste 31 dager
$dateto = $now->modify('+1 day')->format('Y-m-d\TH:i:s');
$datefrom = $now->modify('-31 day')->format('Y-m-d\TH:i:s');

// For manuell førstegangsimport av tidligere år
#$datefrom = "2021-01-01T00:00:00";
#$dateto = "2021-12-31T00:00:00";

$altinn = new Altinn();
$altinn->authenticate();


foreach (ALTINN_ORG_NOS as $orgno) {
// # hent søknader om sykepenger
    $skip = 0;
    do {
        $chk = $skip;
        $messages = $altinn->getMessageList($orgno, $sykepenger, $datefrom, $dateto, $skip);
        foreach ($messages as $message) {
            $altinn->getAttachment($orgno, $message->MessageId);
            $skip++;
        }
        if($skip === $chk) { $skip++; }  // I tilfelle det er akkurat 50 meldinger
    } while($skip % 50 === 0);  // Paging

// # hent inntektsmeldinger
    $skip = 0;
    do {
        $chk = $skip;
        $messages2 = $altinn->getMessageList($orgno, $inntektsmeldinger, $datefrom, $dateto, $skip);
        foreach ($messages2 as $message2) {
            $altinn->getForm($orgno, $message2->MessageId);
            $skip++;
        }
        if($skip === $chk) { $skip++; } // I tilfelle det er akkurat 50 meldinger
    } while($skip % 50 === 0);  // Paging
}
