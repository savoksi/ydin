<?php

namespace Savoksi;

/**
 * Utf-8 -merkistökoodaus.
 *
 * @property-read string $tunniste Kaksikirjaiminen kielikoodi
 */
class Utf8
{
    // Merkistökoodauksen tunniste (esim "UTF-8") tai null, jos käytetään
    // php.ini:ssä asetettua merkistökoodausta.
    /** @var string $_tunniste */
    protected static $_tunniste = 'UTF-8';

    /**
     * Palauta sana isolla alkukirjaimella
     *
     * Esimerkki:
     *
     *     // Palauttaa "Äyskäri HOI"
     *     return Utf8::isoAlkukirjain('äyskäri HOI');
     *
     * @param string $sana
     * @return string
     */
    public static function isoAlkukirjain($sana)
    {
        $t = static::$_tunniste;
        return static::isotKirjaimet(mb_substr($sana, 0, 1, $t))
            . mb_substr($sana, 1, null, $t);
    }

    /**
     * Palauta lause pienaakkosilla.
     *
     * Esimerkki:
     *
     *     // Palauttaa "äyskäri"
     *     return Utf8::pienetKirjaimet('ÄYSKÄRI');
     *
     * @param string $sana
     * @return string
     */
    public static function pienetKirjaimet($sana)
    {
        return mb_strtolower($sana, static::$_tunniste);
    }

    /**
     * Palauta lause suuraakkosilla.
     *
     * Esimerkki:
     *
     *     // Palauttaa "ÄYSKÄRI"
     *     return Utf8::isotKirjaimet('äyskäri');
     *
     * @param string $sana
     * @return string
     */
    public static function isotKirjaimet($sana)
    {
        return mb_strtoupper($sana, static::$_tunniste);
    }

    /**
     * Hae julkisen attribuutin arvo.
     *
     * @param string $nimi
     * @return mixed
     */
    public function __get($nimi): mixed
    {
        switch ($nimi) {
        case 'tunniste':
            return static::$_tunniste;
        }
        virhe('Virheellinen kenttä', $nimi);
    }

    /**
     * Estä attribuuttien kirjoitus luokan ulkopuolelta.
     *
     * @param string $nimi
     * @param mixed $arvo
     * @return mixed
     */
    public function __set($nimi, $arvo)
    {
        virhe('Virheellinen kenttä', $nimi);
    }
}

// Tämä luokka vaatii PHP mbstring -laajennuksen.  Mbstring -laajennus
// ei ole automaattisesti asennettu kaikissa distroissa.
if (!extension_loaded('mbstring')) {
    virhe('Mbstring laajennus puuttuu');
}
