-- =========================================
-- schema.sql — Cinéphoria (PostgreSQL)
-- =========================================

-- 1) Drop puis recrée la base
DROP DATABASE IF EXISTS app;
CREATE DATABASE app;

-- 2) Connexion à la base (psql)
\c app

-- 3) Drop des tables si elles existent (ordre inverse des dépendances)
DROP TABLE IF EXISTS reservation_siege CASCADE;
DROP TABLE IF EXISTS avis CASCADE;
DROP TABLE IF EXISTS incident CASCADE;
DROP TABLE IF EXISTS reservation CASCADE;
DROP TABLE IF EXISTS siege CASCADE;
DROP TABLE IF EXISTS seance CASCADE;
DROP TABLE IF EXISTS salle CASCADE;
DROP TABLE IF EXISTS film CASCADE;
DROP TABLE IF EXISTS qualite CASCADE;
DROP TABLE IF EXISTS genre CASCADE;
DROP TABLE IF EXISTS contact CASCADE;
DROP TABLE IF EXISTS cinema CASCADE;
DROP TABLE IF EXISTS "user" CASCADE;


-- Table: "user"

CREATE TABLE "user" (
  id                        BIGSERIAL PRIMARY KEY,
  email                     VARCHAR(180) NOT NULL UNIQUE,
  roles                     JSON NOT NULL,             
  password                  VARCHAR(255) NOT NULL,
  name                      VARCHAR(255) NOT NULL,
  forname                   VARCHAR(255) NOT NULL,
  username                  VARCHAR(255) NOT NULL,
  create_at                 TIMESTAMP NOT NULL,        
  reset_password_token      VARCHAR(100),
  reset_password_expires_at TIMESTAMP
);



-- Table: cinema

CREATE TABLE cinema (
  id          BIGSERIAL PRIMARY KEY,
  nom         VARCHAR(255) NOT NULL,
  ville       VARCHAR(255) NOT NULL,
  pays        VARCHAR(255) NOT NULL,
  adresse     VARCHAR(255) NOT NULL,
  code_postal VARCHAR(255) NOT NULL
);



-- Table: contact

CREATE TABLE contact (
  id              BIGSERIAL PRIMARY KEY,
  nom_utilisateur VARCHAR(255),
  titre           VARCHAR(255) NOT NULL,
  description     TEXT NOT NULL,
  date_envoi      TIMESTAMP NOT NULL
);

-- Table: genre

CREATE TABLE genre (
  id  BIGSERIAL PRIMARY KEY,
  nom VARCHAR(255) NOT NULL
);



-- Table: qualite

CREATE TABLE qualite (
  id    BIGSERIAL PRIMARY KEY,
  label VARCHAR(255) NOT NULL,
  prix  NUMERIC(6,2) NOT NULL
);



-- Table: film

CREATE TABLE film (
  id               BIGSERIAL PRIMARY KEY,
  genre_id         BIGINT NOT NULL,
  titre            VARCHAR(255) NOT NULL,
  synopsis         TEXT NOT NULL,
  age_minimum      INTEGER,
  affiche          VARCHAR(255),
  coup_de_coeur    BOOLEAN NOT NULL,
  note_moyenne     DOUBLE PRECISION,
  date_publication TIMESTAMP NOT NULL,
  CONSTRAINT fk_film_genre FOREIGN KEY (genre_id) REFERENCES genre(id) ON DELETE NO ACTION
);
CREATE INDEX genre_id_idx ON film(genre_id);


-- Table: salle

CREATE TABLE salle (
  id            BIGSERIAL PRIMARY KEY,
  cinema_id     BIGINT NOT NULL,
  nom           VARCHAR(255) NOT NULL,
  nombre_places INTEGER NOT NULL,
  created_at    TIMESTAMP NOT NULL,
  qualite_id    BIGINT NOT NULL,
  CONSTRAINT fk_salle_cinema  FOREIGN KEY (cinema_id) REFERENCES cinema(id) ON DELETE NO ACTION,
  CONSTRAINT fk_salle_qualite FOREIGN KEY (qualite_id) REFERENCES qualite(id) ON DELETE NO ACTION
);
CREATE INDEX cinema_id_idx ON salle(cinema_id);
CREATE INDEX qualite_id_idx ON salle(qualite_id);


-- Table: seance

CREATE TABLE seance (
  id                BIGSERIAL PRIMARY KEY,
  film_id           BIGINT NOT NULL,
  salle_id          BIGINT NOT NULL,
  cinema_id         BIGINT NOT NULL,
  date              DATE NOT NULL,
  heure_debut       TIME NOT NULL,
  heure_fin         TIME NOT NULL,
  places_disponible INTEGER NOT NULL,
  created_at        TIMESTAMP NOT NULL,
  CONSTRAINT fk_seance_film   FOREIGN KEY (film_id)   REFERENCES film(id)   ON DELETE NO ACTION,
  CONSTRAINT fk_seance_salle  FOREIGN KEY (salle_id)  REFERENCES salle(id)  ON DELETE NO ACTION,
  CONSTRAINT fk_seance_cinema FOREIGN KEY (cinema_id) REFERENCES cinema(id) ON DELETE NO ACTION
);
CREATE INDEX film_id_idx  ON seance(film_id);
CREATE INDEX salle_id_idx ON seance(salle_id);
CREATE INDEX cinema_id_idx ON seance(cinema_id);


-- Table: siege

CREATE TABLE siege (
  id          BIGSERIAL PRIMARY KEY,
  seance_id   BIGINT NOT NULL,
  numero      INTEGER NOT NULL,
  is_pmr      BOOLEAN NOT NULL,
  is_reserved BOOLEAN NOT NULL,
  code        VARCHAR(10) NOT NULL,
  CONSTRAINT fk_siege_seance FOREIGN KEY (seance_id) REFERENCES seance(id) ON DELETE CASCADE
);
CREATE INDEX seance_id_idx ON siege(seance_id);

-- Table: incident
CREATE TABLE incident (
  id               BIGSERIAL PRIMARY KEY,
  salle_id         BIGINT NOT NULL,
  description      TEXT,
  date_signalement TIMESTAMP NOT NULL,
  resolu           BOOLEAN NOT NULL,
  created_at       TIMESTAMP NOT NULL,
  titre            VARCHAR(255) NOT NULL,
  CONSTRAINT fk_incident_salle FOREIGN KEY (salle_id) REFERENCES salle(id) ON DELETE NO ACTION
);

-- Index (nom unique pour éviter les collisions)
CREATE INDEX idx_incident_salle_id ON incident(salle_id);



-- Table: reservation

CREATE TABLE reservation (
  id             BIGSERIAL PRIMARY KEY,
  user_id        BIGINT NOT NULL,
  seance_id      BIGINT NOT NULL,
  nombre_places  INTEGER NOT NULL,
  created_at     TIMESTAMP NOT NULL,
  prix_total     DOUBLE PRECISION NOT NULL,
  CONSTRAINT fk_resa_user  FOREIGN KEY (user_id)  REFERENCES "user"(id) ON DELETE NO ACTION,
  CONSTRAINT fk_resa_seance FOREIGN KEY (seance_id) REFERENCES seance(id) ON DELETE NO ACTION
);
CREATE INDEX user_id_idx   ON reservation(user_id);
CREATE INDEX seance_id_idx ON reservation(seance_id);


-- Table: reservation_siege (pivot)

CREATE TABLE reservation_siege (
  reservation_id BIGINT NOT NULL,
  siege_id       BIGINT NOT NULL,
  PRIMARY KEY (reservation_id, siege_id),
  CONSTRAINT fk_rs_resa  FOREIGN KEY (reservation_id) REFERENCES reservation(id) ON DELETE CASCADE,
  CONSTRAINT fk_rs_siege FOREIGN KEY (siege_id)       REFERENCES siege(id)       ON DELETE CASCADE
);
CREATE INDEX reservation_id_idx ON reservation_siege(reservation_id);
CREATE INDEX siege_id_idx       ON reservation_siege(siege_id);


-- Table: avis

CREATE TABLE avis (
  id          BIGSERIAL PRIMARY KEY,
  user_id     BIGINT NOT NULL,
  film_id     BIGINT NOT NULL,
  note_sur5   INTEGER NOT NULL,
  commentaire TEXT,
  valide      BOOLEAN NOT NULL,
  created_at  TIMESTAMP NOT NULL,
  CONSTRAINT fk_avis_user FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE NO ACTION,
  CONSTRAINT fk_avis_film FOREIGN KEY (film_id) REFERENCES film(id) ON DELETE NO ACTION
);
CREATE INDEX user_id_idx ON avis(user_id);
CREATE INDEX film_id_idx ON avis(film_id);
