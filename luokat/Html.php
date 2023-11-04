<?php

namespace Savoksi;

class Html extends Vastaus
{
    protected function tunnista($data)
    {
        return 'text/html; charset=UTF-8';
    }
}
