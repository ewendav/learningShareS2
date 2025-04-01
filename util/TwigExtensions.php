<?php

namespace Util;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtensions extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('asset', [$this, 'asset']),
        ];
    }

    public function asset($path)
    {
        return '/assets/' . ltrim($path, '/');
    }
}
