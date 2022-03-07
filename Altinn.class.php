<?php

namespace Altinn;

use Curl\Curl;
use PDO;

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

    public function getMessageList($orgno, $svccode)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $url = ALTINN_API_URL . '/api/' . $orgno . '/Messages?$filter=ServiceCode+eq+\'' . $svccode . '\'';
            $curl = new Curl();

            $curl->setHeader('ApiKey', ALTINN_API_KEY);
            $curl->setHeader('Content-Type', 'application/hal+json');
            $curl->setHeader('Accept', 'application/hal+json');
            $curl->setCookie(".ASPXAUTH", $_SESSION['altinn-cookie']);
            $curl->get($url);

            if ($curl->error) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                exit;
            } else {
                return $curl->getResponse()->_embedded->messages;
            }
        }
    }

    public function getAttachment($orgno, $messageid)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
            $db = $this->dbConnect();
            //$this->createDB();
            if ($this->checkDB($messageid)) {
                $files = $this->getAttachmentList($orgno, $messageid);
                foreach ($files as $file) {
                    if (preg_match("/^([a-zA-Z0-9\s_\\.\-\(\):])+\.(pdf|jpg|png|gif)$/i", $file->FileName)) {
                        $pdf_url = $file->_links->self->href;
                    }
                    if (preg_match("/^([a-zA-Z0-9\s_\\.\-\(\):])+\.(xml|XML)$/i", $file->FileName)) {
                        $xml_url = $file->_links->self->href;
                    }
                }
                if (isset($pdf_url) && isset($xml_url)) {
                    $xml = $this->getAttachmentXML($xml_url);
                    $metadata = $this->xml2json($xml);
                    $messagetype = "Model\\" . array_key_first((array)$metadata);
                    $model = new $messagetype($messageid, $metadata);
                    $result = $this->saveAttachmentFile($pdf_url, $model->path, $model->filename);
                    if ($result === "Success") {
                        echo "Saved messageId " . $messageid . " as " . $model->filename . "\n";
                        $this->addDBentry($messageid, $model->path, $model->filename);
                        // save messageid to database
                    }
                }
                //print_r($model);
            } else {
                echo "messageId " . $messageid . " already downloaded\n";
            }
        }
    }

    private function getAttachmentList($orgno, $messageid)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $url = ALTINN_API_URL . '/api/' . $orgno . '/Messages/' . $messageid . '/attachments';
            $curl = new Curl();

            $curl->setHeader('ApiKey', ALTINN_API_KEY);
            $curl->setHeader('Content-Type', 'application/hal+json');
            $curl->setHeader('Accept', 'application/hal+json');
            $curl->setCookie(".ASPXAUTH", $_SESSION['altinn-cookie']);
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
            $curl->setCookie(".ASPXAUTH", $_SESSION['altinn-cookie']);
            $curl->get($url);

            if ($curl->error) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                exit;
            } else {
                return $curl->getResponse();
            }
        }
    }

    private function saveAttachmentFile($url, $path, $filename)
    {
        $code = "UnAuthenticated";
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
            $code = "NoWork";
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $filepath = $path . $filename;
            if (!file_exists($filepath)) {
                $curl = new Curl();
                $curl->setHeader('ApiKey', ALTINN_API_KEY);
                $curl->setHeader('Content-Type', 'application/hal+json');
                $curl->setHeader('Accept', 'application/hal+json');
                $curl->setCookie(".ASPXAUTH", $_SESSION['altinn-cookie']);
                $curl->download($url, $filepath);
                if ($curl->error) {
                    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                    $code = "Error";
                } else {
                    $code = "Success";
                }
            }
        }
        return $code;
    }

    private function xml2json($response)
    {
        $xml_obj = @simplexml_load_string($response);
        if ($xml_obj !== false) {
            $response = json_decode(json_encode($xml_obj), FALSE);
        }
        return $response;
    }

    private function dbConnect()
    {
        try {
            $this->db = new PDO(ALTINN_DB);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $this->db;
    }

    private function checkDB($messageId)
    {
        try {
            $rows = 0;
            $result = $this->db->query('SELECT COUNT(*) FROM downloaded WHERE "messageId" = "' . $messageId . '"', PDO::FETCH_ASSOC);
            $rows = $result->fetchColumn();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        if ($rows > 0) {
            return false;
        } else {
            return true;
        }
    }

    private function createDB()
    {
        try {
            $query = "CREATE TABLE IF NOT EXISTS downloaded (messageId TEXT PRIMARY KEY, filepath TEXT, filename TEXT, date TEXT DEFAULT (datetime('now','localtime')))";
            $result = $this->db->query($query, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }

    private function addDBentry($messageId, $path, $filename)
    {
        try {
            $insert = 'INSERT INTO downloaded (messageId,filepath,filename) VALUES (:id, :path, :name)';
            $stmt = $this->db->prepare($insert);
            $stmt->bindValue(':id', $messageId, PDO::PARAM_STR);
            $stmt->bindValue(':path', $path, PDO::PARAM_STR);
            $stmt->bindValue(':name', $filename, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }
}
