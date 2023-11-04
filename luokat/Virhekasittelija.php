<?php

namespace Savoksi;

use Attribute;

/**
 * Merkitse funktio virhekäsittelijäksi.
 *
 * Virhekäsittelijän sisällä nostettu poikkeus raportoidaan testeissä ikään
 * kuin virhe olisi tapahtunut funktiota kutsuttaessa.
 *
 * Esimerkki:
 *
 *     // Määrittele funktio attribuutilla
 *     #[Virhekasittelija]
 *     function testi() {
 *         virhe('hep');
 *     }
 *
 *     // Kutsu virhekäsittelijää funktiota
 *     testi(); // <-- Näkyy kutsupinossa paikkana, jossa virhe tapahtui
 */
#[Attribute]
class Virhekasittelija
{
}
