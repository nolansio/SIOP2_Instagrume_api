# Instagrume

## Présentation

### Contexte fictif

> Voyant la proportion de personnes en surpoids augmenter, le gouvernement décide de lancer un appel à projets afin de trouver des solutions pour limiter l'obésité de la population.
> Pour répondre à ce besoin, vous proposez la création d'une plateforme web : Instagrume.
> Selon vous, cette application web permettra de promouvoir une alimentation saine en incitant ses utilisateurs à partager des photos de fruits et légumes.

Les utilisateurs peuvent publier, commenter et interagir avec les publications postées.  
Ce projet est réalisé en équipe de 3 dans le cadre de l'apprentissage dans le BTS SIO.

---

## Types d’utilisateurs et fonctionnalités

### Utilisateur non connecté
- Accéder à la page d'accueil et voir une sélection aléatoire de publications
- Rechercher un utilisateur et afficher ses publications et commentaires
- S’inscrire en créant un compte
- Se connecter via un formulaire d’authentification

### Utilisateur connecté
- Créer une publication avec photo et description
- Éditer la description de ses publications
- Supprimer ses publications
- Mettre un like ou dislike sur les publications des autres
- Modifier son profil (avatar, mot de passe)
- Ajouter, répondre, éditer ou supprimer ses commentaires
- Mettre un like ou dislike sur les commentaires des autres

### Modérateur
- Tous les droits d’un utilisateur connecté
- Bannir ou débannir temporairement des utilisateurs
- Locker une publication pour empêcher de nouveaux commentaires
- Supprimer n’importe quelle publication ou commentaire

---

## Dépôts Git

- **Client Web** : `instagrume_client` — [Lien GitHub](https://github.com/nolansio/SIOP2_Instagrume_client)
- **API** : `instagrume_api` — [Lien GitHub](https://github.com/nolansio/SIOP2_Instagrume_api)

---

## Technologies utilisées

- **Frontend** : HTML, CSS (Bootstrap), JavaScript, Twig
- **Backend** : PHP (Symfony)
- **Base de données** : MySQL
- **API** : Symfony + Doctrine
- **Sécurité** : JWT pour les routes protégées
- **Documentation** : Swagger via NelmioApiDocBundle

---

## Installation et configuration

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- MySQL 5.7+ ou MariaDB
- Serveur web (Apache/Nginx) ou serveur Symfony local

### 1. Cloner les dépôts Git
```bash
# Cloner l'API
git clone https://github.com/nolansio/SIOP2_Instagrume_api.git instagrume_api
cd instagrume_api

# Cloner le client (dans un autre répertoire)
git clone https://github.com/nolansio/SIOP2_Instagrume_client.git instagrume_client
cd instagrume_client
```

### 2. Installation de l'API

#### 2.1. Installer les dépendances
```bash
cd instagrume_api
composer install
```

#### 2.2. Générer les clés JWT
```bash
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

#### 2.5. Créer la base de données
Le projet dispose d'une commande personnalisée pour créer automatiquement la base de données :
```bash
php bin/console app:database:create
```

#### 2.6. Exécuter les migrations
```bash
php bin/console make:migration
```

```bash
php bin/console doctrine:migrations:migrate
```

Répondre `yes` pour confirmer l'exécution des migrations.

#### 2.6. Charger les fixtures (données de test)
```bash
php bin/console doctrine:fixtures:load
```

Répondre `yes` pour confirmer le chargement des fixtures.

> **Note** : Les fixtures créent des utilisateurs de test avec différents rôles (utilisateur standard, modérateur) ainsi que des publications et commentaires d'exemple.

#### 2.7. Lancer le serveur API
```bash
symfony server:start --port=3000 -d
# OU si symfony CLI n'est pas installé
php -S localhost:3000 -t public
```

L'API sera accessible à l'adresse : `http://localhost:3000`

### 3. Installation du client web

#### 3.1. Installer les dépendances
```bash
cd instagrume_client
composer install
```

#### 3.2. Lancer le serveur client
```bash
symfony server:start --port=8000 -d
# OU si symfony CLI n'est pas installé
php -S localhost:8000 -t public
```

Le client sera accessible à l'adresse : `http://localhost:8000`

### 4. Accéder à l'application

- **Client web** : http://localhost:8000
- **API** : http://localhost:3000
- **Documentation API (Swagger)** : http://localhost:3000/api/doc

### 5. Comptes de test

Après avoir chargé les fixtures, vous pouvez vous connecter avec les comptes suivants :

|  Username   | Mot de passe |      Rôle      |
|-------------|--------------|----------------|
|    root     |     root     | Administrateur |
|  moderator  |  moderator   |   Modérateur   |
|   albert    |    albert    |   Utilisateur  |
|    elon     |     elon     |   Utilisateur  |
|   Jessica   |   Jessica    |   Utilisateur  |
|    loup     |     loup     |   Utilisateur  |

> **Note** : Les identifiants exacts dépendent de vos fixtures. Consultez le fichier `src/DataFixtures/AppFixtures.php` dans l'API pour voir tous les comptes disponibles.

### 6. Commandes utiles
```bash
# Vider la base de données et recharger les fixtures
php bin/console doctrine:database:drop --force
php bin/console app:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Créer une nouvelle migration après modification d'entité
php bin/console make:migration

# Vider le cache
php bin/console cache:clear
```

### Troubleshooting

**Erreur de connexion à la base de données** :
- Vérifiez que MySQL est démarré
- Vérifiez les identifiants dans le fichier `.env`
- Assurez-vous que le port 3306 est bien utilisé par MySQL

**Erreur JWT** :
- Vérifiez que les clés JWT ont bien été générées dans `config/jwt/`
- Vérifiez les permissions de lecture sur ces fichiers

**Erreur CORS** :
- Si le client et l'API sont sur des ports différents, vérifiez la configuration CORS dans l'API (`config/packages/nelmio_cors.yaml`)

---

## Notes

- Ce projet est destiné à un usage pédagogique et non commercial.
- Les données utilisateurs sont fictives et uniquement à des fins de test.
