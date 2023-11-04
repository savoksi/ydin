<?php

namespace Savoksi;

use Composer\InstalledVersions;

use Throwable;

/**
 * Huoltokomentojen kantaluokka.
 *
 * Tämä luokka määrittelee kaikille komentoriviltä ajettaville
 * huoltokomennoille yhteiset toiminnot.
 */
class Komento extends Sovellus
{
    // Tulostustasot
    public const HILJAINEN = 'hiljainen';
    public const NORMAALI = 'normaali';
    public const SEURANTA = 'seuranta';

    /** @var string $tulostustaso */
    public $tulostustaso = self::NORMAALI;

    /**
     * Luo komento-olio ja suorita se.
     *
     * @param Komento|class-string<Komento>|string|callable $nimi
     * @param string ...$parametrit
     * @return Vastaus
     */
    public static function komento($nimi, ...$parametrit)
    {
        // Luo komento-olio
        if (is_string($nimi)) {
            // Selvitä komento-olion luokka nimen perusteella
            /** @var class-string<Komento> $luokka */
            $luokka = savoksi($nimi, 'Komento');

            // Luo uusi komento-olio luokkanimen avulla.  Tämä antaa nykyisen
            // sovelluksen tarvittaessa ylikirjoittaa komentoja.
            $komento = luo($luokka);
        } elseif ($nimi instanceof Komento) {
            // Parametrit on jo Komento-luokan olio
            $komento = $nimi;
        } elseif (is_callable($nimi)) {
            // Luo komento-olio anonyymistä funktiosta
            $komento = new class ($nimi) extends Komento {
                /** @var callable $funktio */
                protected $funktio;

                /**
                 * @param callable $funktio
                 */
                public function __construct($funktio)
                {
                    $this->funktio = $funktio;
                }

                /**
                 * @param string ...$parametrit
                 * @return Vastaus
                 */
                public function __invoke(...$parametrit)
                {
                    return vastaus(($this->funktio)(...$parametrit));
                }
            };
        } else {
            virhe('Virheellinen parametri', $nimi);
        }

        // Tulkitse komentoriviparametrit ja poimi tiedostonimet
        // komentoriviltä.
        $tiedostot = $komento->tulkitseKomentorivi(...$parametrit);

        // Suorita komento tiedostoparametreilla
        try {
            return sovellus($komento, ...$tiedostot);
        } catch (Throwable $e) {
            // Poikkeus
            switch ($komento->tulostustaso) {
            case self::HILJAINEN:
            case self::NORMAALI:
                // Tulosta vain virheilmoitus
                tulosta($e->getMessage());
                break;

            case self::SEURANTA;
                // Tulosta poikkeus täydellisillä tiedoilla
                throw $e;
            }
            return vastaus();
        }
        /*NOTREACHED*/
    }

    /**
     * Tulkitse komentoriviparametrit.
     *
     * @param string ...$parametrit
     * @return array<string>
     */
    public function tulkitseKomentorivi(...$parametrit)
    {
        // Käy läpi kaikki funktion parametrit ja poimi talteen tiedostonimet
        $i = 0;
        $n = count($parametrit);
        $tiedostot = [];
        while ($i < $n) {
            // Onko kyseessä tiedostonimi?
            $parametri = $parametrit[$i++];
            if (substr($parametri, 0, 1) != '-') {
                // On, tallenna tiedostonimi
                $tiedostot[] = $parametri;
                continue;
            }

            // Poimi vivun mahdollinen argumentti
            if ($i < $n && substr($parametrit[$i], 0, 1) != '-') {
                $argumentti = $parametrit[$i];
            } else {
                $argumentti = null;
            }

            // Tulkitse vipu
            switch ($parametri) {
            case '--':
                // Kaksi viivaa päättää vipujen tulkinnan: kaikki loput
                // komentoriviparametrit tulkitaan tiedostoniminä vaikka ne
                // alkaisivat viivalla.
                while ($i < $n) {
                    $tiedostot[] = $parametrit[$i++];
                }
                break;

            case '-q':
            case '--quiet':
                // Hiljainen operaatio
                $this->tulostustaso = self::HILJAINEN;
                break;

            case '-v':
            case '--verbose':
                // Äänekäs operaatio
                $this->tulostustaso = self::SEURANTA;
                break;

            default:
                // Välitä geneerinen komentorivioptio aliluokan tulkittavaksi
                // FIXME:
                virhe('Virheellinen parametri', $parametri);
            }
        }

        // Palauta lista tiedostonimiä komentoriviltä
        return $tiedostot;
    }
}
