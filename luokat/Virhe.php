<?php

namespace Savoksi;

use Throwable;
use Exception;

class Virhe extends Exception
{
    /**
     * Luo virhe merkkijonosta.
     *
     * @param string $virheilmoitus Virheilmoitus merkkijonona
     * @param mixed ...$parametrit Virheilmoituksen parametrit
     * @return Virhe
     */
    public function __construct($virheilmoitus, ...$parametrit)
    {
        parent::__construct(korvaa($virheilmoitus, ...$parametrit));
    }
}
