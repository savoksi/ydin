<?php

namespace Savoksi;

class Teksti extends Vastaus
{
    protected function tunnista($data)
    {
        return 'text/plain; charset=UTF-8';
    }
}
