<?php

namespace Savoksi;

/**
 * Sovellusten kantaluokka.
 */
class Sovellus
{
    // Parhaillaan suoritettavat sovellukset
    /** @var array<Sovellus> $sovellukset */
    private static $sovellukset = [];

    // Alustetut komponentti-instanssit.  Nämä ovat yhteisiä kaikille
    // sovelluksille: samaa komponenttia ei alusteta useaan kertaan, vaikka
    // sovellus vaihtuu.
    /** @var array<string,object> $instanssit */
    private static $instanssit = [];

    // Sovelluksen perityt ja itse rekisteröimät komponentit
    /** @var array<string,string> $rekisteroidyt */
    private $rekisteroidyt = [];

    // Sovelluksen perityt ja omat asetukset.  Asetukset tallennetaan
    // taulukkoon pistenotaatiolla.
    /** @var array<string,string> $ymparisto */
    private $ymparisto = [];

    // Tälle sovellukselle automaattisesti rekisteröitävät komponentit.
    // Huomaa, että aliluokat voivat ylikirjoittaa tämän muuttujan
    // määrittelemällä uuden arvon aliluokan kuvauksessa.  Kantaluokka ei voi
    // siksi määritellä oletuskomponentteja tässä.
    /** @var array<string,string> $komponentit */
    protected $komponentit = [];

    // Tälle sovellukselle automaattisesti määriteltävät asetukset.  Huomaa,
    // että aliluokat voivat ylikirjoittaa tämän muuttujan määrittelemällä
    // uuden arvon aliluokan kuvauksessa.  Kantaluokka ei voi siksi määritellä
    // oletusasetuksia tässä.
    /** @var array<string,mixed> $asetukset */
    protected $asetukset = [];

    /**
     * Alusta sovellus.
     */
    public function __construct()
    {
        // Rekisteröi oletuskomponentit ja asetukset ensimmäistä sovellusta
        // alustettaessa.
        $n = count(self::$sovellukset);
        if ($n == 0) {
            // FIXME:
        }

        // Kun luodaan uutta sovellusta, niin rekisteröi parhaillaan
        // aktiivisen sovelluksen komponentit ja asetukset pohjalle.
        if ($n > 0) {
            $sovellus = self::$sovellukset[$n - 1];
            $this->rekisteroi($sovellus->rekisteroidyt);
            $this->aseta($sovellus->ymparisto);
        }

        // Rekisteröi aliluokan komponentit ja asetukset
        if (!empty($this->komponentit)) {
            $this->rekisteroi($this->komponentit);
        }
        if (!empty($this->asetukset)) {
            $this->aseta($this->asetukset);
        }
    }

    /**
     * Suorita sovellus.
     *
     * @param string ...$parametrit
     * @return Vastaus
     */
    public function __invoke(...$parametrit)
    {
        virhe('Ei toteutettu');
    }

    /**
     * Hae asetuksen arvo.
     *
     * Esimerkki:
     *
     *     // Palauttaa fi tms
     *     return asetus('oma.kieli');
     *
     * @param string $nimi Asetuksen nimi pistenotaatiolla, esim "oma.kieli"
     * @return string|null
     */
    public function asetus($nimi)
    {
        if (!isset($this->ymparisto[$nimi])) {
            varoitus('Arvoa ei asetettu', $nimi);
            return null;
        }
        return $this->ymparisto[$nimi];
    }

    /**
     * Aseta asetuksen arvo.
     *
     * Esimerkki:
     *
     *     // Aseta yksi asetus
     *     aseta('oma.kieli', 'fi');
     *
     *     // Aseta useita asetuksia
     *     aseta([
     *         'oma.kieli' => 'fi',
     *         'oma.koodi' => '5',
     *     ]);
     *
     * @param array<string,mixed>|string $nimi Asetuksen nimi tai taulukko
     * @param mixed $arvo Asetuksen arvo
     * @return self
     */
    public function aseta($nimi, $arvo = null)
    {
        // Muuta argumentit taulukoksi
        if (is_array($nimi)) {
            // Argumentti on jo taulukko, jätetään arvo huomiotta
            $asetukset = $nimi;
        } else {
            // Argumentti on merkkijono, huomioidaan arvo
            $asetukset = [
                $nimi => $arvo,
            ];
        }

        // Tallenna asetukset ympäristöön
        foreach ($asetukset as $avain => $arvo) {
            if (is_string($arvo)) {
                // Ylikirjoita skalaarinen arvo
                $this->ymparisto[$avain] = $arvo;
            } elseif (is_array($arvo)) {
                // Aseta taulukon arvot rekursiivisesti.  Tämä antaa
                // mahdollisuuden määritellä arvoja lyhyesti tarvitsematta
                // toistaa koko polkua.  Esimerkiksi koodi:
                //
                //     aseta([
                //         'oma' => [
                //             'a' => 1,
                //             'b' => 2,
                //         ],
                //     ]);
                //
                // tekee saman kuin:
                //
                //     aseta([
                //         'oma.a' => 1,
                //         'oma.b' => 2,
                //     ]);
                //
                foreach ($arvo as $x => $y) {
                    $this->aseta("$avain.$x", $y);
                }
            } elseif (!$arvo) {
                // Poista arvo merkitsemällä asetus tyhjäksi, nollaksi tai
                // epätodeksi.
                $this->ymparisto[$avain] = '';
            } else {
                // Muuta arvo, esim numero merkkijonoksi
                $this->ymparisto[$avain] = merkkijono($arvo);
            }
        }

        // Palauta viittaus olioon ketjutusta varten
        return $this;
    }

    /**
     * Luo uusi komponentti-olio.
     *
     * @template T of object
     * @param class-string<T>|string $nimi Komponentin lyhyt tai pitkä nimi
     * @param mixed ...$parametrit Valinnaiset parametrit
     * @return ($nimi is class-string<T> ? T : object)
     */
    public function luo($nimi, ...$parametrit)
    {
        static $laskuri = 0;

        // Varmista, ettei luo-funktio juutu kehäriippuvuuksien vuoksi
        // päättymättömään silmukkaan.
        if ($laskuri > 100) {
            virhe('Päättymätön silmukka');
        }

        // Luo uusi luokka laskurin suojassa
        $laskuri++;
        try {
            // Hae komponenttiluokan täydellinen nimi.  Jos komponentin nimi
            // on esimerkiksi "paate", niin hae luokan nimi "Savoksi\Paate".
            // Jos komponentille on rekisteröity korvike, niin hae korvaavan
            // luokan nimi.
            $luokka = $this->luokka($nimi);

            // Luo uusi olio luokan nimellä
            if (!class_exists($luokka)) {
                virhe('Tuntematon luokka', $luokka);
            }
            $olio = new $luokka(...$parametrit);
        } finally {
            $laskuri--;
        }

        // Palauta äsken luotu olio.  Huomaa, että luokka-funktion seurauksena
        // PHPStan ei kykene tarkistamaan palautuvan olion tyyppiä, vaan
        // varoittaa siitä.  Varoitus on aiheellinen, mutta luokkien
        // uudelleenmäärittelyä ei pysty tekemään muuten.
        return $olio;
    }

    /**
     * Hae komponentti-olion instanssi.
     *
     * @template T of object
     * @param class-string<T>|string $nimi Komponentin lyhyt tai pitkä nimi
     * @param string $aihe Luokan loppuosa, esim "Komento"
     * @return ($nimi is class-string<T> ? T : object)
     */
    public function hae($nimi, $aihe = '')
    {
        // Onko komponentti alustettu aiemmin pitkällä luokkanimellä
        $luokka = $this->luokka($nimi, $aihe);
        if (isset(self::$instanssit[$luokka])) {
            return self::$instanssit[$luokka];
        }

        // Onko komponentti alustettu aiemmin lyhyellä nimellä
        $lyhenne = $this->lyhenne($nimi, $aihe);
        if (isset(self::$instanssit[$lyhenne])) {
            return self::$instanssit[$lyhenne];
        }

        // Luo uusi komponentti
        if (!class_exists($luokka)) {
            virhe('Tuntematon luokka', $luokka);
        }
        $komponentti = new $luokka();

        // Rekisteröi komponentti-instanssi pitkällä luokkanimellä, jotta
        // sitä ei luoda toista kertaa.
        self::$instanssit[$luokka] = $komponentti;

        // Palauta luotu instanssi
        return $komponentti;
    }

    /**
     * Suorita sovellus tai hae nykyinen sovellus.
     *
     * @param Sovellus|string|callable|null $nimi
     * @param string ...$parametrit
     * @return ($nimi is null ? Sovellus : Vastaus)
     */
    #[Paaohjelma]
    public static function sovellus($nimi = null, ...$parametrit)
    {
        // Jos sovellusta ei ole alustettu, niin luo uusi oletussovellus
        // kuitenkaan käynnistämättä sitä.
        if (empty(self::$sovellukset)) {
            array_push(self::$sovellukset, new self());
        }

        // Palauta sovelluspinon päällimmäinen sovellus kysyttäessä
        if (!$nimi) {
            return end(self::$sovellukset);
        }

        // Luo sovellusolio
        if (is_string($nimi)) {
            // Selvitä sovellusluokan täydellinen nimi
            if (!class_exists($nimi)) {
                /** @var class-string<Sovellus> $luokka */
                $luokka = self::savoksi($nimi, 'Sovellus');
            } else {
                // Argumentti on jo kelvollinen luokkanimi
                /** @var class-string<Sovellus> $luokka */
                $luokka = $nimi;
            }

            // Luo sovellus luokan nimellä.  Tämä antaa nykyisen
            // sovelluksen tarvittaessa ylikirjoittaa muita sovelluksia.
            $sovellus = luo($luokka);
        } elseif ($nimi instanceof Sovellus) {
            // Parametri on jo sovellusluokan olio
            $sovellus = $nimi;
        } elseif (is_callable($nimi)) {
            // Luo sovellusluokan olio anonyymistä funktiosta.  Tämä
            // varmistaa, että sovellus() palauttaa Sovellus-luokan olion
            // myös anonyymistä funktiosta kysyttäessä.
            $sovellus = new class ($nimi) extends Sovellus {
                /** @var callable $funktio */
                protected $funktio;

                /**
                 * @param callable $funktio
                 */
                public function __construct($funktio)
                {
                    $this->funktio = $funktio;
                }

                /**
                 * @param string ...$parametrit
                 * @return Vastaus
                 */
                public function __invoke(...$parametrit)
                {
                    return vastaus(($this->funktio)(...$parametrit));
                }
            };
        } else {
            virhe('Virheellinen parametri', $nimi);
        }

        // Aktivoi uusi sovellus, suorita se ja palauta vanha sovellus
        // käyttöön sovelluksen lopetettua.
        array_push(self::$sovellukset, $sovellus);
        try {
            $vastaus = $sovellus(...$parametrit);
        } finally {
            array_pop(self::$sovellukset);
        }
        return $vastaus;
    }

    /**
     * Hae komponentin luokkanimi.
     *
     * @param string $nimi Komponentin lyhyt tai pitkä nimi, esim "kieli"
     * @param string $aihe Luokan loppuosa, esim "Komento"
     * @return string Komponentin pitkä nimi, esim "Savoksi\Kieli"
     */
    public function luokka($nimi, $aihe = '')
    {
        // Onko komponentti rekisteröity pitkällä luokkanimellä kuten
        // "Savoksi\Kieli"?
        $pitka = self::savoksi($nimi, $aihe);
        if (isset($this->rekisteroidyt[$pitka])) {
            return $this->rekisteroidyt[$pitka];
        }

        // Onko komponentti rekisteröity lyhyellä luokkanimellä kuten "kieli"?
        $lyhenne = $this->lyhenne($nimi, $aihe);
        if (isset($this->rekisteroidyt[$lyhenne])) {
            // Kyllä, palauta rekisteröity alustin
            return $this->rekisteroidyt[$lyhenne];
        }

        // Onko nimi itsessään luokkanimi kuten "Kieli"?  Tällä ehdolla on
        // merkitystä erityisesti käytettäessä luokkia nimiavaruuksien
        // ulkopuolella.
        if (class_exists($nimi)) {
            return $nimi;
        }

        // Luokkaa ei ole rekisteröity, joten palauta luokan täydellinen nimi.
        // Jos luokan nimessä ei ole nimiavaruutta, niin Savoksi-nimiavaruus
        // oletetaan.
        return $pitka;
    }

    /**
     * Rekisteröi yksi tai useampi komponentti.
     *
     * Jos komponentti on rekisteröity sekä lyhyellä nimellä (esim paate)
     * ja pitkällä nimellä (esim Savoksi\Paate), niin pitkällä nimellä
     * rekisteröityä versiota käytetään ensisijaisesti.
     *
     * Esimerkki:
     *
     *     // Rekisteröi sovelluskohtainen versio Paateluokalle
     *     $this->rekisteroi([
     *         Paate::class => OmaPaate::class,
     *     ]);
     *
     *     // Palauttaa OmaPaate -luokan olion
     *     $paate = luo(Paate::class);
     *
     * @param string|array<string,string|object> $nimi Luokan nimi tai taulu
     * @param string|object|null $komponentti Korvaava luokka tai olio
     * @return void
     */
    protected function rekisteroi($nimi, $komponentti = null)
    {
        // Muuta argumentit taulukoksi
        if (is_array($nimi)) {
            $taulukko = $nimi;
        } else {
            $taulukko = [
                $nimi => $komponentti,
            ];
        }

        // Rekisteröi useita komponentteja
        foreach ($taulukko as $nimi => $komponentti) {
            // Poista rekisteröinti, jos komponentti on null
            if (is_null($komponentti)) {
                unset($this->rekisteroidyt[$nimi]);
                continue;
            }

            // Hae luokan nimi ja rekisteröi mahdollinen komponentti-olio
            if (is_object($komponentti)) {
                $luokka = get_class($komponentti);
                self::$instanssit[$luokka] = $komponentti;
            } else {
                $luokka = $komponentti;
            }

            // Rekisteröi komponentti pitkällä tai lyhyellä luokkanimellä
            $this->rekisteroidyt[$nimi] = $luokka;
        }
    }

    /**
     * Muodosta luokan pitkä nimi.
     *
     * @param string $nimi Luokan pitkä tai lyhyt nimi, esim "asenna"
     * @param string $aihe Luokan loppuosa, esim "Komento"
     * @return string Pitkä nimi, esim "Savoksi\AsennaKomento"
     */
    public static function savoksi($nimi, $aihe = '')
    {
        static $cache = [];

        // Palauta välimuistiin tallennettu nimi, jos on
        $id = "$nimi##$aihe";
        if (isset($cache[$id])) {
            return $cache[$id];
        }

        // Muuta jokainen sana alkamaan isolla alkukirjaimella
        $i = 0;
        $n = strlen($nimi);
        $tulos = '';
        do {
            // Poimi osoitinta $i seuraava sana
            if (preg_match('/^[a-z0-9åäöÅÄÖ]+/iu', substr($nimi, $i), $x)) {
                // Muuta sanan ensimmäinen kirjain isoksi ja kopioi sana
                // tulokseen koskematta sanan loppuosaan.
                $tulos .= ucfirst($x[0]);
                $i += strlen($x[0]);
            }

            // Tulkitse erikoismerkki sanan perässä
            $c = substr($nimi, $i, 1);
            switch ($c) {
            case ':':
            case '\\':
                // Edellinen sana viittaa nimiavaruuteen.  Jos nimi on
                // esimerkiksi "savoksi:asenna", niin vastaava luokan nimi on
                // "Savoksi\\Asenna".
                $tulos .= '\\';
                $i++;
                break;

            case '-':
            case '_':
                // Seuraava nimi liittyy tähän sanaan => ohita erikoismerkki.
                // Jos nimi on esimerkiksi "luo-tili", niin vastaava luokan
                // nimi on "LuoTili".
                $i++;
                break;

            case '':
                // Nimen loppu
                break;

            default:
                // Tuntematon erikoismerkki luokkanimessä
                virhe('Virheellinen luokkanimi', $nimi);
            }
        } while ($i < $n);

        // Lisää Savoksi-nimiavaruus, jos nimi ei sisällä mitään nimiavaruutta
        if (strpos($tulos, '\\') === false) {
            $tulos = "Savoksi\\$tulos";
        }

        // Lisää aihe luokan nimeen, jos ei jo ole
        if ($aihe) {
            $n = strlen($tulos);
            $m = strlen($aihe);
            if ($n > $m && substr($tulos, $n - $m) != $aihe) {
                $tulos .= $aihe;
            }
        }

        // Tallenna tulos välimuistiin, jotta nimeä ei tarvitse muodostaa
        // uudelleen.
        $cache[$id] = $tulos;

        // Palauta luokan pitkä nimi
        return $tulos;
    }

    /**
     * Palauta luokan lyhyt nimi.
     *
     * @param string $nimi Luokan pitkä tai lyhyt nimi, esim "Savoksi\Paate"
     * @param string $aihe Luokan loppuosa, esim "Komento"
     * @return string Lyhyt nimi, esim "paate"
     */
    public static function lyhenne($nimi, $aihe = '')
    {
        static $cache = [];

        // Palauta aiemmin välimuistiin tallennettu nimi, jos on
        $id = "$nimi##$aihe";
        if (isset($cache[$id])) {
            return $cache[$id];
        }

        // Ohita Savoksi-luokka nimen alussa
        $savoksi = 'Savoksi\\';
        if (substr($nimi, 0, strlen($savoksi)) == $savoksi) {
            $i = strlen($savoksi);
        } else {
            $i = 0;
        }

        // Poista aihe luokkanimestä.  Jos luokan pitkä nimi on esimerkiksi
        // "OmaKomento" ja aihe on "Komento", niin muodosta nimi "Oma".
        $n = strlen($nimi);
        if ($aihe) {
            $m = strlen($aihe);
            if ($n > $m && substr($nimi, $n - $m) == $aihe) {
                // Älä kuitenkaan poista kantaluokkaa.  Jos luokan pitkä nimi
                // on esimerkiksi "Oma\Komento" ja aihe on "Komento", niin
                // jätä nimi ennalleen.
                if (substr($nimi, $n - $m - 1, 1) !== '\\') {
                    $n -= $m;
                }
            }
        }

        // Käsittele jokainen isolla alkukirjaimella alkava sana
        $tulos = '';
        do {
            // Poimi sana seuraavaan isoon kirjaimeen tai erikoismerkkiin asti
            $loppu = substr($nimi, $i);
            if (preg_match('/^[a-zA-ZåäöÅÄÖ][a-zåäö0-9]*/u', $loppu, $x)) {
                // Muunna sana pienille kirjaimille ja kopioi sana tulokseen
                $tulos .= strtolower($x[0]);
                $i += strlen($x[0]);
                if ($i >= $n) {
                    // Sanan loppu
                    break;
                }
            }

            // Päättele seuraavan kirjaimen mukaan, miten edetä
            $c = substr($nimi, $i, 1);
            switch ($c) {
            case '\\':
            case ':':
                // Nimiavaruus vaihtuu => lisää kaksoispiste ja jatka
                // käsittelyä erikoismerkin perästä.  Jos luokan pitkä nimi on
                // esimerkiksi "Sovellus\Asenna", niin vastaava lyhyt nimi on
                // "sovellus:asenna".
                $tulos .= ':';
                $i++;
                break;

            case '-':
            case '_':
                // Seuraava sana.  Jos luokan lyhyt nimi on esimerkiksi
                // "luo_tilit", niin normalisoi nimi muotoon "luo-tilit".
                $tulos .= '-';
                $i++;
                break;

            default:
                // Muu kirjain
                if (preg_match('/^[A-ZÅÄÖ]/u', substr($nimi, $i))) {
                    // Seuraava sana alkaa isolla kirjaimella => lisää
                    // väliviiva ja jatka käsittelyä kohdasta $i.  Jos luokan
                    // pitkä nimi on esimerkiksi "LuoTilitBertta", niin
                    // vastaava lyhyt nimi on "luo-tilit-bertta".
                    $tulos .= '-';
                } else {
                    // Tuntematon erikoismerkki
                    virhe('Virheellinen luokkanimi', $nimi);
                }
            }
        } while ($i < $n);

        // Tallenna tulos välimuistiin, jotta laskentaa ei tarvitse toistaa
        $cache[$id] = $tulos;

        // Palauta luokan lyhyt nimi
        return $tulos;
    }
}
