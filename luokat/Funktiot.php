<?php
/**
 * Tämä tiedosto ladataan kaikkiin sovelluksiin siten, että täällä määritellyt
 * funktiot toimivat oletuksena kaikissa nimiavaruuksissa.  Yritä pitää tämän
 * tiedoston sisältö mahdollisimman lyhyenä!
 */
use Savoksi\Sovellus;
use Savoksi\Komento;
use Savoksi\Savoksi;
use Savoksi\Paate;
use Savoksi\Vastaus;
use Savoksi\Json;
use Savoksi\Html;
use Savoksi\Teksti;
use Savoksi\Merkisto;
use Savoksi\Merkkijono;
use Savoksi\Indeksi;
use Savoksi\Virhe;
use Savoksi\Virhekasittelija;

/**
 * Hae nykyinen tai suorita uusi sovellus.
 *
 * Esimerkki:
 *
 *     // Palauta nykyinen sovellus
 *     $sovellus = sovellus();
 *
 *     // Luo sovellus luokan nimellä ja suorita se parametrilla -v
 *     sovellus(TestiSovellus::class, '-v');
 *
 *     // Määrittele uusi sovellusluokka ja suorita se
 *     sovellus(new class extends Sovellus {
 *         public function __invoke() {
 *             tulosta("sovellus\n");
 *         }
 *     });
 *
 * @param Sovellus|string|callable|null $nimi
 * @param string ...$parametrit
 * @return ($nimi is null ? Sovellus : Vastaus)
 */
function sovellus($nimi = null, ...$parametrit)
{
    return Sovellus::sovellus($nimi, ...$parametrit);
}

/**
 * Luo uusi komponentti-olio.
 *
 * Esimerkki:
 *
 *     // Luo Savoksi\Hakemisto-olio lyhyellä nimellä
 *     $paate = luo('hakemisto');
 *
 *     // Luo Hakemisto-luokan olio parametrilla "/tmp"
 *     $paate = luo(Hakemisto::class, '/tmp');
 *
 * @template T of object
 * @param class-string<T>|string $nimi Komponentin lyhyt tai pitkä nimi
 * @param mixed ...$parametrit
 * @return ($nimi is class-string<T> ? T : object)
 */
function luo($nimi, ...$parametrit)
{
    return sovellus()->luo($nimi, ...$parametrit);
}

/**
 * Hae komponentti-olion instanssi.
 *
 * Esimerkki:
 *
 *     // Hae Savoksi\Paate -luokan olio lyhyellä nimellä
 *     $paate = hae('paate');
 *
 *     // Hae Savoksi\Paate -luokan olio pitkällä nimellä
 *     $paate = hae(Paate::class);
 *
 * @template T of object
 * @param class-string<T>|string $nimi Komponentin lyhyt tai pitkä nimi
 * @param string $aihe Luokan loppuosa, esim "Komento"
 * @return ($nimi is class-string<T> ? T : object)
 */
function hae($nimi, $aihe = '')
{
    return sovellus()->hae($nimi, $aihe);
}

/**
 * Hae asetuksen arvo.
 *
 * Esimerkki:
 *
 *     // Palauttaa fi tms
 *     return asetus('kieli');
 *
 * @param string $nimi
 * @return string|null
 */
function asetus($nimi)
{
    return sovellus()->asetus($nimi);
}

/**
 * Aseta asetuksen arvo.
 *
 * Esimerkki:
 *
 *     // Aseta yksi asetus
 *     aseta('kieli', 'fi');
 *
 * @param array<string,mixed>|string $nimi
 * @param mixed $arvo
 * @return void
 */
function aseta($nimi, $arvo = null)
{
    sovellus()->aseta($nimi, $arvo);
}

/**
 * Suorita huoltokomento.
 *
 * Esimerkki:
 *
 *     // Suorita asenna-komento parametrilla
 *     komento('asenna', 'savoksi/legenda');
 *
 *     // Suorita asenna-komento luokan nimellä
 *     komento(AsennaKomento::class, 'savoksi/legenda');
 *
 * @param Komento|class-string<Komento>|string|callable $nimi
 * @param string ...$parametrit
 * @return string|Vastaus|null
 */
function komento($nimi, ...$parametrit)
{
    return Komento::komento($nimi, ...$parametrit);
}

/**
 * Raportoi virhe sovelluksen toiminnassa.
 *
 * Esimerkki:
 *
 *     // Raportoi virhe
 *     virhe('Virheellinen parametri', $nimi);
 *
 * @param string $virheilmoitus Virheilmoitus merkkijonona
 * @param mixed ...$parametrit Virheilmoituksen parametrit
 * @return never
 */
#[Virhekasittelija]
function virhe($virheilmoitus = 'Virhe', ...$parametrit)
{
    throw new Virhe($virheilmoitus, ...$parametrit);
}

/**
 * Raportoi varoitus sovelluksen toiminnassa.
 *
 * Esimerkki:
 *
 *     // Raportoi varoitus
 *     varoitus('Virheellinen parametri', $nimi);
 *
 * @param string $varoitus Varoituksen merkkijonona
 * @param mixed ...$parametrit Varoituksen parametrit
 * @return void
 */
#[Virhekasittelija]
function varoitus($varoitus = 'Varoitus', ...$parametrit)
{
    if ((error_reporting() & E_USER_NOTICE) != 0) {
        throw new Virhe($varoitus, ...$parametrit);
    }
}

/**
 * Muodosta luokan pitkä nimi.
 *
 * Esimerkki:
 *
 *     // Palauttaa "Savoksi\LuoTilit"
 *     return savoksi('luo-tilit');
 *
 *     // Palauttaa "Sovellus\AsennaKomento"
 *     return savoksi('sovellus:asenna', 'komento');
 *
 * @param string $nimi Luokan pitkä tai lyhyt nimi, esim "asenna"
 * @param string $aihe Luokan loppuosa, esim "Komento"
 * @return string Luokan nimi, esim "Savoksi\AsennaKomento"
 */
function savoksi($nimi, $aihe = '')
{
    return Sovellus::savoksi($nimi, $aihe);
}

/**
 * Palauta luokan lyhyt nimi.
 *
 * Esimerkki:
 *
 *     // Palauttaa "paate"
 *     return lyhenne(Savoksi\Paate::class);
 *
 *     // Palauttaa "sovellus:asenna"
 *     return lyhenne(Sovellus\AsennaKomento::class, 'Komento');
 *
 * @param string $nimi Luokan pitkä tai lyhyt nimi, esim "Savoksi\Paate"
 * @param string $aihe Luokan loppuosa, esim "Komento"
 * @return string Lyhyt nimi, esim "paate"
 */
function lyhenne($nimi, $aihe = '')
{
    return sovellus()->lyhenne($nimi, $aihe);
}

/**
 * Hae komponentin luokkanimi.
 *
 * Esimerkki:
 *
 *     // Palauttaa "Savoksi\Paate" tms
 *     return luokka('paate');
 *
 *     // Palauttaa "Sovellus\AsennaKomento" tms
 *     return luokka('sovellus:asenna', 'komento');
 *
 * @param string $nimi Komponentin lyhyt tai pitkä nimi, esim "asenna"
 * @param string $aihe Luokan loppuosa, esim "Komento"
 * @return string Komponentin pitkä nimi, esim "Savoksi\AsennaKomento"
 */
function luokka($nimi, $aihe = '')
{
    return sovellus()->luokka($nimi, $aihe);
}

/**
 * Korvaa aaltosuluilla merkityt arvot merkkijonossa.
 *
 * @param string $malli Merkkijono, esim "heippa {nimi}"
 * @param mixed ...$arvot Nimetyt arvot
 * @return string Käännetty lause
 */
function korvaa($malli, ...$arvot)
{
    return hae(Merkkijono::class)->korvaa($malli, ...$arvot);
}

/**
 * Muuta parametrina annettu arvo merkkijonoksi.
 *
 * Esimerkki:
 *
 *     // Palauttaa merkkijonon "false"
 *     return merkkijono(false);
 *
 * @param mixed $arvo
 * @return string
 */
function merkkijono($arvo)
{
    return hae(Merkkijono::class)->tulkitse($arvo);
}

/**
 * Hae alkio nimellä tai indeksillä puumaisesta rakenteesta.
 *
 * Esimerkki:
 *
 *     // Palauttaa merkkijonon "c"
 *     return indeksi(2, [ 'a', 'b', 'c' ]);
 *
 *     // Palauttaa numeron 3
 *     return indeksi('c.1', [ 'c' => [ 1, 3, 5] ]);
 *
 * @param string|int|null|array<string> $alkio Alkion nimi tai indeksi
 * @param array<int|string>|object|null $taulu Taulukko tai puurakenne
 * @return mixed
 */
function indeksi($alkio, $taulu)
{
    return hae(Indeksi::class)->hae($alkio, $taulu);
}

/**
 * Tulosta teksti päätteelle.
 *
 * Esimerkki:
 *
 *     // Tulostaa "Heippa äiti!"
 *     tulosta('Heippa äiti!');
 *
 *     // Tulostaa "Heippa äiti!"
 *     tulosta('Heippa', 'äiti', '!');
 *
 * @param mixed ...$teksti
 * @return void
 */
function tulosta(...$teksti)
{
    hae(Paate::class)->tulosta(...$teksti);
}

/**
 * Muotoile vastaus Json-muotoon.
 *
 * Esimerkki:
 *
 *     // Luo Json-vastaus
 *     return json([ 1, 2, 3 ]);
 *
 * @param mixed $data
 * @return Json
 */
function json($data)
{
    return luo(Json::class, $data);
}

/**
 * Muotoile vastaus Html-muotoon.
 *
 * Esimerkki:
 *
 *     // Luo Html-vastaus
 *     return html('<h1>Otsikko</h1>');
 *
 * @param mixed $html
 * @return Html
 */
function html($html)
{
    if ($html instanceof Html) {
        return $html;
    }
    return luo(Html::class, $html);
}

/**
 * Muotoile vastaus tekstimuotoon.
 *
 * Esimerkki:
 *
 *     // Luo tekstivastaus
 *     return teksti('Ei löydy')->koodi(404);
 *
 * @param string|Teksti $teksti
 * @return Teksti
 */
function teksti($teksti)
{
    if ($teksti instanceof Teksti) {
        return $teksti;
    }
    return luo(Teksti::class, $teksti);
}

/**
 * Luo geneerinen vastausolio.
 *
 * Esimerkki:
 *
 *     // Luo geneerinen 404 virhe
 *     return vastaus('Not Found', 'text/plain', 404);
 *
 * @param mixed $data Raaka data tai vastausolio
 * @param string $tyyppi Arvon tyyppi, esim "text/plain"
 * @param int $koodi HTTP-tilakoodi, esim 200
 * @param array<string,string> $otsakkeet
 * @return Vastaus
 */
function vastaus($data = '', $tyyppi = '', $koodi = 200, $otsakkeet = [])
{
    if ($data instanceof Vastaus) {
        return $data;
    }
    return luo(Vastaus::class, $data, $tyyppi, $koodi, $otsakkeet);
}

/**
 * Palauta sana isolla alkukirjaimella
 *
 * Esimerkki:
 *
 *     // Palauttaa "Äyskäri HOI"
 *     return isoAlkukirjain('äyskäri HOI');
 *
 * @param string $sana
 * @return string
 */
function isoAlkukirjain($sana)
{
    return hae(Merkisto::class)->isoAlkukirjain($sana);
}

/**
 * Palauta lause pienaakkosilla.
 *
 * Esimerkki:
 *
 *     // Palauttaa "äyskäri"
 *     return pienetKirjaimet('ÄYSKÄRI');
 *
 * @param string $sana
 * @return string
 */
function pienetKirjaimet($sana)
{
    return hae(Merkisto::class)->pienetKirjaimet($sana);
}

/**
 * Palauta lause suuraakkosilla.
 *
 * Esimerkki:
 *
 *     // Palauttaa "ÄYSKÄRI"
 *     return isotKirjaimet('äyskäri');
 *
 * @param string $sana
 * @return string
 */
function isotKirjaimet($sana)
{
    return hae(Merkisto::class)->isotKirjaimet($sana);
}
