<?php

namespace Savoksi;

/**
 * Päivitä moduulit.
 *
 * Tämä komento suoritetaan savoksi-skriptillä esimerkiksi:
 *
 *     php savoksi paivita
 */
class PaivitaKomento extends Komento
{
    // Päivitä Composer-moduulit uusimpiin versioihin.
    public function __invoke(...$parametrit)
    {
        $this->suorita('update', ...$parametrit);
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
