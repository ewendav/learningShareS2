# learningShareS2

## Présentation fonctionnelle du projet

> LearningShare est une application web dont le concept repose sur l'échange de compétences entre utilisateurs Ces derniers peuvent échanger leurs savoirs en utilisant un système de jetons intégré à la plateforme La version 1 s'adressera à la population de Nantes Métropole

### La philosophie du projet 
Le but de l’application est l’échange de compétences sans but lucratif. LearningShare met en avant une approche basée sur l’entraide. La plateforme fonctionne sur deux principes :  
-	Les échanges de compétences ou partage : Un utilisateur propose un échange de compétences, il enseigne une compétence et en apprend une autre en retour. Deux utilisateurs échangent mutuellement une compétence.
-	Les cours : Un utilisateur propose un cours à une date fixe et une adresse. Les utilisateurs peuvent s’y inscrire et participer.
> *Ces deux principes sont englobés sous le terme d'une session*

### Un système de jetons comme unité d’échange
C’est un point essentiel du fonctionnement de la plateforme. Chaque utilisateur reçoit 100 jetons lors de la création de son compte. Les utilisateurs peuvent gagner des jetons en réalisant des cours ou en faisant des échanges de compétences. Ils les dépensent en assistant à des cours.
-	Gains de jetons : +20 jetons par cours donné et +40 jetons par participation à un échange.
-	Dépense de jetons : -25 jetons pour assister à un cours.

## Présentation technique du projet

Ce projet repose principalement sur une architecture MVC (Modèle-Vue-Contrôleur) pour gérer les différentes entités du système (sessions, partages, utilisateurs, etc.). 
Certaines contraintes ne sont pas prises en compte dans le code actuel, notamment la gestion des conflits de participation à un cours pour un utilisateur déjà impliqué dans une autre session à ce moment-là. 
Ce type de gestion sera implémenté dans la base de données à l’aide de **triggers** (qui n’ont pas encore été définis, car ils seront abordés ultérieurement dans le cadre du cours Admin des BD).

> Le projet respecte **l’ensemble des consignes** du sujet, tout en allant plus loin grâce à des fonctionnalités bonus comme l’authentification, l’internationalisation, la gestion multi-SGBD, etc.

## ✅ Fonctionnalités techniques principales

### 🔧 Architecture & Bonnes pratiques

- Utilisation d’**espaces de noms** (`Controllers`, `Models`, `Entity`, `Util`, etc.) pour une organisation claire.
- Respect des **standards PSR-1 à PSR-4**.
- Chargement automatique des classes via **Composer** et son autoload (voir `composer.json`).
- Séparation stricte des responsabilités :
  - `Controllers/` : logiques métiers liées aux routes.
  - `Models/` : interaction directe avec la base.
  - `Entity/` : représentation des objets métiers.
  - `Util/` : outils comme l’internationalisation, l’authentification, le container d'injection de dépendances ...

### 💾 Base de données

- Création complète de la base de données (tables, clés, relations, contraintes via les travaux du semestre 1)  ***script SQL fourni***.
- Insertion d’un jeu de **données construites via l'aide de l'ia générative** pour assurer la cohérence des relations (ex : utilisateurs, compétences, sessions, échanges, lieux...).
- Possibilité de réinitialisation propre à tout moment. (à faire)

### ⚙️ Configuration via .env

Les paramètres de connexion à la base sont stockés dans un fichier `.env`, sécurisé et facile à modifier.  
Exemple avec `.env.example` fourni :

```
host=127.0.0.1
port=8889
dbname=php
user=php
mdp=php
sgbd=mysql
charset=utf8
```
*- Le système nous permet de choisir entre **MySQL ou PostgreSQL** à l’aide de la variable `sgbd`.*

## 🔐 Authentification (bonus)

- Middleware `AuthMiddleware` développé maison.
- Authentification basée sur :
  - **Sessions PHP**
  - **Mots de passe hachés en bcrypt**
- Accès conditionnel en fonction de l’état de session

## 🌍 Internationalisation (en +)

- Mise en place d’un système i18n via un service fait maison dans `Util\I18n`
- Traductions automatiques dynamiques via une fonction twig custom à partir des préférences du navigateur
- Support de l'anglais et du français

### 📦 Dépendances installées

Voici les principaux packages utilisés via Composer :

- `php-di/php-di` – Injection de dépendances (PDO et Logger)
- `vlucas/phpdotenv` – Gestion du fichier `.env` pour le container de dépendances
- `nikic/fast-route` – Routage rapide au sein de l'application
- `twig/twig` – Moteur de templates les vues
- `monolog/monolog` – pour les logs
- `squizlabs/php_codesniffer` – Vérification des normes PSR 1 et 12(2 étant déprécié et remplacer par la 12)
- `phpcbf` - formate les fichiers PHP afin qu'ils respectent les normes PSR 

## 🚀 Utiliser ou Lancer le projet en local

### 👤 Utilisateurs de test – Scénarios

| Prénom    | Nom       | Email (Login)         | Mot de passe | Sessions proposées | Sessions auxquelles il s'est inscrit |
|-----------|-----------|------------------------|--------------|--------------------|-------------------------------------|
| **Alice** | Dupont    | `alice@example.com`    | `pass`  | 🎓 **Cours Python (Session 1)** | *(aucune)* |
| **Bob**   | Martin    | `bob@example.com`    | `pass`  | 🔁 **Échange Guitare (Session 2)**<br>🔁 **Échange Piano (Session 4)** | *(aucune)* |
| **Charlie** | Durand  | `charlie@example.com`    | `pass`  | 🔁 **Échange Algèbre (Session 3)** | *(aucune)* |
| **David** | Lemoine   | `david@example.com`    | `pass`  | *(aucune session proposée)* | Session 1 (Cours Python)<br> Session 2 (Échange Guitare)<br> Session 3 (Échange Algèbre) |
| **Eva**   | Petit     | `eva@example.com`    | `pass`  | 🎓 **Cours Photographie numérique (Session 5)** | Session 6 (Cours Gestion de projet agile) |
| **François** | Lemoine | `francois@example.com`  | `pass`  | 🎓 **Cours Gestion de projet agile (Session 6)** | Session 4 (Échange Piano)<br> Session 5 (Cours Photographie numérique) |

🧩 **Légende**  
- 🎓 = Session de type **cours (lesson)**
- 🔁 = Session de type **échange (exchange)**

### Utiliser en ligne

https://learningshare.gabin-prive.byraven.fr/

### Etapes de mise en place en local

1. Cloner le dépôt ou récupérer les repertoire de l'application:
   ```bash
   git clone <lien-du-repo>
   cd nom-du-repo
   ```

2. Installer les dépendances :
   ```bash
   composer install
   ```

3. Renomer le fichier `.env.example` :
   ```bash
   mv .env.example .env
   ```

4. Adapter les infos de connexion dans `.env` si besoin.

5. Lancer le script SQL `build_tables_mysql.sql` pour créer la base et `build_instances_mysql.sql` pour insérer les instances de données dans votre SGBD MySQL.

6. Lancer un serveur local (a exécuter depuis le dossier web/) :
   ```bash
   php -S localhost:8000 -t web
   ```

7. Accéder à l'application :
   ```
   http://localhost:8000
   ```

## Autres informations


### Checker les normes PSR-12 (car 2 obsolète)

```
vendor/bin/phpcs .
```

### Mettre aux normes PSR-12 un fichier

```
vendor/bin/phpcbf <fichier>
```

### 📌 Politique de Logging dans l’Application
**1. Ce qui est loggé ✅**
- Actions importantes : Connexion d’un utilisateur, création, modification, suppression de données.
- Erreurs et exceptions : Erreurs SQL, accès refusé, échecs d’authentification.
- Requêtes critiques : Accès à des ressources sensibles ou tentatives d’intrusion.

**2. Ce qui n’est pas loggé ❌**
- Les entités (Entity/) : Elles ne contiennent que des données et ne doivent pas dépendre du logger.
