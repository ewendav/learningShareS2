# learningShareS2

## instructions

commande a lancer régulièrement pour installer les packages ajouté par les autres

```
composer install
```

```
browser-sync start --proxy "localhost:8000" --files "**/*.php, **/*.css, **/*.js, **/*.html, **/*.twig, **/*.yaml, **/*.env, var/cache/**, var/logs/**"
```

## Checker les normes PSR-12 (car 2 obsolète)

```
vendor/bin/phpcs .
```

## Mettre aux normes PSR-12 un fichier

```
vendor/bin/phpcbf <fichier>
```

ne pas oublier de parler de l'internationalisation faite manuellement
