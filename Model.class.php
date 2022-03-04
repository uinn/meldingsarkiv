<?php

namespace Model;

class Message
{
    public string $messageid;
    public object $metadata;
    public string $messagetype;
    public string $subtype;
    public string $surname;
    public string $givenname;
    public string $nin;
    public string $year;
    public string $startdate;
    public string $enddate;
    public string $path;
    public string $filename;

    public function __construct($messageid,$metadata)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->messageid = $messageid;
        //$this->metadata = $metadata;
    }
}

class sykmelding extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->sykmelding;
        //$this->messagetype = array_key_first((array)$metadata);
        $this->messagetype = "sykmelding";
        $this->subtype = "";
        $this->surname = ucwords(strtolower($obj->pasient->navn->etternavn));
        $this->givenname = ucwords(strtolower($obj->pasient->navn->fornavn));
        $this->nin = $obj->pasient->ident;
        $this->year = date('Y',strtotime($obj->perioder->fom));
        $this->startdate = date('ymd',strtotime($obj->perioder->fom));
        $this->enddate = date('ymd',strtotime($obj->perioder->tom));
        $this->filename = $this->surname . "_" . $this->givenname . "_" . $this->startdate . "-" . $this->enddate . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/sykepenger/" . $this->year . "/";
    }

}