<?php

namespace Savoksi;

/**
 * Asenna uusi moduuli.
 *
 * Tämä komento suoritetaan savoksi-skriptillä esimerkiksi:
 *
 *     php savoksi asenna savoksi/testi
 *
 * missä "savoksi/testi" on asennettavan moduulin nimi.
 */
class AsennaKomento extends Komento
{
    /**
     * Alusta projekti käyttöä varten.
     *
     * Huomaa, että Composer itse ja composer.lock-tiedoston määrittelemät
     * moduulit on asennettu jo ennen tämän funktion suoritusta
     * savoksi-skriptissä.  Tätä komentoa tarvitaan lähinnä uusien
     * moduuleiden käyttöönotossa.
     *
     * @param string ...$parametrit
     * @return Vastaus
     */
    public function __invoke(...$parametrit)
    {
        if (!empty($parametrit)) {
            // Asenna uusi moduuli composer.json-tiedostoon
            $this->suorita('require', ...$parametrit);
        } else {
            // Asenna composer.json-tiedostossa mainitut moduulit
            $this->suorita('install');
        }

        // Palauta OK vastaus
        return vastaus();
    }

    /**
     * Suorita Composer-komento.
     *
     * @param string ...$parametrit
     * @return void
     */
    public function suorita(...$parametrit)
    {
        suoritaComposer(...$parametrit);
    }
}

// Funktio suoritaComposer on määritelty vain käynnistettäessä savoksi-komento
// komentoriviltä.  Alla olevan määrittelyn tarkoitus on pitää PHPStan
// hiljaisena.
if (!function_exists('suoritaComposer')) {
    /**
     * @param string $nimi
     * @param string ...$parametrit
     * @return mixed
     */
    function suoritaComposer($nimi, ...$parametrit)
    {
        virhe('Savoksi-komento ei suoritettu');
    }
}
