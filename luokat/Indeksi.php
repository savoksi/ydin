<?php

namespace Savoksi;

class Indeksi
{
    /**
     * Hae alkio nimellä tai indeksillä puumaisesta rakenteesta.
     *
     * Esimerkki:
     *
     *     // Palauttaa merkkijonon "c"
     *     return indeksi(2, [ 'a', 'b', 'c' ]);
     *
     *     // Palauttaa numeron 3
     *     return indeksi('c[1]', [ 'c' => [ 1, 3, 5] ]);
     *
     * @param string|int|null|array<string> $alkio Alkion nimi tai indeksi
     * @param array<int|string>|object|null $taulu Taulukko tai puurakenne
     * @return mixed
     */
    public function hae($alkio, $taulu)
    {
        // Muuta alkion nimi taulukoksi
        if (is_array($alkio)) {
            // Alkion nimi on jo valmiiksi taulukkomuodossa
            $komponentit = $alkio;
        } elseif (is_string($alkio)) {
            // Pilko alkion nimi taulukoksi.  Jos alkion nimi on esimerkiksi
            // "tulos[2].id", niin luo taulukko ['tulos', '2', 'id'].
            $komponentit = $this->tulkitse($alkio);
        } elseif (is_int($alkio)) {
            // Muuta alkion indeksi taulukoksi
            $komponentit = [ $alkio ];
        } elseif (is_null($alkio)) {
            // Ei indeksiä, palauta koko taulukko
            return $taulu;
        } else {
            // Tuntematon indeksin tyyppi
            virhe('Virheellinen alkio', $alkio);
        }

        // Hae indeksin osoittama alkio puurakenteesta
        $i = array_shift($komponentit);
        while ($i !== null) {
            // Hae indeksiä $i vastaava alkio nykyiseltä tasolta
            if (is_array($taulu) && isset($taulu[$i])) {
                $taulu = $taulu[$i];
            } elseif (is_object($taulu) && isset($taulu->{$i})) {
                $taulu = $taulu->{$i};
            } else {
                // Indeksiä $i vastaavaa alkiota ei löydy tältä tasolta
                return null;
            }

            // Hae seuraavan tason indeksi
            $i = array_shift($komponentit);
        }

        // Palauta löydetty alkio
        return $taulu;
    }

    /**
     * Aseta arvo taulukkoon indeksin osoittamaan paikkaan.
     *
     * @param array<int|string,mixed> $taulu Muokattava taulukko
     * @param string|int|null|array<string> $alkio Alkion nimi tai indeksi
     * @param mixed $arvo
     * @return mixed
     */
    public function aseta(&$taulu, $alkio, $arvo)
    {
        // Pilko moniosainen avain komponentteihinsa.  Jos avain on
        // esimerkiksi "taulu[0].avain", niin muodosta komponentit
        // "taulu", "0" ja "avain".
        if (is_array($alkio)) {
            // Alkion nimi on jo valmiiksi taulukkomuodossa
            $komponentit = $alkio;
        } elseif (is_string($alkio)) {
            // Pilko alkion nimi taulukoksi.  Jos alkion nimi on esimerkiksi
            // "tulos[2].id", niin luo taulukko ['tulos', '2', 'id'].
            $komponentit = $this->tulkitse($alkio);
        } elseif (is_int($alkio)) {
            // Muuta alkion indeksi taulukoksi
            $komponentit = [ $alkio ];
        } else {
            // Tuntematon indeksin tyyppi
            virhe('Virheellinen alkio', $alkio);
        }

        // Sijoita arvo taulukkoon
        while (true) {
            // Poimi tason indeksi, esim "0"
            $i = array_shift($komponentit);

            // Jos indeksi on viimeinen, niin sijoita arvo taulukkoon
            if (empty($komponentit)) {
                if (!isset($taulu[$i])) {
                    // Lisää uusi arvo (taulukko tai skalaarinen)
                    $taulu[$i] = $arvo;
                    return;
                }
                if (is_scalar($taulu[$i])) {
                    // Korvaa vanha skaarinen arvo
                    if (is_array($arvo)) {
                        virhe('Skalaarista arvoa ei voi korvata taulukolla', $alkio);
                    }
                    $taulu[$i] = $arvo;
                    return;
                }

                // Lisää olemassaolevaan taulukkoon rekursiivisesti
                if (!is_array($arvo)) {
                    virhe('Taulukkoa ei voi korvata skalaarisella arvolla', $alkio);
                }
                foreach ($arvo as $x => $y) {
                    $this->aseta($taulu[$i], $x, $y);
                }
                return;
            }

            // Luo seuraava taso tai siirry taulukon seuraavalle tasolle
            if (!isset($taulu[$i])) {
                $taulu[$i] = [];
            } elseif (!is_array($taulu[$i])) {
                // Skalaarista arvoa ei saa korvata taulukolla!
                virhe('Ei voi korvata asetusta', $alkio);
            }
            $taulu =& $taulu[$i];
        }
    }

    /**
     * Tulkitse merkkijonossa annettu alkion nimi.
     *
     * Esimerkki:
     *
     *     // Palauttaa alkionimen komponentit 'taulu', '4' ja 'a'
     *     return hae(Indeksi::class)->tulkitse('taulu[4].a');
     *
     * @param string $alkio Alkion nimi, esim "taulu[4].a"
     * @return array<string> Nimen komponentit, esim ['taulu', '4', 'a']
     */
    public function tulkitse($alkio)
    {
        $tila = 0;
        $komponentit = [];

        // Käy läpi alkion nimi merkki merkiltä
        $i = 0;
        $n = strlen($alkio);
        while ($i < $n) {
            // Hae seuraava merkki
            $c = substr($alkio, $i, 1);

            // Päättele seuraava toiminto haetun merkin ja nykyisen tilan
            // mukaan.
            switch ($tila) {
            case 0:
                // Lue indeksi merkkijonosta
                switch ($c) {
                case '.':
                case ']':
                    // Ylimääräinen piste ei ole sallittu!  Esimerkiksi nimi
                    // ".b" on virheellinen.
                    virhe('Virheellinen indeksi', $alkio);

                case '[':
                    // Poimi merkit seuraavaan sulkumerkkiin asti.  Jos alkion
                    // nimi on esimerkiksi "[id]", niin poimi indeksi "id".
                    $i++;
                    $j = strpos($alkio, ']', $i);
                    if ($j === false) {
                        // Indeksin päättävää sulkua ei löydy!
                        virhe('Virheellinen indeksi', $alkio);
                    }

                    // Ohita tyhjä indeksi.  Esimerkiksi nimi "a[]"
                    // käsitellään kuten "a".
                    if ($i < $j) {
                        $komponentit[] = substr($alkio, $i, $j - $i);
                    }

                    // Lue erotinmerkki indeksin perässä
                    $i = $j + 1;
                    $tila = 1;
                    break;

                default:
                    // Poimi merkit seuraavaan erottimeen saakka
                    $j = strcspn($alkio, '[].', $i);
                    if ($j == 0) {
                        virhe('Virheellinen indeksi', $alkio);
                    }
                    $komponentit[] = substr($alkio, $i, $j);

                    // Lue erotinmerkki indeksin perässä
                    $i += $j;
                    $tila = 1;
                }
                break;

            case 1:
                // Lue erotinmerkki indeksin perässä
                switch ($c) {
                case '.':
                    // Piste erottaa indeksit
                    $i++;
                    if ($i >= $n) {
                        // Piste nimen lopussa, esim "taulu."
                        virhe('Virheellinen indeksi', $alkio);
                    }
                    $tila = 0;
                    break;

                case '[':
                    // Kaksi taulukkoindeksiä peräjälkeen, esimerkiksi
                    // "[0][5]".
                    $tila = 0;
                    break;

                default:
                    // Tuntemattomia merkkejä indeksin perässä, esim "a[0]x"
                    virhe('Virheellinen indeksi', $alkio);
                }
                break;

            default:
                virhe('Virheellinen tila', $tila);
            }
        }
        return $komponentit;
    }
}
