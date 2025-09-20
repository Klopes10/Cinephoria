-- =========================================
-- data.sql — Cinéphoria (PostgreSQL)
-- =========================================

--  Utilisateurs 
INSERT INTO "user" (id, email, roles, password, name, forname, username, create_at)
VALUES
  (1, 'alice@example.com', '["ROLE_USER"]', 'hashedpwd1', 'Dupont', 'Alice', 'alice', NOW()),
  (2, 'bob@example.com',   '["ROLE_ADMIN"]', 'hashedpwd2', 'Martin', 'Bob', 'bob', NOW());

-- ===== Cinémas =====
INSERT INTO cinema (id, nom, ville, pays, adresse, code_postal)
VALUES
  (1, 'Cinéma Lumière', 'Paris', 'France', '10 rue du Test', '75001');

-- ===== Qualités =====
INSERT INTO qualite (id, label, prix)
VALUES
  (1, 'Standard 2D', 9.00),
  (2, 'IMAX 3D', 15.00);

-- ===== Genres =====
INSERT INTO genre (id, nom)
VALUES
  (1, 'Action'),
  (2, 'Comédie');

-- ===== Salles =====
INSERT INTO salle (id, nom, nombre_places, created_at, cinema_id, qualite_id)
VALUES
  (1, 'Salle 1', 100, NOW(), 1, 1),
  (2, 'Salle 2', 80, NOW(), 1, 2);

-- ===== Films =====
INSERT INTO film (id, titre, synopsis, age_minimum, affiche, coup_de_coeur, note_moyenne, date_publication, genre_id)
VALUES
  (1, 'Film Action', 'Un film plein d’action.', 12, 'affiche1.jpg', TRUE, 4.5, NOW(), 1),
  (2, 'Film Comédie', 'Une comédie hilarante.', NULL, 'affiche2.jpg', FALSE, 3.8, NOW(), 2);

-- ===== Séances =====
INSERT INTO seance (id, date, heure_debut, heure_fin, places_disponible, created_at, film_id, salle_id, cinema_id)
VALUES
  (1, CURRENT_DATE, '18:00', '20:00', 100, NOW(), 1, 1, 1),
  (2, CURRENT_DATE, '20:30', '22:30', 80, NOW(), 2, 2, 1);

-- Sièges (quelques-uns seulement) 
INSERT INTO siege (id, seance_id, numero, code, is_pmr, is_reserved)
VALUES
  (1, 1, 1, 'A1', FALSE, FALSE),
  (2, 1, 2, 'A2', FALSE, FALSE),
  (3, 1, 3, 'A3', FALSE, FALSE),
  (4, 2, 1, 'B1', FALSE, FALSE),
  (5, 2, 2, 'B2', FALSE, FALSE);

--  Réservation 
-- Utilisateur 1 réserve 3 places pour la séance 1
INSERT INTO reservation (id, user_id, seance_id, nombre_places, created_at, prix_total)
VALUES (1, 1, 1, 3, NOW(), 27.00);

-- Association des sièges A1, A2, A3 à la réservation
INSERT INTO reservation_siege (reservation_id, siege_id) VALUES
  (1, 1),
  (1, 2),
  (1, 3);

-- Marquer les sièges comme réservés
UPDATE siege
SET is_reserved = TRUE
WHERE id IN (1, 2, 3);

--  Avis 
INSERT INTO avis (id, user_id, film_id, note_sur5, commentaire, valide, created_at)
VALUES (1, 1, 1, 4, 'Très bon film d’action !', TRUE, NOW());

-- Incident 
INSERT INTO incident (id, salle_id, titre, description, date_signalement, resolu, created_at)
VALUES (1, 1, 'Bruit projecteur', 'Un bruit intermittent lors de la projection.', NOW(), FALSE, NOW());

--  Contact 
INSERT INTO contact (id, nom_utilisateur, titre, description, date_envoi)
VALUES (1, 'alice', 'Question sur les tarifs', 'Bonjour, y a-t-il des réductions étudiant ?', NOW());


