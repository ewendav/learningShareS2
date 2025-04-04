# learningShareS2

## Contexte du projet

Ce projet repose principalement sur une architecture MVC (Modèle-Vue-Contrôleur) pour gérer les différentes entités du système (sessions, partages, utilisateurs, etc.). 
Certaines contraintes ne sont pas prises en compte dans le code actuel, notamment la gestion des conflits de participation à un cours pour un utilisateur déjà impliqué dans une autre session à ce moment-là. 
Ce type de gestion sera implémenté dans la base de données à l’aide de **triggers** (qui n’ont pas encore été définis, car ils seront abordés ultérieurement dans le cadre du cours Admin des BD).


## 👤 Utilisateurs de test – Scénarios complets avec sessions

| Prénom    | Nom       | Email (Login)         | Mot de passe | Sessions proposées | Sessions auxquelles il est inscrit |
|-----------|-----------|------------------------|--------------|--------------------|-------------------------------------|
| **Alice** | Dupont    | `user1@example.com`    | `password1`  | 🎓 **Cours Python (Session 1)** | Session 1 (Cours Python) |
| **Bob**   | Martin    | `user2@example.com`    | `password2`  | 🔁 **Échange Guitare (Session 2)**<br>🔁 **Échange Piano (Session 4)** | Session 2 (Échange Guitare)<br> Session 4 (Échange Piano) |
| **Charlie** | Durand  | `user3@example.com`    | `password3`  | 🔁 **Échange Algèbre (Session 3)** | Session 3 (Échange Algèbre) |
| **David** | Lemoine   | `user4@example.com`    | `password4`  | *(aucune session proposée)* | Session 1 (Cours Python)<br> Session 2 (Échange Guitare)<br> Session 3 (Échange Algèbre) |
| **Eva**   | Petit     | `user5@example.com`    | `password5`  | 🎓 **Cours Photographie numérique (Session 5)** | Session 6 (Cours Gestion de projet agile) |
| **François** | Lemoine | `user6@example.com`  | `password6`  | 🎓 **Cours Gestion de projet agile (Session 6)** | Session 4 (Échange Piano)<br> Session 5 (Cours Photographie numérique) |

---

🧩 **Légende**  
- 🎓 = Session de type **cours (lesson)**
- 🔁 = Session de type **échange (exchange)**

---

Si tu veux, je peux aussi générer ce tableau au format CSV ou JSON, pour intégration dans un back ou des tests automatisés. Tu veux ?


## 📌 Politique de Logging dans l’Application
### 1. Ce qui est loggé ✅
- Actions importantes : Connexion d’un utilisateur, création, modification, suppression de données.

- Erreurs et exceptions : Erreurs SQL, accès refusé, échecs d’authentification.

- Requêtes critiques : Accès à des ressources sensibles ou tentatives d’intrusion.

### 2. Ce qui n’est pas loggé ❌
- Les entités (Entity/) : Elles ne contiennent que des données et ne doivent pas dépendre du logger.

- Les requêtes non critiques : Simple affichage de pages sans impact sur les données.

- Les logs excessifs : Éviter le spam en ne loggant pas chaque action utilisateur basique (ex : clics).

### 👉 Pourquoi ?
Les logs doivent être utiles pour suivre l’activité critique, détecter les erreurs et sécuriser l’application sans surcharger inutilement les fichiers logs. 🚀

---

## Librairies utilisées

Voici une liste des librairies utilisées dans ce projet, ainsi qu'une brève explication de leur rôle :

### Librairies principales (production) :

- **`twig/twig`** (`^3.0`)  
  Twig est un moteur de templates flexible et puissant pour PHP. Il permet de séparer la logique de présentation du code métier, en offrant un moyen simple de gérer l'affichage des vues.

- **`symfony/ux-twig-component`** (`^2.23`)  
  Cette bibliothèque permet de simplifier l'intégration de composants réactifs dans les templates Twig en utilisant le framework Symfony. Elle facilite l'intégration de l’UX dans les applications PHP avec Twig.

- **`delight-im/auth`** (`^8.3`)  
  Cette bibliothèque fournit des outils pour l'authentification des utilisateurs dans les applications PHP. Elle permet de gérer la connexion, l'enregistrement et les sessions utilisateurs de manière sécurisée.

- **`monolog/monolog`** (`^3.9`)  
  Monolog est un gestionnaire de logs pour PHP. Il permet d'enregistrer et de gérer les logs de l'application (erreurs, informations, avertissements, etc.), avec de nombreuses options de stockage, telles que les fichiers, bases de données, ou services externes.

### Librairies de développement (dev) :

- **`squizlabs/php_codesniffer`** (`^3.12`)  
  PHP_CodeSniffer est un outil qui permet de détecter les violations de conventions de codage dans les fichiers PHP. Il aide à maintenir un code propre et uniforme, et est souvent utilisé dans les projets pour s'assurer que le code suit les normes définies.

- **`delight-im/auth`** (`^8.3`)  
  (Réutilisé en production) La même bibliothèque que celle listée dans les dépendances principales, utilisée pour la gestion de l'authentification des utilisateurs.

- **`php-di/php-di`** (`^7.0`)  
  PHP-DI est une bibliothèque d'injection de dépendances pour PHP. Elle permet de gérer les dépendances entre les différentes classes de manière souple et modulaire, facilitant ainsi le test unitaire et la maintenance du code.

- **`vlucas/phpdotenv`** (`^5.6`)  
  PHP dotenv est un outil permettant de charger des variables d'environnement depuis un fichier `.env`. Cela permet de gérer de manière sécurisée les configurations sensibles (comme les clés d'API ou les informations de base de données) sans les exposer directement dans le code source.

- **`ext-pdo`** (`*`)  
  L'extension PDO (PHP Data Objects) permet de gérer les interactions avec les bases de données de manière sécurisée. Elle permet d'exécuter des requêtes SQL et de récupérer les résultats de manière indépendante du moteur de base de données utilisé.

- **`nikic/fast-route`** (`^1.3`)  
  FastRoute est un moteur de routage rapide pour les applications PHP. Il permet de définir des règles de routage pour associer les URL demandées à des fonctions ou des contrôleurs spécifiques. Cette bibliothèque est optimisée pour des performances élevées.

---

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
