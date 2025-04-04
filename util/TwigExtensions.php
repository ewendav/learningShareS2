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
            new TwigFunction('traduction', [$this, 'traduction']),
            new TwigFunction('getCategoriesIndexed', [$this, 'getCategoriesIndexed']),
        ];
    }
    
    /**
     * Récupère les catégories indexées par ID
     */
    public function getCategoriesIndexed()
    {
        return \Models\CategorieModel::getAllIndexedById();
    }

    public function asset($path)
    {
        return '/assets/' . ltrim($path, '/');
    }

    // permet de traduire les strings en fonction de la langue du navigateur
    // permet de traduire les strings en fonction de la langue du navigateur
    public function traduction(string $terme)
    {
        $tableauDeTraductions = include 'i18n.php';

        $acceptedLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $primaryLanguage = substr($acceptedLanguages, 0, 2);

        if ($primaryLanguage != 'fr' && $primaryLanguage != 'en') {
            $primaryLanguage = 'fr';
        }

        if (array_key_exists($primaryLanguage, $tableauDeTraductions)) {
            if (array_key_exists($terme, $tableauDeTraductions[$primaryLanguage])) {
                return $tableauDeTraductions[$primaryLanguage][$terme];
            }
        }

        return $terme;
    }
}
