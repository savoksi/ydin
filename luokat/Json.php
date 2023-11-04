<?php

namespace Savoksi;

class Json extends Vastaus
{
    protected function tulkitse($data)
    {
        // Tallenna raakadata
        return $data;
    }

    public function muotoile($data)
    {
        // Palauta data merkkijonona
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            // Ei voi muuttaa dataa Json-muotoon!  Tämä tapahtuu esimerkiksi
            // käsiteltäessä Latin1-koodattuja merkkijonoja tai binääridataa.
            virhe('Virheellinen parametri', $data);
        }
        return $json;
    }

    protected function tunnista($data)
    {
        return 'application/json';
    }
}
