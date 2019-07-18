-- Employees table
-- Recommend cloning from Employees_GHR



CREATE TABLE public.review_employees (
    id integer NOT NULL,
    ghr_id integer,
    full_name text,
    first_name text,
    middle_name text,
    last_name text,
    dept_name text,
    title text,
    reports_to integer,
    shift text,
    status_name text,
    reports_to_name text
);


ALTER TABLE ONLY public.review_employees
    ADD CONSTRAINT employees_pkey PRIMARY KEY (id);



-- MBO table



CREATE TABLE public.review_mbo (
    id integer NOT NULL,
    subgroup text,
    worker_title text,
    item text,
    rating text,
    weight text,
    metric text,
    num_rating integer
);

CREATE SEQUENCE public.review_mbo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.review_mbo_id_seq OWNED BY public.review_mbo.id;

ALTER TABLE ONLY public.review_mbo ALTER COLUMN id SET DEFAULT nextval('public.review_mbo_id_seq'::regclass);

ALTER TABLE ONLY public.review_mbo
    ADD CONSTRAINT review_mbo_pkey PRIMARY KEY (id);



-- Ratings table



CREATE TABLE public.review_ratings (
    id integer NOT NULL,
    ghr_id integer,
    full_name character varying(255),
    title character varying(255),
    dept_name character varying(255),
    reports_to integer,
    reports_to_name character varying(255),
    shift character varying(255),
    quarter character varying(1),
    year character varying(4),
    overall_rating character varying(255),
    shift_ranking character varying(2),
    writeup text,
    ean_items text,
    pos_watch boolean,
    neg_watch boolean,
    succession boolean,
    promo boolean,
    subgroup text,
    hidden boolean DEFAULT false,
    ean_verbal boolean,
    ean_written boolean,
    ean_final boolean,
    ean_pa boolean,
    ean_pip boolean,
    overall_ranking character varying(255),
    term_date character varying(255),
    overall_numeric_rating character varying(255)
);

CREATE SEQUENCE public.ratings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.ratings_id_seq OWNED BY public.review_ratings.id;

ALTER TABLE ONLY public.review_ratings
    ADD CONSTRAINT ratings_pk PRIMARY KEY (id);

ALTER TABLE ONLY public.review_ratings
    ADD CONSTRAINT review_ratings_full_name_quarter_year_key UNIQUE (full_name, quarter, year);