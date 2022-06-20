<?php

namespace Model;
use BAM\BAM;

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
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Sykmelding/";
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
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->sykmeldtesFnr));
        $this->nin = $obj->sykmeldtesFnr;
        $this->year = date('Y',strtotime($obj->periode->tom));
        $this->startdate = date('ymd',strtotime($obj->periode->fom));
        $this->enddate = date('ymd',strtotime($obj->periode->tom));
        $this->filename = $this->name . "_" . $this->startdate . "-" . $this->enddate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Sykepenger/";
    }

}

class Sykepenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Sykepenger";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        if(is_object($obj->arbeidsforhold) && is_string($obj->arbeidsforhold->foersteFravaersdag)) {
            $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
            $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        } elseif(is_object($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode) && is_string($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode->fom)) {
            $this->year = date('Y',strtotime($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode->tom));
            $this->startdate = date('ymd',strtotime($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode->fom));
        } else {
            $this->year = date('Y',strtotime($obj->avsendersystem->innsendingstidspunkt));
            $this->startdate = date('ymd',strtotime($obj->avsendersystem->innsendingstidspunkt));
        }
        #if(is_object($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode)) {
        #    $this->enddate = date('ymd',strtotime($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode->tom));
        #} else {
        #    $this->enddate = date('ymd',strtotime($obj->sykepengerIArbeidsgiverperioden->arbeidsgiverperiodeListe->arbeidsgiverperiode[0]->tom));
        #}
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Sykepenger/";
    }

}

class Foreldrepenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Foreldrepenger";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        $this->year = date('Y',strtotime($obj->startdatoForeldrepengeperiode));
        $this->startdate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->enddate = date('ymd',strtotime($obj->startdatoForeldrepengeperiode));
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Foreldrepenger/";
    }

}

class Omsorgspenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Omsorgspenger";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        if(is_object($obj->arbeidsforhold) && is_string($obj->arbeidsforhold->foersteFravaersdag)) {
            $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
            $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        } elseif(is_object($obj->omsorgspenger->delvisFravaersListe) && isset($obj->omsorgspenger->delvisFravaersListe->delvisFravaer->dato) && is_string($obj->omsorgspenger->delvisFravaersListe->delvisFravaer->dato)) {
            $this->year = date('Y',strtotime($obj->omsorgspenger->delvisFravaersListe->delvisFravaer->dato));
            $this->startdate = date('ymd',strtotime($obj->omsorgspenger->delvisFravaersListe->delvisFravaer->dato));
        } elseif(is_object($obj->omsorgspenger->delvisFravaersListe) && isset($obj->omsorgspenger->delvisFravaersListe->delvisFravaer[0]->dato) && is_string($obj->omsorgspenger->delvisFravaersListe->delvisFravaer[0]->dato)) {
            $this->year = date('Y',strtotime($obj->omsorgspenger->delvisFravaersListe->delvisFravaer[0]->dato));
            $this->startdate = date('ymd',strtotime($obj->omsorgspenger->delvisFravaersListe->delvisFravaer[0]->dato));
        } elseif(is_object($obj->omsorgspenger->fravaersPerioder) && isset($obj->omsorgspenger->fravaersPerioder->fravaerPeriode->fom) && is_string($obj->omsorgspenger->fravaersPerioder->fravaerPeriode->fom)) {
            $this->year = date('Y',strtotime($obj->omsorgspenger->fravaersPerioder->fravaerPeriode->fom));
            $this->startdate = date('ymd',strtotime($obj->omsorgspenger->fravaersPerioder->fravaerPeriode->fom));
        } else {
            $this->year = date('Y',strtotime($obj->avsendersystem->innsendingstidspunkt));
            $this->startdate = date('ymd',strtotime($obj->avsendersystem->innsendingstidspunkt));
        }
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Annet/";
    }

}

class Pleiepenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Pleiepenger";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        if(is_object($obj->arbeidsforhold) && is_string($obj->arbeidsforhold->foersteFravaersdag)) {
            $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
            $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        } elseif(is_object($obj->pleiepengerPerioder->periode) && isset($obj->pleiepengerPerioder->periode->fom) && is_string($obj->pleiepengerPerioder->periode->fom)) {
            $this->year = date('Y',strtotime($obj->pleiepengerPerioder->periode->fom));
            $this->startdate = date('ymd',strtotime($obj->pleiepengerPerioder->periode->fom));
        } else {
            $this->year = date('Y',strtotime($obj->avsendersystem->innsendingstidspunkt));
            $this->startdate = date('ymd',strtotime($obj->avsendersystem->innsendingstidspunkt));
        }
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Annet/";
    }

}

class PleiepengerBarn extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "PleiepengerBarn";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        if(is_object($obj->arbeidsforhold) && is_string($obj->arbeidsforhold->foersteFravaersdag)) {
            $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
            $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        } elseif(is_object($obj->pleiepengerPerioder) && isset($obj->pleiepengerPerioder->periode->fom) && is_string($obj->pleiepengerPerioder->periode->fom)) {
            $this->year = date('Y',strtotime($obj->pleiepengerPerioder->periode->fom));
            $this->startdate = date('ymd',strtotime($obj->pleiepengerPerioder->periode->fom));
        } else {
            $this->year = date('Y',strtotime($obj->avsendersystem->innsendingstidspunkt));
            $this->startdate = date('ymd',strtotime($obj->avsendersystem->innsendingstidspunkt));
        }
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Annet/";
    }

}

class PleiepengerNaerstaaende extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "PleiepengerNaerstaaende";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        if(is_object($obj->arbeidsforhold) && is_string($obj->arbeidsforhold->foersteFravaersdag)) {
            $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
            $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        } elseif(is_object($obj->pleiepengerPerioder) && isset($obj->pleiepengerPerioder->periode->fom) && is_string($obj->pleiepengerPerioder->periode->fom)) {
            $this->year = date('Y',strtotime($obj->pleiepengerPerioder->periode->fom));
            $this->startdate = date('ymd',strtotime($obj->pleiepengerPerioder->periode->fom));
        } else {
            $this->year = date('Y',strtotime($obj->avsendersystem->innsendingstidspunkt));
            $this->startdate = date('ymd',strtotime($obj->avsendersystem->innsendingstidspunkt));
        }
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Annet/";
    }

}

class Opplaeringspenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Opplaeringspenger";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        if(is_object($obj->arbeidsforhold) && is_string($obj->arbeidsforhold->foersteFravaersdag)) {
            $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
            $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        } else {
            $this->year = date('Y',strtotime($obj->avsendersystem->innsendingstidspunkt));
            $this->startdate = date('ymd',strtotime($obj->avsendersystem->innsendingstidspunkt));
        }
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Annet/";
    }

}

class Svangerskapspenger extends Message
{
    public function __construct($messageid,$metadata)
    {
        parent::__construct($messageid,$metadata);
        $obj = $metadata->Skjemainnhold;
        $this->messagetype = "Inntektsmelding";
        $this->subtype = "Svangerskapspenger";
        $this->name = preg_replace('/\s+/', '_', BAM::getNameFromBAM($obj->arbeidstakerFnr));
        $this->nin = $obj->arbeidstakerFnr;
        if(is_object($obj->arbeidsforhold) && is_string($obj->arbeidsforhold->foersteFravaersdag)) {
            $this->year = date('Y',strtotime($obj->arbeidsforhold->foersteFravaersdag));
            $this->startdate = date('ymd',strtotime($obj->arbeidsforhold->foersteFravaersdag));
        } else {
            $this->year = date('Y',strtotime($obj->avsendersystem->innsendingstidspunkt));
            $this->startdate = date('ymd',strtotime($obj->avsendersystem->innsendingstidspunkt));
        }
        $this->filename = $this->name . "_" . $this->messagetype . "_" . $this->subtype . "_" . $this->startdate . "_" . $messageid . ".pdf";
        $this->path = ALTINN_LOCAL_STORAGE . "/NAV " . $this->year . "/00 Annet/";
    }

}
