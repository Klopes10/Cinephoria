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

- Application Symfony : [http://localhost:8080](http://localhost:8080)  
- Adminer (PostgreSQL) : [http://localhost:8081](http://localhost:8081)  
- Mongo Express (MongoDB) : [http://localhost:8082](http://localhost:8082)  
- Mailpit (emails de test) : [http://localhost:8025](http://localhost:8025)  

# Accéder à l'application

    . Interface principale : http://localhost:8080
    . Accès à Adminer: http://localhost:8081
    . Accès MongoDB Compass : Connexion à mongodb://localhost:27017