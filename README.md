# Meldingsarkiv
## Nedlasting av meldinger fra Altinn for lovpålagt arivering

[![curl](curl.png)]

Dette scriptet er et objekt-orientert PHP-script som logger inn i Altinn med virksomhetssertifikat, finner inntektsmeldinger og søknader om sykepenger, samt disses metadata, laster de ned og lagrer de på egnet sted med filnavn og sti på ønsket format.

## Authentisering

Pålogging til Altinn kan foregå på flere forskjellige måter, men vi har i dette tilfellet valgt å logge på som en virksomhetsbruker med virksomhetssertifikat. Alternativet er å benytte [maskinporten](https://altinn.github.io/docs/api/rest/kom-i-gang/virksomhet/), men det noe mer komplisert, så dette ble ikke valgt i denne omgang.

- Det benyttes Altinn API versjon 2 (sk. Altinn2)
- Vi har fått tildelt API-nøkler av Altinn
- Vårt virksomhetssertifikat har blitt benyttet til å [opprette virksomhetsbrukeren](https://www.altinn.no/hjelp/innlogging/alternativ-innlogging-i-altinn/virksomhetssertifikat/) `inn_integrasjon_meldingsarkiv`
- Denne brukeren har blitt tildelt nødvendige rettigheter i Altinn

## PHP og Curl

- I dette scriptet brukes OOP PHP, og spesifikt biblioteket [php-curl-class](https://github.com/php-curl-class/php-curl-class)
- Det er laget egne OOP get/set-klasser for alle oerasjoner mot Altinn, samt modell-klasser for behandling av metadata for de forskjellige meldingstypene

## Meldingstyper
Oppdraget er å laste ned 2 hovedtyper av meldinger
- Søknad om sykepenger
- Inntektsmeldinger (med følgende undertyper)
-- Sykepenger
-- Foreldrepenger
-- Omsorgspenger
-- Pleiepenger
-- Svangerskapspenger

Disse meldingenes metadata innholder ikke navn, så det må gjøres separate oppslag i BAM sin database for å hente ut det.
### Søknad om sykepenger
Denne meldingstypen filtreres på `ServiceCode: 4751` og har følgende metadata:
```XML
<sykepengesoeknad>
    <sykepengesoeknadId>b122a9fd-384d-3c8f-905f-ba04566ad273</sykepengesoeknadId>
    <sykmeldingId>117a48ab-52b1-4d1b-afa3-0ba2239d165d</sykmeldingId>
    <periode>
        <fom>2022-02-19</fom>
        <tom>2022-03-07</tom>
    </periode>
    <sykmeldtesFnr>21026421680</sykmeldtesFnr>
    <arbeidsgiverForskuttererLoenn>IKKE_SPURT</arbeidsgiverForskuttererLoenn>
    <identdato>2022-02-01</identdato>
    <sendtTilArbeidsgiverDato>2022-03-08</sendtTilArbeidsgiverDato>
    <sendtTilNAVDato>2022-03-08</sendtTilNAVDato>
    <sykmeldingSkrevetDato>2022-02-01</sykmeldingSkrevetDato>
    <sykmeldingsperiodeListe>
        <graderingsperiode>
            <fom>2022-02-19</fom>
            <tom>2022-03-07</tom>
        </graderingsperiode>
        <sykmeldingsgrad>40</sykmeldingsgrad>
        <korrigertArbeidstid>
            <arbeidsgrad>61</arbeidsgrad>
            <arbeidstimerNormaluke>40.0</arbeidstimerNormaluke>
        </korrigertArbeidstid>
    </sykmeldingsperiodeListe>
    <harBekreftetOpplysningsplikt>true</harBekreftetOpplysningsplikt>
    <harBekreftetKorrektInformasjon>true</harBekreftetKorrektInformasjon>
</sykepengesoeknad>
<juridiskOrganisasjonsnummer>911031981</juridiskOrganisasjonsnummer>
<virksomhetsnummer>911032058</virksomhetsnummer>
```

### Inntektsmelding
Denne meldingstypen filtreres på `ServiceCode: 4936` og har litt forskjellig format på metadata for hver undertype:
```XML
<ns6:melding xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:seres="http://seres.no/xsd/forvaltningsdata" xmlns:ns1="http://seres.no/xsd/NAV/Inntektsmelding_M/2017" xmlns:ns2="http://seres.no/xsd/NAV/Inntektsmelding_M/20171205" xmlns:ns3="http://seres.no/xsd/NAV/Inntektsmelding_M/20180618" xmlns:ns5="http://schemas.microsoft.com/2003/10/Serialization/" xmlns:ns6="http://seres.no/xsd/NAV/Inntektsmelding_M/20180924">
    <ns6:Skjemainnhold>
        <ns6:ytelse>Sykepenger</ns6:ytelse>
        <ns6:aarsakTilInnsending>Ny</ns6:aarsakTilInnsending>
        <ns6:arbeidsgiver>
            <ns6:virksomhetsnummer>974799333</ns6:virksomhetsnummer>
            <ns6:kontaktinformasjon>
                <ns6:kontaktinformasjonNavn>Stine Saksbehandler</ns6:kontaktinformasjonNavn>
                <ns6:telefonnummer>62430000</ns6:telefonnummer>
            </ns6:kontaktinformasjon>
        </ns6:arbeidsgiver>
        <ns6:arbeidstakerFnr>01047712345</ns6:arbeidstakerFnr>
        <ns6:naerRelasjon>false</ns6:naerRelasjon>
        <ns6:arbeidsforhold>
            <ns6:arbeidsforholdId xsi:nil="true" />
            <ns6:foersteFravaersdag>2021-06-08</ns6:foersteFravaersdag>
            <ns6:beregnetInntekt>
                <ns6:beloep>123456.0</ns6:beloep>
                <ns6:aarsakVedEndring xsi:nil="true" />
            </ns6:beregnetInntekt>
            <ns6:avtaltFerieListe />
            <ns6:utsettelseAvForeldrepengerListe />
            <ns6:graderingIForeldrepengerListe />
        </ns6:arbeidsforhold>
        <ns6:refusjon>
            <ns6:refusjonsbeloepPrMnd>123546.0</ns6:refusjonsbeloepPrMnd>
            <ns6:refusjonsopphoersdato xsi:nil="true" />
            <ns6:endringIRefusjonListe />
        </ns6:refusjon>
        <ns6:sykepengerIArbeidsgiverperioden>
            <ns6:arbeidsgiverperiodeListe>
                <ns6:arbeidsgiverperiode>
                    <ns6:fom>2021-06-08</ns6:fom>
                    <ns6:tom>2021-06-23</ns6:tom>
                </ns6:arbeidsgiverperiode>
            </ns6:arbeidsgiverperiodeListe>
            <ns6:bruttoUtbetalt>123456.0</ns6:bruttoUtbetalt>
            <ns6:begrunnelseForReduksjonEllerIkkeUtbetalt xsi:nil="true" />
        </ns6:sykepengerIArbeidsgiverperioden>
        <ns6:startdatoForeldrepengeperiode xsi:nil="true" />
        <ns6:opphoerAvNaturalytelseListe />
        <ns6:gjenopptakelseNaturalytelseListe />
        <ns6:avsendersystem>
            <ns6:systemnavn>SAP [SID:QOA/603]</ns6:systemnavn>
            <ns6:systemversjon>740</ns6:systemversjon>
            <ns6:innsendingstidspunkt>2021-07-15T10:52:13+00:00</ns6:innsendingstidspunkt>
        </ns6:avsendersystem>
        <ns6:pleiepengerPerioder />
        <ns6:omsorgspenger>
            <ns6:harUtbetaltPliktigeDager xsi:nil="true" />
            <ns6:fravaersPerioder />
            <ns6:delvisFravaersListe />
        </ns6:omsorgspenger>
    </ns6:Skjemainnhold>
</ns6:melding>
```
```XML
<ns6:melding xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:seres="http://seres.no/xsd/forvaltningsdata" xmlns:ns1="http://seres.no/xsd/NAV/Inntektsmelding_M/2017" xmlns:ns2="http://seres.no/xsd/NAV/Inntektsmelding_M/20171205" xmlns:ns3="http://seres.no/xsd/NAV/Inntektsmelding_M/20180618" xmlns:ns5="http://schemas.microsoft.com/2003/10/Serialization/" xmlns:ns6="http://seres.no/xsd/NAV/Inntektsmelding_M/20180924">
    <ns6:Skjemainnhold>
        <ns6:ytelse>Foreldrepenger</ns6:ytelse>
        <ns6:aarsakTilInnsending>Ny</ns6:aarsakTilInnsending>
        <ns6:arbeidsgiver>
            <ns6:virksomhetsnummer>974799333</ns6:virksomhetsnummer>
            <ns6:kontaktinformasjon>
                <ns6:kontaktinformasjonNavn>Stine Saksbehandler</ns6:kontaktinformasjonNavn>
                <ns6:telefonnummer>61288000</ns6:telefonnummer>
            </ns6:kontaktinformasjon>
        </ns6:arbeidsgiver>
        <ns6:arbeidstakerFnr>12345678910</ns6:arbeidstakerFnr>
        <ns6:naerRelasjon>false</ns6:naerRelasjon>
        <ns6:arbeidsforhold>
            <ns6:arbeidsforholdId xsi:nil="true" />
            <ns6:foersteFravaersdag xsi:nil="true" />
            <ns6:beregnetInntekt>
                <ns6:beloep>123546.0</ns6:beloep>
                <ns6:aarsakVedEndring xsi:nil="true" />
            </ns6:beregnetInntekt>
            <ns6:avtaltFerieListe />
            <ns6:utsettelseAvForeldrepengerListe />
            <ns6:graderingIForeldrepengerListe />
        </ns6:arbeidsforhold>
        <ns6:refusjon>
            <ns6:refusjonsbeloepPrMnd>123456.0</ns6:refusjonsbeloepPrMnd>
            <ns6:refusjonsopphoersdato xsi:nil="true" />
            <ns6:endringIRefusjonListe />
        </ns6:refusjon>
        <ns6:sykepengerIArbeidsgiverperioden>
            <ns6:arbeidsgiverperiodeListe>
                <ns6:arbeidsgiverperiode>
                    <ns6:fom xsi:nil="true" />
                    <ns6:tom xsi:nil="true" />
                </ns6:arbeidsgiverperiode>
            </ns6:arbeidsgiverperiodeListe>
            <ns6:bruttoUtbetalt xsi:nil="true" />
            <ns6:begrunnelseForReduksjonEllerIkkeUtbetalt xsi:nil="true" />
        </ns6:sykepengerIArbeidsgiverperioden>
        <ns6:startdatoForeldrepengeperiode>2022-02-07</ns6:startdatoForeldrepengeperiode>
        <ns6:opphoerAvNaturalytelseListe />
        <ns6:gjenopptakelseNaturalytelseListe />
        <ns6:avsendersystem>
            <ns6:systemnavn>SAP [SID:QOA/603][BUILD:20210820]</ns6:systemnavn>
            <ns6:systemversjon>740</ns6:systemversjon>
            <ns6:innsendingstidspunkt>2022-01-28T09:38:47+00:00</ns6:innsendingstidspunkt>
        </ns6:avsendersystem>
        <ns6:pleiepengerPerioder />
        <ns6:omsorgspenger>
            <ns6:harUtbetaltPliktigeDager xsi:nil="true" />
            <ns6:fravaersPerioder />
            <ns6:delvisFravaersListe />
        </ns6:omsorgspenger>
    </ns6:Skjemainnhold>
</ns6:melding>
```

## Lagring
### Struktur
De nedlastede meldingene lagres på X/specialSMB i følgende struktur:
- Sykepenger
  - 2021
  - 2022
- Foreldrepenger
  - 2021
  - 2022
- Annet
  - 2021
  - 2022
### Filnavn
Filnavn lagres med "underscore" istedenfor mellomrom, og med Altinn sin meldingsId for å sikre unike filnavn.

Søknad om sykepenger lagres som:
`<Etternavn>_<Fornavn>_Sykepenger_<startdato>-<sluttdato>_<meldingsId>.pdf` eks.:
Olsen_Ole_Sykepenger_220301-220331_a123456.pdf

Innteksmeldinger lagres som:
`<Etternavn>_<Fornavn>_Inntektsmelding_<undertype>_<startdato>_<meldingsId>.pdf` eks.:
Olsen_Ole_Inntektsmelding_Foreldrepenger_220301_b123456.pdf




















