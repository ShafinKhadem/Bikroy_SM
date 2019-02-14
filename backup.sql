--
-- PostgreSQL database dump
--

-- Dumped from database version 10.6
-- Dumped by pg_dump version 10.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: report_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.report_type AS ENUM (
    'spam',
    'unavailable',
    'fraud',
    'duplicate',
    'wrong category',
    'other'
);


ALTER TYPE public.report_type OWNER TO postgres;

--
-- Name: approve_trigger(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.approve_trigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	msg varchar;
	price int;
BEGIN
	msg='ur ad with id '||new.ad_id||' has been approved by '||new.approver_mail;
	perform send_message('bikroy.com', new.poster_mail, msg);
	return new;
END
$$;


ALTER FUNCTION public.approve_trigger() OWNER TO postgres;

--
-- Name: check_ad_category(integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_ad_category(_ad_id integer, _category character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	select "count"(*) into cnt from "public".ads where ad_id=_ad_id and category=_category;
	if cnt=0 then
		return 'f';
	else
		return 't';
	end if;
end; $$;


ALTER FUNCTION public.check_ad_category(_ad_id integer, _category character varying) OWNER TO postgres;

--
-- Name: check_ad_type(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_ad_type(_ad_id integer, _category character varying, _subcategory character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	select "count"(*) into cnt from "public".ads where ad_id=_ad_id and category=_category and subcategory=_subcategory;
	if cnt=0 then
		return 'f';
	else
		return 't';
	end if;
end; $$;


ALTER FUNCTION public.check_ad_type(_ad_id integer, _category character varying, _subcategory character varying) OWNER TO postgres;

--
-- Name: check_edit_access(integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_edit_access(_ad_id integer, usermail character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	if is_admin(usermail) then
		return 't';
	end if;
	select "count"(*) into cnt from ads where ad_id=_ad_id and poster_mail=usermail;
	if cnt=0 then
		return 'f';
	else
		return 't';
	end if;
end; $$;


ALTER FUNCTION public.check_edit_access(_ad_id integer, usermail character varying) OWNER TO postgres;

--
-- Name: ad_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ad_id_seq OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: ads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ads (
    ad_id integer DEFAULT nextval('public.ad_id_seq'::regclass) NOT NULL,
    buy_or_sell boolean NOT NULL,
    poster_phone character varying(15),
    price integer,
    is_negotiable boolean,
    title character varying(255) NOT NULL,
    details character varying(1000),
    category character varying(255) NOT NULL,
    subcategory character varying(255),
    location character varying(255) NOT NULL,
    sublocation character varying(255) NOT NULL,
    poster_mail character varying(255) NOT NULL,
    approver_mail character varying(255),
    "time" timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.ads OWNER TO postgres;

--
-- Name: edit_ad(public.ads); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_ad(_ad public.ads) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    update ads set(ad_id, buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, "location", sublocation, poster_mail, approver_mail, "time") =
    (_ad.ad_id, _ad.buy_or_sell, _ad.poster_phone, _ad.price, _ad.is_negotiable, _ad.title, _ad.details, _ad.category, _ad.subcategory, _ad."location", _ad.sublocation, _ad.poster_mail, _ad.approver_mail, _ad."time") where ad_id=_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_ad(_ad public.ads) OWNER TO postgres;

--
-- Name: car_ads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.car_ads (
    brand character varying(255),
    model character varying(255),
    edition character varying(255),
    model_year character varying(255),
    condition character varying(255),
    transmission character varying(255),
    body_type character varying(255),
    fuel_type character varying(255),
    engine_capacity integer,
    kilometers_run real,
    ad_id integer NOT NULL,
    CONSTRAINT is_car_ad CHECK ((public.check_ad_type(ad_id, 'vehicle'::character varying, 'car'::character varying) = true))
);


ALTER TABLE public.car_ads OWNER TO postgres;

--
-- Name: car_ads_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.car_ads_view AS
 SELECT l.ad_id,
    l.buy_or_sell,
    l.poster_phone,
    l.price,
    l.is_negotiable,
    l.title,
    l.details,
    l.category,
    l.subcategory,
    l.location,
    l.sublocation,
    l.poster_mail,
    l.approver_mail,
    l."time",
    r.brand,
    r.model,
    r.edition,
    r.model_year,
    r.condition,
    r.transmission,
    r.body_type,
    r.fuel_type,
    r.engine_capacity,
    r.kilometers_run
   FROM (( SELECT ads.ad_id,
            ads.buy_or_sell,
            ads.poster_phone,
            ads.price,
            ads.is_negotiable,
            ads.title,
            ads.details,
            ads.category,
            ads.subcategory,
            ads.location,
            ads.sublocation,
            ads.poster_mail,
            ads.approver_mail,
            ads."time"
           FROM public.ads
          WHERE (((ads.category)::text = 'vehicle'::text) AND ((ads.subcategory)::text = 'car'::text))) l
     LEFT JOIN public.car_ads r ON ((l.ad_id = r.ad_id)));


ALTER TABLE public.car_ads_view OWNER TO postgres;

--
-- Name: edit_car_ad(public.car_ads_view); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_car_ad(_ad public.car_ads_view) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    perform edit_ad(row(_ad.ad_id, _ad.buy_or_sell, _ad.poster_phone, _ad.price, _ad.is_negotiable, _ad.title, _ad.details, _ad.category, _ad.subcategory, _ad."location", _ad.sublocation, _ad.poster_mail, _ad.approver_mail, _ad."time"));
    update car_ads set(brand, model, edition, model_year, "condition", transmission, body_type, fuel_type, engine_capacity, kilometers_run) =
    (_ad.brand, _ad.model, _ad.edition, _ad.model_year, _ad."condition", _ad.transmission, _ad.body_type, _ad.fuel_type, _ad.engine_capacity, _ad.kilometers_run) where ad_id=_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_car_ad(_ad public.car_ads_view) OWNER TO postgres;

--
-- Name: electronics_ads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.electronics_ads (
    brand character varying(255),
    model character varying(255),
    ad_id integer NOT NULL,
    CONSTRAINT is_electronics_ad CHECK ((public.check_ad_category(ad_id, 'electronics'::character varying) = true))
);


ALTER TABLE public.electronics_ads OWNER TO postgres;

--
-- Name: electronics_ads_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.electronics_ads_view AS
 SELECT l.ad_id,
    l.buy_or_sell,
    l.poster_phone,
    l.price,
    l.is_negotiable,
    l.title,
    l.details,
    l.category,
    l.subcategory,
    l.location,
    l.sublocation,
    l.poster_mail,
    l.approver_mail,
    l."time",
    r.brand,
    r.model
   FROM (( SELECT ads.ad_id,
            ads.buy_or_sell,
            ads.poster_phone,
            ads.price,
            ads.is_negotiable,
            ads.title,
            ads.details,
            ads.category,
            ads.subcategory,
            ads.location,
            ads.sublocation,
            ads.poster_mail,
            ads.approver_mail,
            ads."time"
           FROM public.ads
          WHERE ((ads.category)::text = 'electronics'::text)) l
     LEFT JOIN public.electronics_ads r ON ((l.ad_id = r.ad_id)));


ALTER TABLE public.electronics_ads_view OWNER TO postgres;

--
-- Name: edit_electronics_ad(public.electronics_ads_view); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_electronics_ad(_ad public.electronics_ads_view) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    perform edit_ad(row(_ad.ad_id, _ad.buy_or_sell, _ad.poster_phone, _ad.price, _ad.is_negotiable, _ad.title, _ad.details, _ad.category, _ad.subcategory, _ad."location", _ad.sublocation, _ad.poster_mail, _ad.approver_mail, _ad."time"));
    update electronics_ads set(brand, model) =
    (_ad.brand, _ad.model) where ad_id=_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_electronics_ad(_ad public.electronics_ads_view) OWNER TO postgres;

--
-- Name: mobile_phone_ads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mobile_phone_ads (
    brand character varying(255),
    model character varying(255),
    edition character varying(255),
    features character varying(1000),
    authenticity character varying(255),
    condition character varying(255),
    ad_id integer NOT NULL,
    CONSTRAINT is_mobile_ad CHECK ((public.check_ad_type(ad_id, 'mobile'::character varying, 'mobile_phone'::character varying) = true))
);


ALTER TABLE public.mobile_phone_ads OWNER TO postgres;

--
-- Name: mobile_phone_ads_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.mobile_phone_ads_view AS
 SELECT l.ad_id,
    l.buy_or_sell,
    l.poster_phone,
    l.price,
    l.is_negotiable,
    l.title,
    l.details,
    l.category,
    l.subcategory,
    l.location,
    l.sublocation,
    l.poster_mail,
    l.approver_mail,
    l."time",
    r.brand,
    r.model,
    r.edition,
    r.features,
    r.authenticity,
    r.condition
   FROM (( SELECT ads.ad_id,
            ads.buy_or_sell,
            ads.poster_phone,
            ads.price,
            ads.is_negotiable,
            ads.title,
            ads.details,
            ads.category,
            ads.subcategory,
            ads.location,
            ads.sublocation,
            ads.poster_mail,
            ads.approver_mail,
            ads."time"
           FROM public.ads
          WHERE (((ads.category)::text = 'mobile'::text) AND ((ads.subcategory)::text = 'mobile_phone'::text))) l
     LEFT JOIN public.mobile_phone_ads r ON ((l.ad_id = r.ad_id)));


ALTER TABLE public.mobile_phone_ads_view OWNER TO postgres;

--
-- Name: edit_mobile_phone_ad(public.mobile_phone_ads_view); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_mobile_phone_ad(_ad public.mobile_phone_ads_view) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    perform edit_ad(row(_ad.ad_id, _ad.buy_or_sell, _ad.poster_phone, _ad.price,
       _ad.is_negotiable, _ad.title, _ad.details, _ad.category, _ad.subcategory, _ad."location", _ad.sublocation, _ad.poster_mail, _ad.approver_mail, _ad."time"));
    update mobile_phone_ads set(brand, model, edition, features, authenticity, "condition") =
    (_ad.brand, _ad.model, _ad.edition, _ad.features, _ad.authenticity, _ad.condition) where ad_id=_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_mobile_phone_ad(_ad public.mobile_phone_ads_view) OWNER TO postgres;

--
-- Name: motor_cycle_ads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.motor_cycle_ads (
    bike_type character varying(255),
    brand character varying(255),
    model character varying(255),
    model_year integer,
    condition character varying(255),
    engine_capacity integer,
    kilometers_run real,
    ad_id integer NOT NULL,
    CONSTRAINT is_motor_cycle_ad CHECK ((public.check_ad_type(ad_id, 'vehicle'::character varying, 'motor_cycle'::character varying) = true))
);


ALTER TABLE public.motor_cycle_ads OWNER TO postgres;

--
-- Name: motor_cycle_ads_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.motor_cycle_ads_view AS
 SELECT l.ad_id,
    l.buy_or_sell,
    l.poster_phone,
    l.price,
    l.is_negotiable,
    l.title,
    l.details,
    l.category,
    l.subcategory,
    l.location,
    l.sublocation,
    l.poster_mail,
    l.approver_mail,
    l."time",
    r.bike_type,
    r.brand,
    r.model,
    r.model_year,
    r.condition,
    r.engine_capacity,
    r.kilometers_run
   FROM (( SELECT ads.ad_id,
            ads.buy_or_sell,
            ads.poster_phone,
            ads.price,
            ads.is_negotiable,
            ads.title,
            ads.details,
            ads.category,
            ads.subcategory,
            ads.location,
            ads.sublocation,
            ads.poster_mail,
            ads.approver_mail,
            ads."time"
           FROM public.ads
          WHERE (((ads.category)::text = 'vehicle'::text) AND ((ads.subcategory)::text = 'motor_cycle'::text))) l
     LEFT JOIN public.motor_cycle_ads r ON ((l.ad_id = r.ad_id)));


ALTER TABLE public.motor_cycle_ads_view OWNER TO postgres;

--
-- Name: edit_motor_cycle_ad(public.motor_cycle_ads_view); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_motor_cycle_ad(_motor_cycle_ad public.motor_cycle_ads_view) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    perform edit_ad(row(_motor_cycle_ad.ad_id, _motor_cycle_ad.buy_or_sell, _motor_cycle_ad.poster_phone, _motor_cycle_ad.price, _motor_cycle_ad.is_negotiable, _motor_cycle_ad.title, _motor_cycle_ad.details, _motor_cycle_ad.category, _motor_cycle_ad.subcategory, _motor_cycle_ad."location", _motor_cycle_ad.sublocation, _motor_cycle_ad.poster_mail, _motor_cycle_ad.approver_mail, _motor_cycle_ad."time"));
    update motor_cycle_ads set(bike_type, brand, model, model_year, "condition", engine_capacity, kilometers_run) =
    (_motor_cycle_ad.bike_type, _motor_cycle_ad.brand, _motor_cycle_ad.model, _motor_cycle_ad.model_year, _motor_cycle_ad.condition, _motor_cycle_ad.engine_capacity, _motor_cycle_ad.kilometers_run) where ad_id=_motor_cycle_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_motor_cycle_ad(_motor_cycle_ad public.motor_cycle_ads_view) OWNER TO postgres;

--
-- Name: get_categories(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_categories() RETURNS TABLE(category character varying)
    LANGUAGE plpgsql
    AS $$
begin
 return query 
 
 select distinct product_type.category from product_type;
end; $$;


ALTER FUNCTION public.get_categories() OWNER TO postgres;

--
-- Name: get_column_names(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_column_names(table_or_view_name character varying) RETURNS TABLE(column_name information_schema.sql_identifier)
    LANGUAGE plpgsql
    AS $$
begin
	return query 
 
	SELECT information_schema.columns.column_name
	FROM   information_schema.columns
	WHERE  table_name = table_or_view_name
	ORDER  BY ordinal_position;
end; $$;


ALTER FUNCTION public.get_column_names(table_or_view_name character varying) OWNER TO postgres;

--
-- Name: get_locations(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_locations() RETURNS TABLE(location character varying)
    LANGUAGE plpgsql
    AS $$
begin
 return query 
 
 select distinct locations."location" from locations;
end; $$;


ALTER FUNCTION public.get_locations() OWNER TO postgres;

--
-- Name: get_subcategories(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_subcategories(_category character varying) RETURNS TABLE(subcategory character varying)
    LANGUAGE plpgsql
    AS $$
begin
 return query 
 
 select product_type."subcategory" from product_type where "category"=_category;
end; $$;


ALTER FUNCTION public.get_subcategories(_category character varying) OWNER TO postgres;

--
-- Name: get_sublocations(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_sublocations(_location character varying) RETURNS TABLE(sublocation character varying)
    LANGUAGE plpgsql
    AS $$
begin
 return query 
 
 select locations."sublocation" from locations where "location"=_location;
end; $$;


ALTER FUNCTION public.get_sublocations(_location character varying) OWNER TO postgres;

--
-- Name: is_admin(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.is_admin(usermail character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	select "count"(*) into cnt from users where email=usermail and is_admin='t';
	if cnt=1 then
		return 't';
	else
		return 'f';
	end if;
end; $$;


ALTER FUNCTION public.is_admin(usermail character varying) OWNER TO postgres;

--
-- Name: pay_trigger(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pay_trigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	var record;
	msg varchar;
BEGIN
	msg='payment for ad '||new.ad_id||' with promotion days: '||new.promoted_days;
	for var in (select email from users where is_admin='t')
	loop
		perform send_message('bikroy.com', var.email, msg);
	end loop;
	return new;
END
$$;


ALTER FUNCTION public.pay_trigger() OWNER TO postgres;

--
-- Name: post_ad(boolean, character varying, integer, boolean, character varying, character varying, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.post_ad(_buy_or_sell boolean, _poster_phone character varying, _price integer, _is_negotiable boolean, _title character varying, _details character varying, _category character varying, _subcategory character varying, _location character varying, _sublocation character varying, _poster_mail character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
	adid int;
	cnt int;
	_approver_mail varchar;
begin
	if is_admin(_poster_mail) then
		_approver_mail:=_poster_mail;
	else
		_approver_mail:=NULL;
	end if;
	insert into ads(buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, "location", sublocation, poster_mail, approver_mail) values(_buy_or_sell, _poster_phone, _price, _is_negotiable, _title, _details, _category, _subcategory, _location, _sublocation, _poster_mail, _approver_mail) returning ad_id into adid;
	GET DIAGNOSTICS cnt = ROW_COUNT;
	return adid;
end; $$;


ALTER FUNCTION public.post_ad(_buy_or_sell boolean, _poster_phone character varying, _price integer, _is_negotiable boolean, _title character varying, _details character varying, _category character varying, _subcategory character varying, _location character varying, _sublocation character varying, _poster_mail character varying) OWNER TO postgres;

--
-- Name: post_trigger(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.post_trigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	msg varchar;
	price int;
BEGIN
	select ad_price into price from product_type where category=new.category and subcategory=new.subcategory;
	msg='ur ad is pending for admin approval, u need to first pay '||price||'. ur ad id is '||new.ad_id;
	perform send_message('bikroy.com', new.poster_mail, msg);
	return new;
END
$$;


ALTER FUNCTION public.post_trigger() OWNER TO postgres;

--
-- Name: report_trigger(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.report_trigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	var record;
	msg varchar;
BEGIN
	msg='report from '||new.reporter_mail||' on ad '||new.reported_ad_id||' as '||new.report_type||' with message: '||new.message;
	for var in (select email from users where is_admin='t')
	loop
		perform send_message('bikroy.com', var.email, msg);
	end loop;
	return new;
END
$$;


ALTER FUNCTION public.report_trigger() OWNER TO postgres;

--
-- Name: send_message(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.send_message(_sender_mail character varying, _receiver_mail character varying, _message character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	insert into chats(sender_mail, receiver_mail, message) values(_sender_mail, _receiver_mail, _message);
end; $$;


ALTER FUNCTION public.send_message(_sender_mail character varying, _receiver_mail character varying, _message character varying) OWNER TO postgres;

--
-- Name: chats; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chats (
    sender_mail character varying(255) NOT NULL,
    receiver_mail character varying(255) NOT NULL,
    message character varying(255) NOT NULL,
    "time" timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.chats OWNER TO postgres;

--
-- Name: locations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.locations (
    location character varying(255) NOT NULL,
    sublocation character varying(255) NOT NULL
);


ALTER TABLE public.locations OWNER TO postgres;

--
-- Name: pay_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pay_history (
    ad_id integer NOT NULL,
    promoted_days smallint DEFAULT 0,
    amount integer,
    transaction_id character varying(32) NOT NULL,
    "time" timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.pay_history OWNER TO postgres;

--
-- Name: product_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_type (
    category character varying(255) NOT NULL,
    subcategory character varying(255) DEFAULT ''::character varying NOT NULL,
    ad_price integer DEFAULT 0
);


ALTER TABLE public.product_type OWNER TO postgres;

--
-- Name: report_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.report_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.report_id_seq OWNER TO postgres;

--
-- Name: reports; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reports (
    report_id integer DEFAULT nextval('public.report_id_seq'::regclass) NOT NULL,
    report_type public.report_type NOT NULL,
    message character varying(255),
    reporter_mail character varying(255) NOT NULL,
    reported_ad_id integer NOT NULL,
    "time" timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.reports OWNER TO postgres;

--
-- Name: stars; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stars (
    starred_ad_id integer NOT NULL,
    starrer_mail character varying(255) NOT NULL,
    "time" timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.stars OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    is_admin boolean DEFAULT false,
    location character varying(255),
    sublocation character varying(255)
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Data for Name: ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ads (ad_id, buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, location, sublocation, poster_mail, approver_mail, "time") FROM stdin;
2	t	12345	1000	t	ljlkjlkjl	dfasdfasf	electronics	computer	Dhaka	Malibagh	admin	admin	2019-02-15 05:22:16.842652
\.


--
-- Data for Name: car_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.car_ads (brand, model, edition, model_year, condition, transmission, body_type, fuel_type, engine_capacity, kilometers_run, ad_id) FROM stdin;
\.


--
-- Data for Name: chats; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chats (sender_mail, receiver_mail, message, "time") FROM stdin;
\.


--
-- Data for Name: electronics_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.electronics_ads (brand, model, ad_id) FROM stdin;
dell	n4050	2
\.


--
-- Data for Name: locations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.locations (location, sublocation) FROM stdin;
BUET	CSE
BUET	EEE
Dhaka	Malibagh
Dhaka	Mirpur
Dhaka	Jatrabari
Khulna	KUET
Khulna	KU
\.


--
-- Data for Name: mobile_phone_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mobile_phone_ads (brand, model, edition, features, authenticity, condition, ad_id) FROM stdin;
\.


--
-- Data for Name: motor_cycle_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.motor_cycle_ads (bike_type, brand, model, model_year, condition, engine_capacity, kilometers_run, ad_id) FROM stdin;
\.


--
-- Data for Name: pay_history; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pay_history (ad_id, promoted_days, amount, transaction_id, "time") FROM stdin;
\.


--
-- Data for Name: product_type; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_type (category, subcategory, ad_price) FROM stdin;
vehicle	motor_cycle	500
vehicle	car	1000
mobile	mobile_phone	100
electronics	computer	200
electronics	computer accessories	50
electronics	tv	150
electronics	others	50
others	others	25
\.


--
-- Data for Name: reports; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.reports (report_id, report_type, message, reporter_mail, reported_ad_id, "time") FROM stdin;
\.


--
-- Data for Name: stars; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stars (starred_ad_id, starrer_mail, "time") FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (email, password, name, is_admin, location, sublocation) FROM stdin;
cr7@gmail.com	cr7	cristiano ronaldo	f	Dhaka	Jatrabari
admin2	adminMugdho	admin	t	Dhaka	Jatrabari
bikroy.com	ami i database	database itself	t	BUET	CSE
lm10@gmail.com	I am the GOAT	lionel messi	f	Dhaka	Mirpur
admin	ami i admin	admin	t	BUET	CSE
nazrinshukti	lifeispink	nazrin shukti	f	BUET	CSE
\.


--
-- Name: ad_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ad_id_seq', 2, true);


--
-- Name: report_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.report_id_seq', 1, true);


--
-- Name: ads ads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ads
    ADD CONSTRAINT ads_pkey PRIMARY KEY (ad_id);


--
-- Name: car_ads car_ads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.car_ads
    ADD CONSTRAINT car_ads_pkey PRIMARY KEY (ad_id);


--
-- Name: electronics_ads electronics_ads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.electronics_ads
    ADD CONSTRAINT electronics_ads_pkey PRIMARY KEY (ad_id);


--
-- Name: locations locations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_pkey PRIMARY KEY (location, sublocation);


--
-- Name: mobile_phone_ads mobile_ads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mobile_phone_ads
    ADD CONSTRAINT mobile_ads_pkey PRIMARY KEY (ad_id);


--
-- Name: motor_cycle_ads motor_cycle_ads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.motor_cycle_ads
    ADD CONSTRAINT motor_cycle_ads_pkey PRIMARY KEY (ad_id);


--
-- Name: pay_history pay_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_history
    ADD CONSTRAINT pay_history_pkey PRIMARY KEY (transaction_id);


--
-- Name: product_type product_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_type
    ADD CONSTRAINT product_type_pkey PRIMARY KEY (category, subcategory);


--
-- Name: reports reports_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_pkey PRIMARY KEY (reporter_mail, reported_ad_id);


--
-- Name: stars stars_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stars
    ADD CONSTRAINT stars_pkey PRIMARY KEY (starred_ad_id, starrer_mail);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (email);


--
-- Name: ads approve_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER approve_trigger AFTER UPDATE OF approver_mail ON public.ads FOR EACH ROW WHEN (((old.approver_mail IS NULL) AND (new.approver_mail IS NOT NULL))) EXECUTE PROCEDURE public.approve_trigger();


--
-- Name: pay_history pay_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER pay_trigger AFTER INSERT ON public.pay_history FOR EACH ROW EXECUTE PROCEDURE public.pay_trigger();


--
-- Name: ads post_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER post_trigger AFTER INSERT ON public.ads FOR EACH ROW WHEN ((NOT public.is_admin(new.poster_mail))) EXECUTE PROCEDURE public.post_trigger();


--
-- Name: reports report_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER report_trigger AFTER INSERT ON public.reports FOR EACH ROW EXECUTE PROCEDURE public.report_trigger();


--
-- Name: ads ads_approver_mail_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ads
    ADD CONSTRAINT ads_approver_mail_fkey FOREIGN KEY (approver_mail) REFERENCES public.users(email) ON UPDATE SET NULL ON DELETE SET NULL;


--
-- Name: ads ads_category_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ads
    ADD CONSTRAINT ads_category_fkey FOREIGN KEY (category, subcategory) REFERENCES public.product_type(category, subcategory) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: ads ads_location_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ads
    ADD CONSTRAINT ads_location_fkey FOREIGN KEY (location, sublocation) REFERENCES public.locations(location, sublocation) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: ads ads_poster_mail_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ads
    ADD CONSTRAINT ads_poster_mail_fkey FOREIGN KEY (poster_mail) REFERENCES public.users(email) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: car_ads car_ads_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.car_ads
    ADD CONSTRAINT car_ads_ad_id_fkey FOREIGN KEY (ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chats chats_receiver_mail_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chats
    ADD CONSTRAINT chats_receiver_mail_fkey FOREIGN KEY (receiver_mail) REFERENCES public.users(email) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chats chats_sender_mail_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chats
    ADD CONSTRAINT chats_sender_mail_fkey FOREIGN KEY (sender_mail) REFERENCES public.users(email) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: electronics_ads electronics_ads_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.electronics_ads
    ADD CONSTRAINT electronics_ads_ad_id_fkey FOREIGN KEY (ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: mobile_phone_ads mobile_ads_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mobile_phone_ads
    ADD CONSTRAINT mobile_ads_ad_id_fkey FOREIGN KEY (ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: motor_cycle_ads motor_cycle_ads_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.motor_cycle_ads
    ADD CONSTRAINT motor_cycle_ads_ad_id_fkey FOREIGN KEY (ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: pay_history pay_history_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_history
    ADD CONSTRAINT pay_history_ad_id_fkey FOREIGN KEY (ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: reports reports_reported_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_reported_ad_id_fkey FOREIGN KEY (reported_ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: reports reports_reporter_mail_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_reporter_mail_fkey FOREIGN KEY (reporter_mail) REFERENCES public.users(email) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stars stars_starred_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stars
    ADD CONSTRAINT stars_starred_ad_id_fkey FOREIGN KEY (starred_ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stars stars_starrer_mail_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stars
    ADD CONSTRAINT stars_starrer_mail_fkey FOREIGN KEY (starrer_mail) REFERENCES public.users(email) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: users users_location_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_location_fkey FOREIGN KEY (location, sublocation) REFERENCES public.locations(location, sublocation) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

