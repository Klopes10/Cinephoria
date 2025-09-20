--
-- PostgreSQL database dump
--

-- Dumped from database version 16.9
-- Dumped by pg_dump version 16.9

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: avis; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.avis (
    id integer NOT NULL,
    user_id integer NOT NULL,
    film_id integer NOT NULL,
    note_sur5 integer NOT NULL,
    commentaire text,
    valide boolean NOT NULL,
    created_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.avis OWNER TO app;

--
-- Name: COLUMN avis.created_at; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.avis.created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: avis_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.avis_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.avis_id_seq OWNER TO app;

--
-- Name: avis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.avis_id_seq OWNED BY public.avis.id;


--
-- Name: cinema; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.cinema (
    id integer NOT NULL,
    nom character varying(255) NOT NULL,
    ville character varying(255) NOT NULL,
    pays character varying(255) NOT NULL,
    adresse character varying(255) NOT NULL,
    code_postal character varying(255) NOT NULL
);


ALTER TABLE public.cinema OWNER TO app;

--
-- Name: cinema_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.cinema_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cinema_id_seq OWNER TO app;

--
-- Name: cinema_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.cinema_id_seq OWNED BY public.cinema.id;


--
-- Name: contact; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.contact (
    id integer NOT NULL,
    nom_utilisateur character varying(255) NOT NULL,
    titre character varying(255) NOT NULL,
    description text NOT NULL,
    date_envoi timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.contact OWNER TO app;

--
-- Name: COLUMN contact.date_envoi; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.contact.date_envoi IS '(DC2Type:datetime_immutable)';


--
-- Name: contact_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.contact_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contact_id_seq OWNER TO app;

--
-- Name: contact_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.contact_id_seq OWNED BY public.contact.id;


--
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.doctrine_migration_versions (
    version character varying(191) NOT NULL,
    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    execution_time integer
);


ALTER TABLE public.doctrine_migration_versions OWNER TO app;

--
-- Name: film; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.film (
    id integer NOT NULL,
    genre_id integer NOT NULL,
    titre character varying(255) NOT NULL,
    synopsis text NOT NULL,
    age_minimum integer,
    affiche character varying(255) NOT NULL,
    coup_de_coeur boolean NOT NULL,
    note_moyenne double precision,
    created_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.film OWNER TO app;

--
-- Name: COLUMN film.created_at; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.film.created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: film_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.film_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.film_id_seq OWNER TO app;

--
-- Name: film_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.film_id_seq OWNED BY public.film.id;


--
-- Name: genre; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.genre (
    id integer NOT NULL,
    nom character varying(255) NOT NULL
);


ALTER TABLE public.genre OWNER TO app;

--
-- Name: genre_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.genre_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.genre_id_seq OWNER TO app;

--
-- Name: genre_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.genre_id_seq OWNED BY public.genre.id;


--
-- Name: incident; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.incident (
    id integer NOT NULL,
    salle_id integer NOT NULL,
    description text,
    date_signalement timestamp(0) without time zone NOT NULL,
    resolu boolean NOT NULL,
    created_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.incident OWNER TO app;

--
-- Name: COLUMN incident.date_signalement; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.incident.date_signalement IS '(DC2Type:datetime_immutable)';


--
-- Name: COLUMN incident.created_at; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.incident.created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: incident_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.incident_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.incident_id_seq OWNER TO app;

--
-- Name: incident_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.incident_id_seq OWNED BY public.incident.id;


--
-- Name: reservation; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.reservation (
    id integer NOT NULL,
    user_id integer NOT NULL,
    seance_id integer NOT NULL,
    nombre_places integer NOT NULL,
    created_at timestamp(0) without time zone NOT NULL,
    prix_total double precision NOT NULL
);


ALTER TABLE public.reservation OWNER TO app;

--
-- Name: COLUMN reservation.created_at; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.reservation.created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: reservation_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.reservation_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reservation_id_seq OWNER TO app;

--
-- Name: reservation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.reservation_id_seq OWNED BY public.reservation.id;


--
-- Name: reservation_siege; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.reservation_siege (
    reservation_id integer NOT NULL,
    siege_id integer NOT NULL
);


ALTER TABLE public.reservation_siege OWNER TO app;

--
-- Name: salle; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.salle (
    id integer NOT NULL,
    cinema_id integer NOT NULL,
    nom character varying(255) NOT NULL,
    nombre_places integer NOT NULL,
    qualite character varying(255) NOT NULL,
    created_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.salle OWNER TO app;

--
-- Name: COLUMN salle.created_at; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.salle.created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: salle_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.salle_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.salle_id_seq OWNER TO app;

--
-- Name: salle_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.salle_id_seq OWNED BY public.salle.id;


--
-- Name: seance; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.seance (
    id integer NOT NULL,
    film_id integer NOT NULL,
    salle_id integer NOT NULL,
    cinema_id integer NOT NULL,
    date date NOT NULL,
    heure_debut time(0) without time zone NOT NULL,
    heure_fin time(0) without time zone NOT NULL,
    qualite character varying(255) NOT NULL,
    places_disponible integer NOT NULL,
    created_at timestamp(0) without time zone NOT NULL,
    prix double precision NOT NULL
);


ALTER TABLE public.seance OWNER TO app;

--
-- Name: COLUMN seance.date; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.seance.date IS '(DC2Type:date_immutable)';


--
-- Name: COLUMN seance.heure_debut; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.seance.heure_debut IS '(DC2Type:time_immutable)';


--
-- Name: COLUMN seance.heure_fin; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.seance.heure_fin IS '(DC2Type:time_immutable)';


--
-- Name: COLUMN seance.created_at; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public.seance.created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: seance_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.seance_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seance_id_seq OWNER TO app;

--
-- Name: seance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.seance_id_seq OWNED BY public.seance.id;


--
-- Name: siege; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public.siege (
    id integer NOT NULL,
    seance_id integer NOT NULL,
    numero integer NOT NULL,
    is_pmr boolean DEFAULT false NOT NULL,
    is_reserved boolean DEFAULT false NOT NULL
);


ALTER TABLE public.siege OWNER TO app;

--
-- Name: siege_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.siege_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.siege_id_seq OWNER TO app;

--
-- Name: siege_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.siege_id_seq OWNED BY public.siege.id;


--
-- Name: user; Type: TABLE; Schema: public; Owner: app
--

CREATE TABLE public."user" (
    id integer NOT NULL,
    email character varying(180) NOT NULL,
    roles json NOT NULL,
    password character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    forname character varying(255) NOT NULL,
    username character varying(255) NOT NULL,
    create_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public."user" OWNER TO app;

--
-- Name: COLUMN "user".create_at; Type: COMMENT; Schema: public; Owner: app
--

COMMENT ON COLUMN public."user".create_at IS '(DC2Type:datetime_immutable)';


--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: app
--

CREATE SEQUENCE public.user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_id_seq OWNER TO app;

--
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: app
--

ALTER SEQUENCE public.user_id_seq OWNED BY public."user".id;


--
-- Name: avis id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.avis ALTER COLUMN id SET DEFAULT nextval('public.avis_id_seq'::regclass);


--
-- Name: cinema id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.cinema ALTER COLUMN id SET DEFAULT nextval('public.cinema_id_seq'::regclass);


--
-- Name: contact id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.contact ALTER COLUMN id SET DEFAULT nextval('public.contact_id_seq'::regclass);


--
-- Name: film id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.film ALTER COLUMN id SET DEFAULT nextval('public.film_id_seq'::regclass);


--
-- Name: genre id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.genre ALTER COLUMN id SET DEFAULT nextval('public.genre_id_seq'::regclass);


--
-- Name: incident id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.incident ALTER COLUMN id SET DEFAULT nextval('public.incident_id_seq'::regclass);


--
-- Name: reservation id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.reservation ALTER COLUMN id SET DEFAULT nextval('public.reservation_id_seq'::regclass);


--
-- Name: salle id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.salle ALTER COLUMN id SET DEFAULT nextval('public.salle_id_seq'::regclass);


--
-- Name: seance id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.seance ALTER COLUMN id SET DEFAULT nextval('public.seance_id_seq'::regclass);


--
-- Name: siege id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.siege ALTER COLUMN id SET DEFAULT nextval('public.siege_id_seq'::regclass);


--
-- Name: user id; Type: DEFAULT; Schema: public; Owner: app
--

ALTER TABLE ONLY public."user" ALTER COLUMN id SET DEFAULT nextval('public.user_id_seq'::regclass);


--
-- Data for Name: avis; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.avis (id, user_id, film_id, note_sur5, commentaire, valide, created_at) FROM stdin;
\.


--
-- Data for Name: cinema; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.cinema (id, nom, ville, pays, adresse, code_postal) FROM stdin;
1	Cinéphoria - Strasbourg	Strasbourg	France	1 rue de la paix	67000
2	Cinéphoria - Charleroi	Charleroi	Belgique	10 rue des canards	6000
3	Cinéphoria - Liege	Liege	Belgique	20 rue des oursins	4020
4	Cinéphoria - Bordeaux	Bordeaux	France	10 rue de la loutres	33000
5	Cinéphoria - Lille	Lille	France	10 rue des peintres	59000
6	Cinéphoria - Nantes	Nantes	France	10 rue de la tulipe	44000
7	Cinéphoria - Toulouse	Toulouse	France	10 rue de la rose	31000
8	Cinéphoria - Paris	Paris	France	10 rue de la Tour Eiffel	75000
\.


--
-- Data for Name: contact; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.contact (id, nom_utilisateur, titre, description, date_envoi) FROM stdin;
\.


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
DoctrineMigrations\\Version20250730133307	2025-07-31 14:25:57	615
\.


--
-- Data for Name: film; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.film (id, genre_id, titre, synopsis, age_minimum, affiche, coup_de_coeur, note_moyenne, created_at) FROM stdin;
1	1	Amazing Spiderman 2	Ce n’est un secret pour personne que le combat le plus rude de Spider-Man est celui qu’il mène contre lui-même en tentant de concilier la vie quotidienne de Peter Parker et les lourdes responsabilités de Spider-Man. Mais Peter Parker va se rendre compte qu’il fait face à un conflit de bien plus grande ampleur. Être Spider-Man, quoi de plus grisant ? Peter Parker trouve son bonheur entre sa vie de héros, bondissant d’un gratte-ciel à l’autre, et les doux moments passés aux côté de Gwen. Mais être Spider-Man a un prix : il est le seul à pouvoir protéger ses concitoyens new-yorkais des abominables méchants qui menacent la ville.  Face à Electro, Peter devra affronter un ennemi nettement plus puissant que lui.  Au retour de son vieil ami Harry Osborn, il se rend compte que tous ses ennemis ont un point commun : OsCorp.	\N	spiderman-1754231311.png	t	\N	2025-07-31 14:46:16
2	1	Deadpool 3	Après avoir échoué à rejoindre l’équipe des Avengers, Wade Wilson passe d’un petit boulot à un autre sans vraiment trouver sa voie. Jusqu’au jour où un haut gradé du Tribunal des Variations Anachroniques lui propose une mission digne de lui… à condition de voir son monde et tous ceux qu’il aime être anéantis.\r\n\r\nRefusant catégoriquement, Wade endosse de nouveau le costume de Deadpool et tente de convaincre Wolverine de l’aider à sauver son univers…	12	deadpool3-1754491972.jpg	t	\N	2025-08-06 14:52:45
\.


--
-- Data for Name: genre; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.genre (id, nom) FROM stdin;
1	Action
\.


--
-- Data for Name: incident; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.incident (id, salle_id, description, date_signalement, resolu, created_at) FROM stdin;
\.


--
-- Data for Name: reservation; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.reservation (id, user_id, seance_id, nombre_places, created_at, prix_total) FROM stdin;
1	1	1	2	2025-07-31 14:50:38	15
\.


--
-- Data for Name: reservation_siege; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.reservation_siege (reservation_id, siege_id) FROM stdin;
\.


--
-- Data for Name: salle; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.salle (id, cinema_id, nom, nombre_places, qualite, created_at) FROM stdin;
1	1	Salle 1	20	IMAX	2025-07-31 14:46:44
\.


--
-- Data for Name: seance; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.seance (id, film_id, salle_id, cinema_id, date, heure_debut, heure_fin, qualite, places_disponible, created_at, prix) FROM stdin;
1	1	1	1	2025-08-03	10:20:00	12:00:00	IMAX	20	2025-07-31 14:49:53	1180
2	1	1	1	2025-08-05	18:00:00	20:00:00	IMAX	20	2025-08-04 14:43:31	1200
3	2	1	1	2025-08-30	10:00:00	12:00:00	IMAX	20	2025-08-06 14:59:08	1200
\.


--
-- Data for Name: siege; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.siege (id, seance_id, numero, is_pmr, is_reserved) FROM stdin;
1	1	1	f	f
2	1	2	f	f
3	1	3	f	f
4	1	4	f	f
5	1	5	f	f
6	1	6	f	f
7	1	7	f	f
8	1	8	f	f
9	1	9	f	f
10	1	10	t	f
11	1	11	f	f
12	1	12	f	f
13	1	13	f	f
14	1	14	f	f
15	1	15	f	f
16	1	16	f	f
17	1	17	f	f
18	1	18	f	f
19	1	19	f	f
20	1	20	t	f
21	2	1	f	f
22	2	2	f	f
23	2	3	f	f
24	2	4	f	f
25	2	5	f	f
26	2	6	f	f
27	2	7	f	f
28	2	8	f	f
29	2	9	f	f
30	2	10	t	f
31	2	11	f	f
32	2	12	f	f
33	2	13	f	f
34	2	14	f	f
35	2	15	f	f
36	2	16	f	f
37	2	17	f	f
38	2	18	f	f
39	2	19	f	f
40	2	20	t	f
41	3	1	f	f
42	3	2	f	f
43	3	3	f	f
44	3	4	f	f
45	3	5	f	f
46	3	6	f	f
47	3	7	f	f
48	3	8	f	f
49	3	9	f	f
50	3	10	t	f
51	3	11	f	f
52	3	12	f	f
53	3	13	f	f
54	3	14	f	f
55	3	15	f	f
56	3	16	f	f
57	3	17	f	f
58	3	18	f	f
59	3	19	f	f
60	3	20	t	f
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public."user" (id, email, roles, password, name, forname, username, create_at) FROM stdin;
1	Kev7@live.fr	["ROLE_SUPER_ADMIN"]	$2y$13$Rzv9lCIpdBoxiKs7jVIVCOmmEdJgRhRkBHJr4il9NZ8u9CCdGnvYC	Lopes	Kevin	Kazuto	2025-07-31 14:27:47
\.


--
-- Name: avis_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.avis_id_seq', 1, false);


--
-- Name: cinema_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.cinema_id_seq', 8, true);


--
-- Name: contact_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.contact_id_seq', 1, false);


--
-- Name: film_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.film_id_seq', 2, true);


--
-- Name: genre_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.genre_id_seq', 1, true);


--
-- Name: incident_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.incident_id_seq', 1, false);


--
-- Name: reservation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.reservation_id_seq', 1, true);


--
-- Name: salle_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.salle_id_seq', 1, true);


--
-- Name: seance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.seance_id_seq', 3, true);


--
-- Name: siege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.siege_id_seq', 60, true);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.user_id_seq', 1, true);


--
-- Name: avis avis_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.avis
    ADD CONSTRAINT avis_pkey PRIMARY KEY (id);


--
-- Name: cinema cinema_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.cinema
    ADD CONSTRAINT cinema_pkey PRIMARY KEY (id);


--
-- Name: contact contact_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.contact
    ADD CONSTRAINT contact_pkey PRIMARY KEY (id);


--
-- Name: doctrine_migration_versions doctrine_migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.doctrine_migration_versions
    ADD CONSTRAINT doctrine_migration_versions_pkey PRIMARY KEY (version);


--
-- Name: film film_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.film
    ADD CONSTRAINT film_pkey PRIMARY KEY (id);


--
-- Name: genre genre_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.genre
    ADD CONSTRAINT genre_pkey PRIMARY KEY (id);


--
-- Name: incident incident_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.incident
    ADD CONSTRAINT incident_pkey PRIMARY KEY (id);


--
-- Name: reservation reservation_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.reservation
    ADD CONSTRAINT reservation_pkey PRIMARY KEY (id);


--
-- Name: reservation_siege reservation_siege_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.reservation_siege
    ADD CONSTRAINT reservation_siege_pkey PRIMARY KEY (reservation_id, siege_id);


--
-- Name: salle salle_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.salle
    ADD CONSTRAINT salle_pkey PRIMARY KEY (id);


--
-- Name: seance seance_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.seance
    ADD CONSTRAINT seance_pkey PRIMARY KEY (id);


--
-- Name: siege siege_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.siege
    ADD CONSTRAINT siege_pkey PRIMARY KEY (id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: idx_24796450b83297e7; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_24796450b83297e7 ON public.reservation_siege USING btree (reservation_id);


--
-- Name: idx_24796450bf006e8b; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_24796450bf006e8b ON public.reservation_siege USING btree (siege_id);


--
-- Name: idx_3d03a11adc304035; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_3d03a11adc304035 ON public.incident USING btree (salle_id);


--
-- Name: idx_42c84955a76ed395; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_42c84955a76ed395 ON public.reservation USING btree (user_id);


--
-- Name: idx_42c84955e3797a94; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_42c84955e3797a94 ON public.reservation USING btree (seance_id);


--
-- Name: idx_4e977e5cb4cb84b6; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_4e977e5cb4cb84b6 ON public.salle USING btree (cinema_id);


--
-- Name: idx_6706b4f7e3797a94; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_6706b4f7e3797a94 ON public.siege USING btree (seance_id);


--
-- Name: idx_8244be224296d31f; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_8244be224296d31f ON public.film USING btree (genre_id);


--
-- Name: idx_8f91abf0567f5183; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_8f91abf0567f5183 ON public.avis USING btree (film_id);


--
-- Name: idx_8f91abf0a76ed395; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_8f91abf0a76ed395 ON public.avis USING btree (user_id);


--
-- Name: idx_df7dfd0e567f5183; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_df7dfd0e567f5183 ON public.seance USING btree (film_id);


--
-- Name: idx_df7dfd0eb4cb84b6; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_df7dfd0eb4cb84b6 ON public.seance USING btree (cinema_id);


--
-- Name: idx_df7dfd0edc304035; Type: INDEX; Schema: public; Owner: app
--

CREATE INDEX idx_df7dfd0edc304035 ON public.seance USING btree (salle_id);


--
-- Name: uniq_identifier_email; Type: INDEX; Schema: public; Owner: app
--

CREATE UNIQUE INDEX uniq_identifier_email ON public."user" USING btree (email);


--
-- Name: reservation_siege fk_24796450b83297e7; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.reservation_siege
    ADD CONSTRAINT fk_24796450b83297e7 FOREIGN KEY (reservation_id) REFERENCES public.reservation(id) ON DELETE CASCADE;


--
-- Name: reservation_siege fk_24796450bf006e8b; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.reservation_siege
    ADD CONSTRAINT fk_24796450bf006e8b FOREIGN KEY (siege_id) REFERENCES public.siege(id) ON DELETE CASCADE;


--
-- Name: incident fk_3d03a11adc304035; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.incident
    ADD CONSTRAINT fk_3d03a11adc304035 FOREIGN KEY (salle_id) REFERENCES public.salle(id);


--
-- Name: reservation fk_42c84955a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.reservation
    ADD CONSTRAINT fk_42c84955a76ed395 FOREIGN KEY (user_id) REFERENCES public."user"(id);


--
-- Name: reservation fk_42c84955e3797a94; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.reservation
    ADD CONSTRAINT fk_42c84955e3797a94 FOREIGN KEY (seance_id) REFERENCES public.seance(id);


--
-- Name: salle fk_4e977e5cb4cb84b6; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.salle
    ADD CONSTRAINT fk_4e977e5cb4cb84b6 FOREIGN KEY (cinema_id) REFERENCES public.cinema(id);


--
-- Name: siege fk_6706b4f7e3797a94; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.siege
    ADD CONSTRAINT fk_6706b4f7e3797a94 FOREIGN KEY (seance_id) REFERENCES public.seance(id);


--
-- Name: film fk_8244be224296d31f; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.film
    ADD CONSTRAINT fk_8244be224296d31f FOREIGN KEY (genre_id) REFERENCES public.genre(id);


--
-- Name: avis fk_8f91abf0567f5183; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.avis
    ADD CONSTRAINT fk_8f91abf0567f5183 FOREIGN KEY (film_id) REFERENCES public.film(id);


--
-- Name: avis fk_8f91abf0a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.avis
    ADD CONSTRAINT fk_8f91abf0a76ed395 FOREIGN KEY (user_id) REFERENCES public."user"(id);


--
-- Name: seance fk_df7dfd0e567f5183; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.seance
    ADD CONSTRAINT fk_df7dfd0e567f5183 FOREIGN KEY (film_id) REFERENCES public.film(id);


--
-- Name: seance fk_df7dfd0eb4cb84b6; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.seance
    ADD CONSTRAINT fk_df7dfd0eb4cb84b6 FOREIGN KEY (cinema_id) REFERENCES public.cinema(id);


--
-- Name: seance fk_df7dfd0edc304035; Type: FK CONSTRAINT; Schema: public; Owner: app
--

ALTER TABLE ONLY public.seance
    ADD CONSTRAINT fk_df7dfd0edc304035 FOREIGN KEY (salle_id) REFERENCES public.salle(id);


--
-- PostgreSQL database dump complete
--

