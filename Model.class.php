<?php

namespace Model;

class Message
{
    public string $messageid;
    public object $metadata;
    public string $messagetype;
    public string $subtype;
    public string $name;
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
        $this->name = preg_replace('/\s+/', '_', ucwords(strtolower($obj->pasient->navn->etternavn . "_" . $obj->pasient->navn->fornavn)));
        $this->nin = $obj->pasient->ident;
        $this->year = date('Y',strtotime($obj->sykmeldingSkrevetDato));
        $this->startdate = date('ymd',strtotime($obj->perioder->fom));
        $this->enddate = date('ymd',strtotime($obj->perioder->tom));
        $this->filename = $this->name . "_" . $this->startdate . "-" . $this->enddate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/sykepenger/" . $this->year . "/";
    }

}

class sykepengesoeknad extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->sykepengesoeknad;
        $this->messagetype = "sykepengesoeknad";
        $this->subtype = "";
        //$this->name = preg_replace('/\s+/', '_', ucwords(strtolower(getNameFromBAM($obj->sykmeldtesFnr))));
        $this->nin = $obj->sykmeldtesFnr;
        $this->year = date('Y',strtotime($obj->periode->fom));
        $this->startdate = date('ymd',strtotime($obj->periode->fom));
        $this->enddate = date('ymd',strtotime($obj->periode->tom));
        $this->filename = $this->name . "_" . $this->startdate . "-" . $this->enddate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/sykepenger/" . $this->year . "/";
    }

}

class Sykepenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->melding->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Sykepenger";
        //$this->name = preg_replace('/\s+/', '_', ucwords(strtolower(getNameFromBAM($obj->arbeidstakerFnr))));
        $this->nin = $obj->arbeidstakerFnr;
        $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        $this->enddate = date('ymd',strtotime($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode->tom));
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/sykepenger/" . $this->year . "/";
    }

}

class Foreldrepenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->melding->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Foreldrepenger";
        //$this->name = preg_replace('/\s+/', '_', ucwords(strtolower(getNameFromBAM($obj->arbeidstakerFnr))));
        $this->nin = $obj->arbeidstakerFnr;
        $this->year = date('Y',strtotime($obj->startdatoForeldrepengeperiode));
        $this->startdate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->enddate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/foreldrepenger/" . $this->year . "/";
    }

}

class Omsorgspenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->melding->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Omsorgspenger";
        //$this->name = preg_replace('/\s+/', '_', ucwords(strtolower(getNameFromBAM($obj->arbeidstakerFnr))));
        $this->nin = $obj->arbeidstakerFnr;
        $this->year = date('Y',strtotime($obj->startdatoForeldrepengeperiode));
        $this->startdate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->enddate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/annet/" . $this->year . "/";
    }

}

class Pleiepenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->melding->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Pleiepenger";
        //$this->name = preg_replace('/\s+/', '_', ucwords(strtolower(getNameFromBAM($obj->arbeidstakerFnr))));
        $this->nin = $obj->arbeidstakerFnr;
        $this->year = date('Y',strtotime($obj->startdatoForeldrepengeperiode));
        $this->startdate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->enddate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/annet/" . $this->year . "/";
    }

}

class Opplaeringspenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->melding->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Opplaeringspenger";
        //$this->name = preg_replace('/\s+/', '_', ucwords(strtolower(getNameFromBAM($obj->arbeidstakerFnr))));
        $this->nin = $obj->arbeidstakerFnr;
        $this->year = date('Y',strtotime($obj->startdatoForeldrepengeperiode));
        $this->startdate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->enddate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/annet/" . $this->year . "/";
    }

}

class Svangerskapspenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->melding->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Svangerskapspenger";
        //$this->name = preg_replace('/\s+/', '_', ucwords(strtolower(getNameFromBAM($obj->arbeidstakerFnr))));
        $this->nin = $obj->arbeidstakerFnr;
        $this->year = date('Y',strtotime($obj->startdatoForeldrepengeperiode));
        $this->startdate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->enddate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/annet/" . $this->year . "/";
    }

}

