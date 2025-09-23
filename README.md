# 🎬 Cinéphoria – Application Web

ECF (Évaluation en cours de formation) Studi - CDA (Concepteur Développeur d’Applications)

---

## 📖 Description
Cinéphoria est une application de gestion de cinéma qui combine :  
- Une base de données **relationnelle (PostgreSQL)** pour la gestion des films, séances, salles, utilisateurs, réservations.  
- Une base de données **non relationnelle (MongoDB)** pour l’affichage des statistiques (ex. nombre de réservations par film sur 7 jours) via un Dashboard.  

L’application web permet aux visiteurs de consulter les films et séances disponibles, aux utilisateurs de réserver et noter des séances, et aux employés/administrateurs de gérer le catalogue et suivre les statistiques.

---

## 🛠️ Installation et configuration

L’environnement est basé sur **Docker** afin d’assurer une portabilité et une uniformité.  

### 1. Prérequis
Avant de démarrer, installer :  
- [Docker Desktop]  
- [Git]
- [Visual Studio Code](recommandé)  

### 2. Cloner le projet

git clone https://github.com/Klopes10/Cinephoria.git
cd Cinephoria

### 3. Lancer les services

docker compose up -d --build

Cette commande lance :

- php → Serveur PHP-FPM (Symfony)
- nginx → Serveur web
- postgres → Base de données relationnelle
- mongo → Base de données NoSQL
- adminer → Interface graphique pour PostgreSQL

## 🌍 Accéder aux services

docker compose ps : 
- Application Symfony : [http://localhost:8080](http://localhost:8080)  
- Adminer (PostgreSQL) : [http://localhost:8081](http://localhost:8081)  
- Mongo Express (MongoDB) : [http://localhost:8082](http://localhost:8082)  
- Mailpit (emails de test) : [http://localhost:8025](http://localhost:8025)  

# Accéder à l'application

    . Interface principale : http://localhost:8080
    . Accès à Adminer: http://localhost:8081
    . Accès MongoDB Compass : Connexion à mongodb://localhost:27017

# Ajout de données semblable au site à un temps T

docker compose exec -T database psql -U app -d app < app.sql

# Tests et Verifications
docker compose exec php bash -lc 'php bin/phpunit tests/Functional'
docker compose exec php bash -lc 'php bin/phpunit tests/Unit'

# SQL
Lancement d'un fichier de création de base de données
- docker compose exec -T database psql -U app -d app_test < db/schema.sql
Ajout de data minimal
- docker compose exec -T database psql -U app -d app_test < db/data.sql
Transaction effectuée sur l'user à l'ID n°1 pour 2 places
- docker compose exec -T database \
  psql -U app -d app_test \
  -v seance_id=1 -v user_id=1 -v qty=2 \
  < db/transaction_reservation.sql

# Page d'accueil
La page d'accueil recense les films sortis récemment. La liste est réinitialisée chaque mercredi.

# Mise à jour du contenu
La mise à jour de contenu se réalise via l'espace Administration (pour l'administrateur) ou Intranet (pour l'employé), tous deux accessibles en se connectant :
- L'employé peut ajouter, modifier ou supprimer des films, séances et cinémas.
- L'administrateur peut ajouter, modifier ou supprimer des films, séances, cinémas, genres, qualités et ajouter des comptes utilisateurs.
- Les sièges sont créés automatiquement à la création d'une séance.

Compte utilisable : 

Rôle / Identifiant / Mot de passe 

1. Client  / user@cinephoria.com / *UserTest1! 

2. Employé /employe@cinephoria.com /*EmployeTest1! 

3. Administrateur /admin@cinephoria.com /*AdminTest1! 



