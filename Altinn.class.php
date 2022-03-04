<?php

namespace Altinn;

use Curl\Curl;

class Altinn
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function authenticate()
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
            return;
        }
        $url = ALTINN_API_URL . "/api/authentication/authenticatewithpassword?ForceEIAuthentication";
        $curl = new Curl();

        $curl->setHeader('ApiKey', ALTINN_API_KEY);
        $curl->setHeader('Content-Type', 'application/hal+json');
        $curl->setHeader('Accept', 'application/hal+json');

        $payload = [
            'UserName' => ALTINN_API_CLIENT_USER,
            'UserPassword' => ALTINN_API_CLIENT_PASS,
        ];

        $curl->setOpt(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        $curl->setOpt(CURLOPT_SSLCERT, ALTINN_API_CLIENT_CERT);
        $curl->setOpt(CURLOPT_SSLKEY, ALTINN_API_CLIENT_KEY);
        $curl->setOpt(CURLOPT_KEYPASSWD, ALTINN_API_CLIENT_KEYPWD);

        $curl->post($url, $payload);

        if ($curl->error) {
            echo $curl->response->error_type . ': ' . $curl->response->errorMessage . "\n";
            exit;
        } else {
            $_SESSION['authenticated'] = true;
            $_SESSION['altinn-cookie'] = $curl->getResponseCookie(".ASPXAUTH");
        }

        return true;

    }

    public function getMessageList($orgno,$svccode)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $url = ALTINN_API_URL . '/api/' . $orgno . '/Messages?$filter=ServiceCode+eq+\'' . $svccode . '\'';
            $curl = new Curl();

            $curl->setHeader('ApiKey', ALTINN_API_KEY);
            $curl->setHeader('Content-Type', 'application/hal+json');
            $curl->setHeader('Accept', 'application/hal+json');
            $curl->setCookie(".ASPXAUTH",$_SESSION['altinn-cookie']);
            $curl->get($url);

            if ($curl->error) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                exit;
            } else {
                return $curl->getResponse()->_embedded->messages;
            }
        }
    }

    public function getAttachments($orgno,$messageid)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $files = $this->getAttachmentList($orgno,$messageid);
            foreach($files as $file) {
                if (preg_match("/^([a-zA-Z0-9\s_\\.\-\(\):])+\.(pdf|jpg|png|gif)$/i", $file->FileName)) {
                    $pdf_url = $file->_links->self->href;
                }
                if (preg_match("/^([a-zA-Z0-9\s_\\.\-\(\):])+\.(xml|XML)$/i", $file->FileName)) {
                    $xml_url = $file->_links->self->href;
                }
            }
            $xml = $this->getAttachmentXML($xml_url);
            echo $xml_url." - XML\n";
            echo $pdf_url." - PDF\n";
            echo $xml;
        }
    }

    private function getAttachmentList($orgno,$messageid)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $url = ALTINN_API_URL . '/api/' . $orgno . '/Messages/' . $messageid . '/attachments';
            $curl = new Curl();

            $curl->setHeader('ApiKey', ALTINN_API_KEY);
            $curl->setHeader('Content-Type', 'application/hal+json');
            $curl->setHeader('Accept', 'application/hal+json');
            $curl->setCookie(".ASPXAUTH",$_SESSION['altinn-cookie']);
            $curl->get($url);

            if ($curl->error) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                exit;
            } else {
                return $curl->getResponse()->_embedded->attachments;
            }
        }
    }

    private function getAttachmentXML($url)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $curl = new Curl();

            $curl->setHeader('ApiKey', ALTINN_API_KEY);
            $curl->setHeader('Content-Type', 'application/hal+json');
            $curl->setHeader('Accept', 'application/hal+json');
            $curl->setCookie(".ASPXAUTH",$_SESSION['altinn-cookie']);
            $curl->get($url);

            if ($curl->error) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                exit;
            } else {
                return $curl->getResponse();
            }
        }
    }

}