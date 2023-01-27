<?php

use Savoksi\Virhe;
use Savoksi\Komento;

/**
 * Raportoi virhe.
 *
 * Esimerkki:
 *
 *     // Lähetä 404 virhe
 *     virhe('Tiedostoa ei löydy', 404);
 *
 * @param string $virheilmoitus Lyhyt virheilmoitus suomeksi
 * @param int $virhekoodi Valinnainen HTTP-tilakoodi, esim 404
 * @return never
 */
function virhe(string $virheilmoitus = '', int $virhekoodi = 0)
{
    throw new Virhe($virheilmoitus, $virhekoodi);
}
