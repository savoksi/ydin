<?php

namespace Savoksi;

use Exception;
use Throwable;

/**
 * Yleinen poikkeustilanne.
 */
class Virhe extends Exception
{
    /**
     * Luo yleinen virhe.
     *
     * Esimerkki:
     *
     *     // Nosta poikkeustilanne virheilmoituksella ja virhekoodilla
     *     throw new Virhe('Tiedostoa ei löydy', 404);
     *
     * @param string $virheilmoitus Virheilmoitus tekstinä
     * @param int $virhekoodi HTTP-virhekoodi, esim 404
     * @param Exception $poikkeus Edellinen poikkeus
     */
    public function __construct($virheilmoitus = '', $virhekoodi = 0, $poikkeus = null)
    {
        // Käytä oletusviestiä, jos viestiä ei anneta parametrina
        if (!$virheilmoitus) {
            $virheilmoitus = get_called_class();
        }

        // Alusta kantaluokka
        parent::__construct($virheilmoitus, $virhekoodi, $poikkeus);
    }

    /**
     * Hae virheilmoitus tekstinä.
     *
     * @return string
     */
    public function haeViesti()
    {
        return $this->getMessage();
    }

    /**
     * Hae virhekoodi.
     *
     * @return int
     */
    public function haeKoodi()
    {
        return $this->getCode();
    }
}

