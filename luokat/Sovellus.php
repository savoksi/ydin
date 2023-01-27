<?php

namespace Savoksi;

use Throwable;

/**
 * Erilaisten sovellusten kantaluokka.
 */
class Sovellus
{
    // Parhaillaan suoritettava sovellus
    protected static ?Sovellus $sovellus = null;

    /**
     * Suorita sovellus.
     *
     * @param array<string> $parametrit
     * @return void
     */
    public function suorita(array $parametrit = [])
    {
        try {
            // Aseta tämä sovellus aktiiviseksi
            $edellinen = self::$sovellus;
            self::$sovellus = $this;

            // Alusta uusi sovellus
            $this->_alusta($parametrit);

            // Suorita uuden sovelluksen pääohjelma
            try {
                $this->_suorita();
            } finally {
                // Siivoa sovelluksen jäljiltä
                $this->_lopeta();
            }
        } finally {
            // Palauta aiempi sovellus aktiiviseksi
            self::$sovellus = $edellinen;
        }
    }

    /**
     * Alusta sovellus.
     *
     * @param array<string> $parametrit
     * @return void
     */
    protected function _alusta(array $parametrit)
    {
        // Aliluokka määrittelee tämän funktion tarvittaessa
    }

    /**
     * Suorita sovellus.
     *
     * @return void
     */
    protected function _suorita()
    {
        // Aliluokka määrittelee tämän funktion
        virhe('Ei toteutettu');
    }

    /**
     * Siivoa sovellus.
     *
     * @return void
     */
    protected function _lopeta()
    {
        // Aliluokka määrittelee tämän funktion tarvittaessa
    }
}
