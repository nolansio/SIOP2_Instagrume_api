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

1. Cloner les dépôts Git (client et API)
2. Installer les dépendances avec Composer
3. Configurer la base de données et exécuter les migrations
4. Charger les fixtures pour les données de test
5. Lancer le serveur Symfony pour le client et l’API
6. Accéder à l’application via le navigateur

---

## Notes

- Ce projet est destiné à un usage pédagogique et non commercial.
- Les données utilisateurs sont fictives et uniquement à des fins de test.
