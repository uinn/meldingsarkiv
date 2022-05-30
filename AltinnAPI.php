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

$now = new DateTime();
$time = $now->format("Y-m-d H:i:s");
$dateto = $now->modify('+1 day')->format('Y-m-d\TH:i:s');
$datefrom = $now->modify('-31 day')->format('Y-m-d\TH:i:s');
#$datefrom = "2022-05-01T00:00:00";
#$dateto = "2022-05-29T00:00:00";
#echo $datefrom . " - " . $dateto . "\n";

$altinn = new Altinn();
$altinn->authenticate();


foreach (ALTINN_ORG_NOS as $orgno) {
// # get sykepenger
    $skip = 0;
    do {
        $chk = $skip;
        $messages = $altinn->getMessageList($orgno, $sykepenger, $datefrom, $dateto, $skip);
        foreach ($messages as $message) {
            $altinn->getAttachment($orgno, $message->MessageId);
            $skip++;
        }
        #echo $orgno . " : " . $skip . " - Sykepenger\n";
        if($skip === $chk) { $skip++; }
    } while($skip % 50 === 0);

// # get inntektsmeldinger
    $skip = 0;
    do {
        $chk = $skip;
        $messages2 = $altinn->getMessageList($orgno, $inntektsmeldinger, $datefrom, $dateto, $skip);
        foreach ($messages2 as $message2) {
            $altinn->getForm($orgno, $message2->MessageId);
            $skip++;
        }
        #echo $orgno . " : " . $skip . " - Inntektsmeldinger\n";
        if($skip === $chk) { $skip++; }
    } while($skip % 50 === 0);
}
echo "\n";
