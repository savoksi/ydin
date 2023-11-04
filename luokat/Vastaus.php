<?php

namespace Savoksi;

use JsonSerializable;

/**
 * Tallenna sovelluksen vastaus.
 *
 * @property-read mixed $data Raaka vastausdata binääri- tai tekstimuodossa
 * @property-read string $tyyppi Vastauksen tyyppi, esim "text/plain"
 * @property-read int $koodi HTTP-tilakoodi, esim 200
 * @property-read array<string,string> $otsakkeet
 */
class Vastaus implements JsonSerializable
{
    // Vastausdata raakamuodossa
    /** @var mixed $data */
    protected $data = '';

    // Arvon todellinen tyyppi, esim "text/plain" tai "application/json"
    /** @var string $tyyppi */
    protected $tyyppi = '';

    // HTTP-tilakoodi, esim 200
    /** @var int $koodi */
    protected $koodi = 200;

    // Lisäotsakkeet, esim Location
    /** @var array<string,string> $otsakkeet */
    protected $otsakkeet = [];

    /**
     * Luo vastausolio.
     *
     * Esimerkki:
     *
     *     // Luo geneerinen 404 virhe
     *     return vastaus('Not Found', 'text/plain', 404);
     *
     * @param mixed $data Raaka data
     * @param string $tyyppi Arvon tyyppi, esim "text/plain"
     * @param int $koodi HTTP-tilakoodi, esim 200
     * @param array<string,string> $otsakkeet
     */
    public function __construct(
        $data = '',
        $tyyppi = '',
        $koodi = 0,
        $otsakkeet = [],
    )
    {
        // Aseta vastauksen data
        $this->data($data);

        // Aseta vastauksen tyyppi
        if ($tyyppi) {
            // Käytä annettua tyyppiä
            $this->tyyppi($tyyppi);
        } else {
            // Tunnista datan tyyppi
            $this->tyyppi($this->tunnista($data));
        }

        // Aseta tilakoodi
        if ($koodi) {
            $this->koodi($koodi);
        }

        // Lisää otsakkeet
        if ($otsakkeet) {
            $this->otsakkeet($otsakkeet);
        }
    }

    /**
     * Aseta vastauksen HTTP-tilakoodi.
     *
     * Esimerkki:
     *
     *     // Palauta Html-muotoinen 404 virhe
     *     return html('<h1>Virhe</h1>')
     *         ->koodi(404);
     *
     * @param int $koodi Uusi tilakoodi, esim 404
     * @return self
     */
    public function koodi($koodi)
    {
        $this->koodi = $koodi;
        return $this;
    }

    /**
     * Aseta datan tyyppi.
     *
     * Esimerkki:
     *
     *     // Rakenna Html-vastaus
     *     return vastaus($data)
     *         ->tyyppi('text/html');
     *
     * @param string $tyyppi
     * @return self
     */
    public function tyyppi($tyyppi)
    {
        $this->tyyppi = $tyyppi;
        return $this;
    }

    /**
     * Aseta vastausdata.
     *
     * Esimerkki:
     *
     *     // Luo geneerinen vastaus
     *     return vastaus()
     *         ->data('Virhe')
     *         ->tyyppi('text/plain')
     *         ->koodi(404);
     *
     * @param mixed $data
     * @return self
     */
    public function data($data)
    {
        $this->data = $this->tulkitse($data);
        return $this;
    }

    /**
     * Aseta yksi tai useampia otsakkeita.
     *
     * Esimerkki:
     *
     *     // Palauta Location-otsake selaimelle tilakoodilla 301
     *     return vastaus()
     *         ->koodi(301)
     *         ->otsakkeet([
     *             'Location' => 'https://savoksi.fi',
     *         ]);
     *
     * @param array<string,string> $otsakkeet
     * @return self
     */
    public function otsakkeet($otsakkeet)
    {
        foreach ($otsakkeet as $avain => $arvo) {
            $this->otsakkeet[$avain] = $arvo;
        }
        return $this;
    }

    /**
     * Hae tai aseta otsakkeen arvo.
     *
     * Esimerkki:
     *
     *     // Palauta vastaus Location-otsakeella
     *     return vastaus()
     *         ->otsake('Location', 'https://savoksi.fi')
     *         ->otsake('X-Savoksi', true);
     *
     *     // Poimi vastausolioon asetettu otsake
     *     return $vastaus->otsake('Location');
     *
     * @param string $nimi
     * @param mixed $arvo
     * @return ($arvo is void ? string : self)
     */
    public function otsake($nimi, $arvo = null)
    {
        // Jos funktiolle annetaan vain yksi argumentti, niin hae otsakkeen
        // arvo ja palauta se.
        if (func_num_args() == 1) {
            return $this->otsakkeet[$nimi] ?? '';
        }

        // Muussa tapauksessa aseta tai poista otsakkeen arvo
        if ($arvo) {
            $this->otsakkeet[$nimi] = merkkijono($arvo);
        } else {
            unset($this->otsakkeet[$nimi]);
        }
        return $this;
    }

    /**
     * Tulkitse vastausdata ja palauta se sopivammassa muodossa.
     *
     * @param mixed $data Raakadata
     * @return mixed
     */
    protected function tulkitse($data)
    {
        // Tallenna data oletuksena merkkijonona
        return merkkijono($data);
    }

    /**
     * Muotoile vastausdata merkkijonoksi.
     *
     * @param mixed $data
     * @return string
     */
    protected function muotoile($data)
    {
        // Muuta data oletuksena merkkijonoksi
        return merkkijono($data);
    }

    /**
     * Päättele vastausdatan MIME-tyyppi.
     *
     * @param mixed $data
     * @return string
     */
    protected function tunnista($data)
    {
        if (empty($data)) {
            return '';
        }
        return 'text/plain; charset=UTF-8';
    }

    /**
     * Muotoile vastaus merkkijonoksi
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->muotoile($this->data);
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
        case 'data':
            return $this->data;

        case 'tyyppi':
            return $this->tyyppi;

        case 'koodi':
            return $this->koodi;

        case 'otsakkeet':
            return $this->otsakkeet;
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

    /**
     * Palauta sarjoitettavat kentät json_encode funktiolle.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'data' => $this->data,
            'koodi' => $this->koodi,
            'tyyppi' => $this->tyyppi,
            'otsakkeet' => $this->otsakkeet,
        ];
    }
}
