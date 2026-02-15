ZenHealth - Gestion d'Institut de Beauté



**Projet SGBD 2025-2026** Application web de gestion des réservations, des soins et du personnel pour l'institut de beauté ZenHealth.



**Description**



ZenHealth est une application intranet développée en **PHP** utilisant **Eloquent**. Elle permet :

* Gestion des réservations de cabines.
* Commande de soins et services.
* Encaissement et facturation.
* Gestion administrative.



**Installation et Configuration**



**Prérequis**

* PHP >= 8.2
* Composer
* Serveur MySQL / MariaDB



**Installation des dépendances**

À la racine du projet, lancez la commande suivante pour installer Eloquent et les autres dépendances :

*composer install*



**Base de Données**

Créez une base de données MySQL.



Importez le script SQL fourni pour créer les tables et insérer les données de test :



*Fichier : zenhealth.sql*



Configurez l'accès à la base de données dans le fichier de configuration :



*Fichier : src/conf/conf.ini*



Modifiez host, database, username, et password selon votre environnement.



**Guide d'Utilisation**

L'application distingue deux rôles d'utilisateurs : Hôtesse et Gestionnaire.



**Connexion**

Pour tester l'application, utilisez les identifiants présents dans la base de données.



Compte Gestionnaire :

ID : 1

Mot de passe : $#;§èm$$$$$0



Compte Hôtesse :

ID : 2

Mot de passe : $xy#;§èm$$$$$1



(Note : Les mots de passe sont stockés tels quels dans la base de données fournie).



**Fonctionnalités Hôtesse**

Une fois connectée, une hôtesse peut :



Réserver une cabine :

Menu : Réserver une cabine.

Saisir le numéro de cabine, la date/heure et le nombre de personnes.

Contrôle : Le système vérifie la disponibilité et la capacité de la cabine.



Commander un service :

Menu : Commander un service.

Associer un service à une réservation existante.

Contrôle : Le stock journalier du service est décrémenté. Impossible de commander si la réservation est déjà payée.



**Fonctionnalités Gestionnaire**

Un gestionnaire possède les droits de l'hôtesse, plus des actions d'administration spécifiques :



Affecter une hôtesse :

Menu : Affecter une hôtesse.

Lier une hôtesse à une cabine spécifique.

Contrôle : Une cabine ne peut avoir qu'une seule hôtesse affectée.



Encaisser une réservation :

Menu : Encaisser réservation.

Calcule automatiquement le montant total des services consommés.

Valide le paiement et clôture la réservation.



Gérer les services :

Menu : Gérer les services.

Permet de modifier le prix unitaire ou le stock journalier d'un service.



Annuler une réservation :

Menu : Annuler une réservation.

Supprime une réservation et remet les services en stock.

Contrôle : Impossible d'annuler une réservation déjà payée.



**Architecture Technique**

Dispatcher : Point d'entrée. Il analyse l'URL, vérifie la session et dirige vers la bonne Action.



Actions : Chaque fonctionnalité est une classe distincte qui gère le formulaire HTML.



Repository : Cette classe contient toute la logique et les transactions SQL.



Modèles Eloquent : Représentation objet des tables de la BDD.



Authentification \& Autorisation :

AuthnProvider : Gère la connexion et la session.

Authz : Vérifie les droits.



Gestion des Transactions

Toutes les opérations modifiant la base de données sont encapsulées dans des transactions au sein de la classe ZenManager. Cela garantit l'intégrité des données même en cas d'accès simultanés par plusieurs hôtesses.



Auteur : ADAM Tristan / LOGEART Pierre

