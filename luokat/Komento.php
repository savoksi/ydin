<?php

namespace Savoksi;

use Throwable;

class Komento extends Sovellus
{
    /**
     * Luo komento-olio nimen perusteella.
     *
     * Funktio ottaa argumenttina komennon nimen.
     *
     * Esimerkki:
     *
     *     // Palauttaa TestiKomento-luokan ilmentymän
     *     $olio = Komento::luo('testi');
     *
     * @param string $nimi Komennon nimi, esim "testi"
     * @return Komento
     */
    public static function luo(string $nimi)
    {
        // Muodosta komento-olion luokkanimi komentoriviparametrin
        // perusteella.  Jos ensimmäinen parametri on esimerkiksi "testi",
        // niin muodosta komentoluokan nimi "Savoksi\TestiKomento".
        $luokka = 'Savoksi\\' . ucfirst($nimi) . 'Komento';
        if (!class_exists($luokka)) {
            virhe("Komentoa $nimi ei löydy");
        }

        // Muodosta komento-olio
        return new $luokka();
    }

    /**
     * Alusta komento.
     *
     * @param array<string> $parametrit
     * @return void
     */
    protected function _alusta(array $parametrit)
    {
        // Tulkitse komentoriviparametrit
        // FIXME:
    }

    /**
     * Suorita komento.
     *
     * @return void
     */
    protected function _suorita()
    {
        // Suorita valittu komento
        virhe('Ei toteutettu');
    }

    /**
     * Siivoa komennon jäljet.
     *
     * @return void
     */
    protected function _lopeta()
    {
        // Aliluokka määrittelee tämän komennon tarvittaessa
    }
}
