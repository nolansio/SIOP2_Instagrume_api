
# Listes des routes API

--------------------------------------------------

## TOKEN

@ Génère un jeton d'authentification d'un utilisateur
api/users/

--------------------------------------------------

## USERS

@ Retourne toutes les informations de tous les utilisateurs
api/users/

@ Retourne toutes les informations de l'utilisateur par son ID
api/users/id/{id}

@ Retourne toutes les informations de l'utilisateur par son USERNAME
api/users/username/{username}

@ Retourne tout les usernames selon les caractere present dans la barre de recherche
api/users/search/{chaine de caractere}

@ Créé un nouvel utilisateur
api/users/

@ Modifie un utilisateur
api/users/

@ Supprime un utilisateur
api/users/id/{id}

@ Retourne son propre utilisateur
api/users/myself

--------------------------------------------------

## PUBLICATIONS

@ Retourne toutes les informations de toutes les publications
api/publications/

@ Retourne toutes les informations de la publication par son ID
api/publications/id/{id}

@ Créé une nouvelle publication
api/publications/

@ Supprime une publication
api/publications/id/{id}

--------------------------------------------------
