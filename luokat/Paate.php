<?php

namespace Savoksi;

/**
 * Syöte/tulostusrajapinta.
 */
class Paate
{
    /**
     * Tulosta teksti päätteelle.
     *
     * @param string ...$teksti
     * @return void
     */
    public function __invoke(...$teksti)
    {
        $this->tulosta(...$teksti);
    }

    /**
     * Tulosta teksti päätteelle.
     *
     * @param mixed ...$teksti
     * @return void
     */
    public function tulosta(...$teksti)
    {
        // Erikoismerkit, joiden perään ei tulosteta välilyöntiä
        $alku = " \r\n\t={[(<>";

        // Erikoismerkit, joiden eteen ei tulosteta välilyöntiä
        $loppu = " \r\n\t=}])<>;:,.!?%";

        // Muotoile argumentit merkkijonoiksi riville välilyönneillä
        // erotettuna.
        $rivi = '';
        foreach ($teksti as $sana) {
            // Muuta argumentti merkkijonoksi
            $sana = merkkijono($sana);

            // Päättele, erotetaanko seuraava argumentti välilyönnillä
            if (strlen($rivi) == 0) {
                // Älä lisää välilyöntiä rivin alkuun
                /*NOP*/;
            } elseif (strlen($sana) == 0) {
                // Älä lisää välilyöntiä ennen tyhjää sanaa
                /*NOP*/;
            } elseif (strpos($alku, substr($rivi, -1)) !== false) {
                // Älä lisää välilyöntiä erikoismerkin perään
                /*NOP*/;
            } elseif (strspn($sana, $loppu) > 0) {
                // Älä lisää välilyöntiä ennen erikoismerkkiä
                /*NOP*/;
            } else {
                // Muussa tapauksessa erota sana välilyönnillä
                $rivi .= ' ';
            }

            // Lisää sana rivin loppuun
            $rivi .= $sana;
        }

        // Lisää rivin loppuun rivinvaihto, jos ei ole
        $n = strlen($rivi);
        switch (substr($rivi, $n - 1)) {
        case "\r":
        case "\n":
            // Rivin lopussa on jo rivinvaihto, älä lisää toista
            /*NOP*/;
            break;

        case '$':
            // Dollarimerkki rivin lopussa estää rivinvaihdon ja jätetään
            // tulostamatta.
            $rivi = substr($rivi, 0, $n - 1);
            break;

        default:
            // Muussa tapauksessa lisää rivinvaihto
            $rivi .= "\n";
        }

        // Tulosta muotoiltu rivi
        echo $rivi;
    }
}
