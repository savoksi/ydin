<?php

namespace Savoksi;

use SimpleXMLElement;

class Merkkijono
{
    /**
     * Korvaa aaltosuluilla merkityt arvot merkkijonossa.
     *
     * @param string $malli Merkkijono, esim "heippa {nimi}"
     * @param mixed ...$arvot Nimetyt arvot
     * @return string Käännetty lause
     */
    public function korvaa($malli, ...$arvot)
    {
        // Jos ensimmäinen valinnainen parametri on taulukko tai olio, niin
        // hae korvattavat arvot siitä.  Tämä on antaa mahdollisuuden välittää
        // korvattavat arvot taulukossa, oliossa, numeroituina parametreina
        // tai nimettyinä parametreina.
        if (count($arvot) == 1 && isset($arvot[0])) {
            if (is_array($arvot[0]) || is_object($arvot[0])) {
                $arvot = $arvot[0];
            }
        }

        // Etsi ja korvaa aaltosuluilla erotetut nimetyt arvot
        $tulos = '';
        $i = 0;
        $n = strlen($malli);
        $korvattu = false;
        while ($i < $n) {
            // Etsi seuraavan aaltosululla erotetun nimen alkukohta
            $p = strpos($malli, '{', $i);
            if ($p === false) {
                // Malli loppui ilman aaltosulkua
                $p = $n;
            }

            // Kopioi tekstin osa ennen seuraavaa aaltosulkua sellaisenaan
            $tulos .= substr($malli, $i, $p - $i);
            if ($p >= $n) {
                // Teksti käsitely loppuun asti
                break;
            }

            // Ohita kopioitu teksti ja nimen aloittava aaltosulku
            $i = $p + 1;

            // Seuraako aaltosulkua numero tai nimi?
            $jatko = substr($malli, $i, 80);
            if (preg_match('/^([1-9][0-9]*)}/', $jatko, $x)) {
                // Aaltosulkua seuraa numero, esimerkiksi "{2}" => vastaava
                // arvo löytyy taulukon alkiosta indeksillä numero - 1.
                $nimi = intval($x[1], 10) - 1;
            } elseif (preg_match('/^([-_.a-z0-9\\[\\]]+)}/i', $jatko, $x)) {
                // Aaltosulkua seuraa muuttujanimi tai indeksi, esimerkiksi
                // "{id}" tai "{a[0]}" => arvo löytyy indeksillä.
                $nimi = $x[1];
            } else if (preg_match('/^}/', $jatko, $x)) {
                // Aaltosulut ilman sisältöä, esimerkiksi "{}" => vastaava
                // arvo löytyy taulukon alkiosta nolla.
                $nimi = 0;
            } else {
                // Aaltosulkua ei seuraa numero tai nimi.  Tulosta aaltosulku
                // sellaisenaan ja jatka etsintää seuraavasta merkistä.
                $tulos .= '{';
                continue;
            }

            // Hae nimeä vastaava arvo ja kopioi se tulokseen
            $tulos .= merkkijono(indeksi($nimi, $arvot));

            // Lauseessa oli vähintään yksi merkitty kohta, joten älä liitä
            // numeroituja arvoja automaattisesti lauseen perään.
            $korvattu = true;

            // Ohita nimi syötteessä ja jatka käsittelyä nimen päättävän
            // aaltosulun perästä.
            $i += strlen($x[0]);
        }

        // Jos malli ei määritellyt yhtään merkittyä arvoa, niin kopioi kaikki
        // paramerina annetut arvot tuloksen loppuun JSON-muodossa.  Tämä
        // yksinkertaistaa käännettävien lauseiden valmistelua: jos korvattava
        // arvo tulisi muutenkin merkkijonon loppuun, niin korvauspaikkaa ei
        // tarvitse merkitä lauseeseen.  Huomaa kuitenkin, että jos funktiolle
        // annetaan tarpeettomia parametreja, niin ne kaikki tulevat näkyviin.
        // Tämä voi olla hyödyllistä esimerkiksi virheilmoituksen yhteydessä,
        // mutta saattaa myös paljastaa id-numeroita tai muita arkaluontoisia
        // tietoja, jos parametreihin päätyy olioita tai taulukoita.
        if (!$korvattu && !empty($arvot)) {
            if (is_array($arvot) && count($arvot) == 1 && isset($arvot[0])) {
                $arvo = merkkijono($arvot[0]);
            } else {
                $arvo = merkkijono($arvot);
            }
            if (strlen($arvo) > 0) {
                $tulos .= ' ' . $arvo;
            }
        }
        return $tulos;
    }

    /**
     * Muuta parametrina annettu arvo merkkijonoksi.
     *
     * @param mixed $arvo
     * @return string
     */
    public function tulkitse($arvo)
    {
        // Palauta merkkijonoarvot sellaisenaan.  Huomaa, että tämä tarkistus
        // tulee tehdä aina ensimmäisenä, jotta merkkijonossa annettua arvoa
        // esimerkiksi "strlen" ei missään tapauksessa tulkita anonyyminä
        // funktiona.
        if (is_string($arvo)) {
            // FIXME: UTF-8 muunnos
            return $arvo;
        }

        // Jos arvo on anonyymi funktio, niin suorita funktio ja muotoile
        // funktion paluuarvo.  Huomaa, että is_callable tulkitsee tietyt
        // taulukot suoritettavina funktioina.  Jätetään tässä taulukot
        // käsittelemättä, jotta mahdollisesti netistä saatua arvoa ei missään
        // tapauksessa tulkita suoritettavana funktiona.
        if (!is_array($arvo) && is_callable($arvo)) {
            $arvo = call_user_func($arvo);
            if (is_string($arvo)) {
                // FIXME: UTF-8 muunnos
                return $arvo;
            }
        }

        // Muotoile SimpleXML-elementti merkkijonoksi
        if ($arvo instanceof SimpleXMLElement) {
            $xml = $arvo->asXML();
            if ($xml === false) {
                // Ei voitu muuttaa elementtiä merkkijonoksi.  Tämä ei pitäisi
                // koskaan tapahtua, koska tietoa ei tallenneta levylle.
                return 'invalid';
            }
            return $xml;
        }

        // Anna olion muotoilla oma merkkijonoarvonsa
        if (is_object($arvo) && method_exists($arvo, '__toString')) {
            // FIXME: UTF-8 muunnos
            return $arvo->__toString();
        }

        // Muotoile totuusarvo merkkijonona "true" tai "false"
        if (is_bool($arvo)) {
            return $arvo ? 'true' : 'false';
        }

        // Muuta kokonaisluvu merkkijonoiksi
        if (is_int($arvo)) {
            return (string) $arvo;
        }

        // Tulosta null-arvo merkkijonona "null"
        if (is_null($arvo)) {
            return 'null';
        }

        // Muotoile taulukot, liukuluvut ja muuntyyppiset arvot JSON-muotoon.
        // Huomaa, että tätä funktiota käytetään usein virhekäsittelyn
        // yhteydessä, joten poikkeuksia ei saa nostaa, vaikka arvon
        // käsittelyssä tapahtuisi virhe.  Huomaa myös, että json_encode ei
        // serialisoi olioita, vaan ne tulostuvat muodossa "{}".
        $data = json_encode(
            $arvo,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($data === false) {
            // Dataa ei saatu muutettua Json-muotoon!  Tämä tapahtuu
            // esimerkiksi käsiteltäessä Lati1-koodattuja merkkijonoja tai
            // binääridataa.
            return 'invalid';
        }
        return $data;
    }
}
