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
            echo 'error: ' . $curl->error . "\n";
            echo 'errorCode: ' . $curl->errorCode . "\n";
            echo 'errorMessage: ' . $curl->errorMessage . "\n";
            exit;
        } else {
            $_SESSION['authenticated'] = true;
            $_SESSION['altinn-cookie'] = $curl->getResponseCookie(".ASPXAUTH");
        }

        return true;

    }

    public function getMessageList($orgno, $svccode, $fromdate, $dateto, $skip)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $url = ALTINN_API_URL . '/api/' . $orgno . '/Messages?dateFrom=' . $fromdate . '&dateTo=' . $dateto . '&$skip=' . $skip . '&$filter=ServiceCode+eq+\'' . $svccode . '\'';
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
            $this->createDB();
            if ($this->checkDB($messageid)) {
		        $now = new \DateTime();
		        $now->setTimezone(new \DateTimeZone('Europe/Oslo'));
		        $time = $now->format("Y-m-d H:i:s");
                $files = $this->getAttachmentList($orgno, $messageid);
                foreach ($files as $file) {
                    if (preg_match("/^.*\.(pdf|jpg|png|gif)$/i", $file->FileName)) {
                        $pdf_url = $file->_links->self->href;
                    }
                    if (preg_match("/^.*\.(xml|XML)$/i", $file->FileName)) {
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
		            	$this->logpath = ALTINN_LOCAL_STORAGE . "/NAV " . $model->year . "/00 Altinn API/";
            			if (!file_exists($this->logpath)) {
			            	if (!mkdir($this->logpath, 0755, true) && !is_dir($this->logpath)) {
				            	throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->logpath));
                		    }
			            }
			            $logfile = $this->logpath . "/Importlogg.txt";
			            $logline = $time . " - Saved messageId " . $orgno . "/" . $messageid . " as " . $model->path . $model->filename . "\n";
			            file_put_contents($logfile,$logline, FILE_APPEND);
                        $this->addDBentry($messageid, $model->path, $model->filename);
                        // save messageid to database
                    }
                }
            } else {
                // echo "messageId " . $messageid . " already downloaded\n";
            }
        }
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

    private function createDB(): void
    {
        try {
            $query = "CREATE TABLE IF NOT EXISTS downloaded (messageId TEXT PRIMARY KEY, filepath TEXT, filename TEXT, date TEXT DEFAULT (datetime('now','localtime')))";
            $result = $this->db->query($query, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

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

    private function xml2json($response)
    {
        $xml_obj = @simplexml_load_string($response);
        if ($xml_obj !== false) {
            $response = json_decode(json_encode($xml_obj), FALSE);
        }
        return $response;
    }

    private function saveAttachmentFile($url, $path, $filename)
    {
        $code = "UnAuthenticated";
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
            $code = "NoWork";
            if (!file_exists($path)) {
                if (!mkdir($path, 0755, true) && !is_dir($path)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
                }
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

    public function getForm($orgno, $messageid)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
            $db = $this->dbConnect();
            $this->createDB();
            if ($this->checkDB($messageid)) {
		        $now = new \DateTime();
		        $now->setTimezone(new \DateTimeZone('Europe/Oslo'));
		        $time = $now->format("Y-m-d H:i:s");
                $xml_url = $this->getFormsUrl($orgno, $messageid);
                $pdf_url = str_replace("formdata", 'print', $xml_url);
                if (isset($pdf_url, $xml_url)) {
                    $xml = $this->getFormsXML($xml_url);
                    $metadata = json_decode(json_encode($this->xml2json(preg_replace('/ns6:/', '', $xml))), FALSE);
                    $messagetype = "Model\\" . $metadata->Skjemainnhold->ytelse;
                    $model = new $messagetype($messageid, $metadata);
                    $result = $this->saveAttachmentFile($pdf_url, $model->path, $model->filename);
		            if ($result === "Success") {
                        $this->logpath = ALTINN_LOCAL_STORAGE . "/NAV " . $model->year . "/00 Altinn API/";
                        if (!file_exists($this->logpath)) {
                                if (!mkdir($this->logpath, 0755, true) && !is_dir($this->logpath)) {
                                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->logpath));
                                }
                        }
                        $logfile = $this->logpath . "/Importlogg.txt";
                        $logline = $time . " - Saved messageId " . $orgno . "/" . $messageid . " as " . $model->path . $model->filename . "\n";
                        file_put_contents($logfile,$logline, FILE_APPEND);
                        $this->addDBentry($messageid, $model->path, $model->filename);
                        // save messageid to database
                    }
                }
            } else {
                // echo "messageId " . $messageid . " already downloaded\n";
            }
        }
    }

    private function getFormsUrl($orgno, $messageid)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $url = ALTINN_API_URL . '/api/' . $orgno . '/Messages/' . $messageid . '/forms';
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
                return $curl->getResponse()->_embedded->forms[0]->_links->formdata->href;
            }
        }
    }

    private function getFormsXML($url)
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

            $curl = new Curl();
            $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_BINARYTRANSFER, true);
            $curl->setHeader('ApiKey', ALTINN_API_KEY);
            $curl->setHeader('Content-Type', 'application/hal+json');
            $curl->setHeader('Accept', 'application/hal+json');
            $curl->setCookie(".ASPXAUTH", $_SESSION['altinn-cookie']);
            $file = tmpfile();
            $path = stream_get_meta_data($file)['uri'];
            $curl->download($url, $path);
            $xml_response = file_get_contents($path);
            if ($curl->error) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                exit;
            } else {
                return $xml_response;
            }
        }
    }
}
