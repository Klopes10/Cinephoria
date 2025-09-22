-- =========================================
-- data.sql — Cinéphoria (jeu de données minimal)
-- Compatible avec schema.sql ci-dessus
-- =========================================

-- Users
INSERT INTO "user" (email, roles, password, name, forname, username, create_at)
VALUES
  ('alice@example.com', '["ROLE_USER"]',  'hashedpwd1', 'Dupont', 'Alice', 'alice', NOW()),
  ('bob@example.com',   '["ROLE_ADMIN"]', 'hashedpwd2', 'Martin', 'Bob',   'bob',   NOW());

-- Cinéma / Qualité / Genre
INSERT INTO cinema (nom, ville, pays, adresse, code_postal) VALUES
  ('Cinéma Lumière', 'Paris', 'France', '10 rue du Test', '75001');

INSERT INTO qualite (label, prix) VALUES
  ('Standard', 9.00),
  ('IMAX 3D',  15.00);

INSERT INTO genre (nom) VALUES ('Action'), ('Comédie');

-- Salle (qualité obligatoire)
INSERT INTO salle (cinema_id, nom, nombre_places, created_at, qualite_id)
VALUES
  (1, 'Salle 1', 100, NOW(), 1),
  (1, 'Salle 2',  80, NOW(), 2);

-- Films
INSERT INTO film (genre_id, titre, synopsis, age_minimum, affiche, coup_de_coeur, note_moyenne, date_publication)
VALUES
  (1, 'Film Action',  'Un film plein d’action.', 12,  'affiche1.jpg', TRUE,  4.5, NOW()),
  (2, 'Film Comédie', 'Une comédie hilarante.',  NULL,'affiche2.jpg', FALSE, 3.8, NOW());

-- Séances (⚠️ qualite_id requis et cohérent avec la salle choisie)
-- On met 2 séances, la #1 en Standard (qualite_id=1), la #2 en IMAX 3D (qualite_id=2)
INSERT INTO seance (film_id, salle_id, cinema_id, date, heure_debut, heure_fin, places_disponible, created_at, qualite_id)
VALUES
  (1, 1, 1, CURRENT_DATE, '18:00', '20:00', 100, NOW(), 1),
  (2, 2, 1, CURRENT_DATE, '20:30', '22:30',  80, NOW(), 2);

-- 20 sièges pour la séance 1 (A1..A20) et 20 sièges pour la séance 2 (B1..B20)
INSERT INTO siege (seance_id, numero, code, is_pmr, is_reserved)
SELECT 1, gs, 'A' || gs, FALSE, FALSE FROM generate_series(1, 20) AS gs;

INSERT INTO siege (seance_id, numero, code, is_pmr, is_reserved)
SELECT 2, gs, 'B' || gs, FALSE, FALSE FROM generate_series(1, 20) AS gs;

-- Exemple : une réservation existante sur la séance 1 (3 places)
INSERT INTO reservation (user_id, seance_id, nombre_places, created_at, prix_total)
VALUES (1, 1, 3, NOW(), 27.00);

INSERT INTO reservation_siege (reservation_id, siege_id)
SELECT currval('reservation_id_seq')::bigint, id FROM siege
WHERE seance_id = 1 AND numero IN (1,2,3);

UPDATE siege SET is_reserved = TRUE
WHERE seance_id = 1 AND numero IN (1,2,3);

-- Un avis
INSERT INTO avis (user_id, film_id, note_sur5, commentaire, valide, created_at)
VALUES (1, 1, 4, 'Très bon film d’action !', TRUE, NOW());

-- Un incident
INSERT INTO incident (salle_id, titre, description, date_signalement, resolu, created_at)
VALUES (1, 'Bruit projecteur', 'Un bruit intermittent lors de la projection.', NOW(), FALSE, NOW());
