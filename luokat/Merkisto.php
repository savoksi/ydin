<?php

namespace Savoksi;

/**
 * Parametrisoitu merkistökoodaus.
 *
 * @property-read string $tunniste Kaksikirjaiminen kielikoodi
 */
class Merkisto
{
    // Merkistökoodauksen tunniste, esim "UTF-8"
    /** @var string $_tunniste */
    protected $_tunniste = 'UTF-8';

    /**
     * Alusta geneerinen merkistökoodaus.
     *
     * Esimerkki:
     *
     *     // Luo UTF-8 -merkistökoodaus
     *     $merkisto = new Merkisto('UTF-8');
     *
     *     // Palauttaa "Äyskäri HOI"
     *     return $merkisto->isoAlkukirjain('äyskäri HOI');
     *
     * @param string $tunniste
     */
    public function __construct($tunniste = 'UTF-8')
    {
        $this->_tunniste = $tunniste;
    }

    /**
     * Palauta sana isolla alkukirjaimella
     *
     * Esimerkki:
     *
     *     // Palauttaa "Äyskäri HOI"
     *     return $merkisto->isoAlkukirjain('äyskäri HOI');
     *
     * @param string $sana
     * @return string
     */
    public function isoAlkukirjain($sana)
    {
        return $this->isotKirjaimet(mb_substr($sana, 0, 1, $this->_tunniste))
            . mb_substr($sana, 1, null, $this->_tunniste);
    }

    /**
     * Palauta lause pienaakkosilla.
     *
     * Esimerkki:
     *
     *     // Palauttaa "äyskäri"
     *     return $merkisto->pienetKirjaimet('ÄYSKÄRI');
     *
     * @param string $sana
     * @return string
     */
    public function pienetKirjaimet($sana)
    {
        return mb_strtolower($sana, $this->_tunniste);
    }

    /**
     * Palauta lause suuraakkosilla.
     *
     * Esimerkki:
     *
     *     // Palauttaa "ÄYSKÄRI"
     *     return $merkisto->isotKirjaimet('äyskäri');
     *
     * @param string $sana
     * @return string
     */
    public function isotKirjaimet($sana)
    {
        return mb_strtoupper($sana, $this->_tunniste);
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
            return $this->_tunniste;
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
