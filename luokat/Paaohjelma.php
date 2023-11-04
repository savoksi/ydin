<?php

namespace Savoksi;

use Attribute;

/**
 * Merkitse funktio pääohjelmaksi.
 *
 * Pääohjelma ja sen yläpuolella olevat kutsut jätetään raportoimatta
 * virhetilanteissa, jolloin kutsupinot siistiytyvät.
 */
#[Attribute]
class Paaohjelma
{
}
