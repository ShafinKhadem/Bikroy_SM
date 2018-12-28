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
-- Name: check_ad_type(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_ad_type(_ad_id integer, _category character varying, _subcategory character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	select "count"(*) into cnt from ads where ad_id=_ad_id and category=_category and subcategory=_subcategory;
	if cnt=0 then
		return 'f';
	else
		return 't';
	end if;
end; $$;


ALTER FUNCTION public.check_ad_type(_ad_id integer, _category character varying, _subcategory character varying) OWNER TO postgres;

--
-- Name: delete_ad(integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.delete_ad(_ad_id integer, usermail character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	delete from ads where ads.ad_id=_ad_id and (ads.poster_mail=usermail or exists(select * from users where users.email=usermail and is_admin='t'));
	GET DIAGNOSTICS cnt = ROW_COUNT;
	return cnt;
end; $$;


ALTER FUNCTION public.delete_ad(_ad_id integer, usermail character varying) OWNER TO postgres;

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
    poster_phone integer,
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
    "time" time without time zone DEFAULT CURRENT_TIME NOT NULL,
    date date DEFAULT CURRENT_DATE NOT NULL
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
    update ads set(ad_id, buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, "location", sublocation, poster_mail, approver_mail, "time", "date") =
    (_ad.ad_id, _ad.buy_or_sell, _ad.poster_phone, _ad.price, _ad.is_negotiable, _ad.title, _ad.details, _ad.category, _ad.subcategory, _ad."location", _ad.sublocation, _ad.poster_mail, _ad.approver_mail, _ad."time", _ad."date") where ad_id=_ad.ad_id;
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
    l.date,
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
            ads."time",
            ads.date
           FROM public.ads
          WHERE (((ads.category)::text = 'vehicle'::text) AND ((ads.subcategory)::text = 'car'::text))) l
     LEFT JOIN public.car_ads r ON ((l.ad_id = r.ad_id)));


ALTER TABLE public.car_ads_view OWNER TO postgres;

--
-- Name: edit_car_ad(public.car_ads_view); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_car_ad(_car_ad public.car_ads_view) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    perform edit_ad(row(_car_ad.ad_id, _car_ad.buy_or_sell, _car_ad.poster_phone, _car_ad.price, _car_ad.is_negotiable, _car_ad.title, _car_ad.details, _car_ad.category, _car_ad.subcategory, _car_ad."location", _car_ad.sublocation, _car_ad.poster_mail, _car_ad.approver_mail, _car_ad."time", _car_ad."date"));
    update car_ads set(brand, model, edition, model_year, "condition", transmission, body_type, fuel_type, engine_capacity, kilometers_run) =
    (_car_ad.brand, _car_ad.model, _car_ad.edition, _car_ad.model_year, _car_ad."condition", _car_ad.transmission, _car_ad.body_type, _car_ad.fuel_type, _car_ad.engine_capacity, _car_ad.kilometers_run) where ad_id=_car_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_car_ad(_car_ad public.car_ads_view) OWNER TO postgres;

--
-- Name: mobile_ads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mobile_ads (
    brand character varying(255),
    model character varying(255),
    edition character varying(255),
    features character varying(1000),
    authenticity character varying(255),
    condition character varying(255),
    ad_id integer NOT NULL,
    CONSTRAINT is_mobile_ad CHECK ((public.check_ad_type(ad_id, 'mobile'::character varying, 'mobile_phone'::character varying) = true))
);


ALTER TABLE public.mobile_ads OWNER TO postgres;

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
    l.date,
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
            ads."time",
            ads.date
           FROM public.ads
          WHERE ((ads.category)::text = 'electronics'::text)) l
     LEFT JOIN public.mobile_ads r ON ((l.ad_id = r.ad_id)));


ALTER TABLE public.electronics_ads_view OWNER TO postgres;

--
-- Name: edit_electronics_ad(public.electronics_ads_view); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_electronics_ad(_electronics_ad public.electronics_ads_view) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    perform edit_ad(row(_electronics_ad.ad_id, _electronics_ad.buy_or_sell, _electronics_ad.poster_phone, _electronics_ad.price, _electronics_ad.is_negotiable, _electronics_ad.title, _electronics_ad.details, _electronics_ad.category, _electronics_ad.subcategory, _electronics_ad."location", _electronics_ad.sublocation, _electronics_ad.poster_mail, _electronics_ad.approver_mail, _electronics_ad."time", _electronics_ad."date"));
    update electronics_ads set(brand, model) =
    (_electronics_ad.brand, _electronics_ad.model) where ad_id=_electronics_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_electronics_ad(_electronics_ad public.electronics_ads_view) OWNER TO postgres;

--
-- Name: mobile_ads_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.mobile_ads_view AS
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
    l.date,
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
            ads."time",
            ads.date
           FROM public.ads
          WHERE (((ads.category)::text = 'mobile'::text) AND ((ads.subcategory)::text = 'mobile_phone'::text))) l
     LEFT JOIN public.mobile_ads r ON ((l.ad_id = r.ad_id)));


ALTER TABLE public.mobile_ads_view OWNER TO postgres;

--
-- Name: edit_mobile_ad(public.mobile_ads_view); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.edit_mobile_ad(_mobile_ad public.mobile_ads_view) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
    perform edit_ad(row(_mobile_ad.ad_id, _mobile_ad.buy_or_sell, _mobile_ad.poster_phone, _mobile_ad.price, _mobile_ad.is_negotiable, _mobile_ad.title, _mobile_ad.details, _mobile_ad.category, _mobile_ad.subcategory, _mobile_ad."location", _mobile_ad.sublocation, _mobile_ad.poster_mail, _mobile_ad.approver_mail, _mobile_ad."time", _mobile_ad."date"));
    update mobile_ads set(brand, model, edition, features, authenticity, "condition") =
    (_mobile_ad.brand, _mobile_ad.model, _mobile_ad.edition, _mobile_ad.features, _mobile_ad.authenticity, _mobile_ad.condition) where ad_id=_mobile_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_mobile_ad(_mobile_ad public.mobile_ads_view) OWNER TO postgres;

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
    l.date,
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
            ads."time",
            ads.date
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
    perform edit_ad(row(_motor_cycle_ad.ad_id, _motor_cycle_ad.buy_or_sell, _motor_cycle_ad.poster_phone, _motor_cycle_ad.price, _motor_cycle_ad.is_negotiable, _motor_cycle_ad.title, _motor_cycle_ad.details, _motor_cycle_ad.category, _motor_cycle_ad.subcategory, _motor_cycle_ad."location", _motor_cycle_ad.sublocation, _motor_cycle_ad.poster_mail, _motor_cycle_ad.approver_mail, _motor_cycle_ad."time", _motor_cycle_ad."date"));
    update motor_cycle_ads set(bike_type, brand, model, model_year, "condition", engine_capacity, kilometers_run) =
    (_motor_cycle_ad.bike_type, _motor_cycle_ad.brand, _motor_cycle_ad.model, _motor_cycle_ad.model_year, _motor_cycle_ad.condition, _motor_cycle_ad.engine_capacity, _motor_cycle_ad.kilometers_run) where ad_id=_motor_cycle_ad.ad_id;
    GET DIAGNOSTICS cnt = ROW_COUNT;
    return cnt;
end; $$;


ALTER FUNCTION public.edit_motor_cycle_ad(_motor_cycle_ad public.motor_cycle_ads_view) OWNER TO postgres;

--
-- Name: get_accounts(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_accounts() RETURNS TABLE(email character varying, name character varying)
    LANGUAGE plpgsql
    AS $$
begin
 return query 
 
 select "users"."email", "users"."name" from "users"
 order by "users"."name", "users"."email";
end; $$;


ALTER FUNCTION public.get_accounts() OWNER TO postgres;

--
-- Name: get_car_ad(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_car_ad(_ad_id integer) RETURNS SETOF public.car_ads_view
    LANGUAGE plpgsql
    AS $$
begin
 return query
 
 select * from car_ads_view v where v.ad_id=_ad_id;
end; $$;


ALTER FUNCTION public.get_car_ad(_ad_id integer) OWNER TO postgres;

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
-- Name: get_chats(character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_chats(usermail1 character varying, usermail2 character varying) RETURNS TABLE(sender_mail character varying, receiver_mail character varying, message character varying, "time" time without time zone, date date)
    LANGUAGE plpgsql
    AS $$
begin
 return query 
 
 select chats.sender_mail, chats.receiver_mail, chats.message, chats."time", chats."date" from chats where 
 (chats.sender_mail=usermail1 and chats.receiver_mail=usermail2) or (chats.sender_mail=usermail2 and chats.receiver_mail=usermail1)
 order by chats."date" desc, chats."time" desc;
end; $$;


ALTER FUNCTION public.get_chats(usermail1 character varying, usermail2 character varying) OWNER TO postgres;

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
-- Name: get_electronics_ad(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_electronics_ad(_ad_id integer) RETURNS SETOF public.electronics_ads_view
    LANGUAGE plpgsql
    AS $$
begin
 return query
 
 select * from electronics_ads_view v where v.ad_id=_ad_id;
end; $$;


ALTER FUNCTION public.get_electronics_ad(_ad_id integer) OWNER TO postgres;

--
-- Name: get_favorites(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_favorites(usermail character varying) RETURNS TABLE(ad_id integer, title character varying, "time" time without time zone, date date)
    LANGUAGE plpgsql
    AS $$
begin
 return query 
 
 select r.ad_id, r.title, l."time", l."date" from
 (select * from stars where
 stars.starrer_mail=usermail) l left join
 (select ads.ad_id, ads.title from ads) r on (l.starred_ad_id=r.ad_id)
 order by l.date desc, l.time desc;
end; $$;


ALTER FUNCTION public.get_favorites(usermail character varying) OWNER TO postgres;

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
-- Name: get_mobile_ad(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_mobile_ad(_ad_id integer) RETURNS SETOF public.mobile_ads_view
    LANGUAGE plpgsql
    AS $$
begin
 return query
 
 select * from mobile_ads_view v where v.ad_id=_ad_id;
end; $$;


ALTER FUNCTION public.get_mobile_ad(_ad_id integer) OWNER TO postgres;

--
-- Name: get_motor_cycle_ad(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_motor_cycle_ad(_ad_id integer) RETURNS SETOF public.motor_cycle_ads_view
    LANGUAGE plpgsql
    AS $$
begin
 return query
 
 select * from motor_cycle_ads_view v where v.ad_id=_ad_id;
end; $$;


ALTER FUNCTION public.get_motor_cycle_ad(_ad_id integer) OWNER TO postgres;

--
-- Name: get_others_ad(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_others_ad(_ad_id integer) RETURNS SETOF public.ads
    LANGUAGE plpgsql
    AS $$
begin
 return query
 
 select * from ads v where v.ad_id=_ad_id;
end; $$;


ALTER FUNCTION public.get_others_ad(_ad_id integer) OWNER TO postgres;

--
-- Name: get_posts(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_posts(usermail character varying) RETURNS TABLE(ad_id integer, title character varying, "time" time without time zone, date date)
    LANGUAGE plpgsql
    AS $$
begin
 return query
 
 select "ads"."ad_id", "ads"."title", ads."time", ads."date" from "ads"
 where poster_mail=usermail
 order by ads."date" desc, ads."time" desc;
end; $$;


ALTER FUNCTION public.get_posts(usermail character varying) OWNER TO postgres;

--
-- Name: get_reports(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_reports(usermail character varying) RETURNS TABLE(ad_id integer, title character varying, report_type public.report_type, message character varying, "time" time without time zone, date date)
    LANGUAGE plpgsql
    AS $$
begin
 return query
 
 select r.ad_id, r.title, l.report_type, l.message, l."time", l."date" from
 (select * from reports where
 reports.reporter_mail=usermail) l left join
 (select ads.ad_id, ads.title from ads) r on (l.reported_ad_id=r.ad_id)
 order by l.date desc, l.time desc;
end; $$;


ALTER FUNCTION public.get_reports(usermail character varying) OWNER TO postgres;

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
-- Name: post_ad(boolean, integer, integer, boolean, character varying, character varying, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.post_ad(_buy_or_sell boolean, _poster_phone integer, _price integer, _is_negotiable boolean, _title character varying, _details character varying, _category character varying, _subcategory character varying, _location character varying, _sublocation character varying, _poster_mail character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    adid int;
		cnt int;
begin
 insert into ads(buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, "location", sublocation, poster_mail) values(_buy_or_sell, _poster_phone, _price, _is_negotiable, _title, _details, _category, _subcategory, _location, _sublocation, _poster_mail) returning ad_id into adid;
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return adid;
end; $$;


ALTER FUNCTION public.post_ad(_buy_or_sell boolean, _poster_phone integer, _price integer, _is_negotiable boolean, _title character varying, _details character varying, _category character varying, _subcategory character varying, _location character varying, _sublocation character varying, _poster_mail character varying) OWNER TO postgres;

--
-- Name: post_car_ad(integer, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, integer, real); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.post_car_ad(_ad_id integer, _brand character varying, _model character varying, _edition character varying, _model_year character varying, _condition character varying, _transmission character varying, _body_type character varying, _fuel_type character varying, _engine_capacity integer, _kilometers_run real) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
 insert into car_ads(brand, model, edition, model_year, "condition", transmission, body_type, fuel_type, engine_capacity, kilometers_run, ad_id) values(_brand, _model, _edition, _model_year, _condition, _transmission, _body_type, _fuel_type, _engine_capacity, _kilometers_run, _ad_id);
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return cnt;
end; $$;


ALTER FUNCTION public.post_car_ad(_ad_id integer, _brand character varying, _model character varying, _edition character varying, _model_year character varying, _condition character varying, _transmission character varying, _body_type character varying, _fuel_type character varying, _engine_capacity integer, _kilometers_run real) OWNER TO postgres;

--
-- Name: post_electronics_ad(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.post_electronics_ad(_ad_id integer, _brand character varying, _model character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
 insert into mobile_ads(brand, model, ad_id) values(_brand, _model, _ad_id);
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return cnt;
end; $$;


ALTER FUNCTION public.post_electronics_ad(_ad_id integer, _brand character varying, _model character varying) OWNER TO postgres;

--
-- Name: post_mobile_ad(integer, character varying, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.post_mobile_ad(_ad_id integer, _brand character varying, _model character varying, _edition character varying, _features character varying, _authenticity character varying, _condition character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
 insert into mobile_ads(brand, model, edition, features, authenticity, "condition", ad_id) values(_brand, _model, _edition, _features, _authenticity, _condition, _ad_id);
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return cnt;
end; $$;


ALTER FUNCTION public.post_mobile_ad(_ad_id integer, _brand character varying, _model character varying, _edition character varying, _features character varying, _authenticity character varying, _condition character varying) OWNER TO postgres;

--
-- Name: post_motor_cycle_ad(integer, character varying, character varying, character varying, integer, character varying, integer, real); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.post_motor_cycle_ad(_ad_id integer, _bike_type character varying, _brand character varying, _model character varying, _model_year integer, _condition character varying, _engine_capacity integer, _kilometers_run real) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    cnt int;
begin
 insert into motor_cycle_ads(bike_type, brand, model, model_year, "condition", engine_capacity, kilometers_run, ad_id) values(_bike_type, _brand, _model, _model_year, _condition, _engine_capacity, _kilometers_run, _ad_id);
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return cnt;
end; $$;


ALTER FUNCTION public.post_motor_cycle_ad(_ad_id integer, _bike_type character varying, _brand character varying, _model character varying, _model_year integer, _condition character varying, _engine_capacity integer, _kilometers_run real) OWNER TO postgres;

--
-- Name: report_ad(integer, character varying, public.report_type, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.report_ad(_ad_id integer, _email character varying, _report_type public.report_type, _message character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
 insert into reports(reported_ad_id, reporter_mail, report_type, message) values(_ad_id, _email, _report_type, _message);
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return cnt;
end; $$;


ALTER FUNCTION public.report_ad(_ad_id integer, _email character varying, _report_type public.report_type, _message character varying) OWNER TO postgres;

--
-- Name: send_message(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.send_message(_sender_mail character varying, _receiver_mail character varying, _message character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
 insert into chats(sender_mail, receiver_mail, message) values(_sender_mail, _receiver_mail, _message);
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return cnt;
end; $$;


ALTER FUNCTION public.send_message(_sender_mail character varying, _receiver_mail character varying, _message character varying) OWNER TO postgres;

--
-- Name: star_ad(integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.star_ad(_ad_id integer, _email character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
 insert into stars(starred_ad_id, starrer_mail) values(_ad_id, _email);
 GET DIAGNOSTICS cnt = ROW_COUNT;
 return cnt;
end; $$;


ALTER FUNCTION public.star_ad(_ad_id integer, _email character varying) OWNER TO postgres;

--
-- Name: star_remove_ad(integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.star_remove_ad(_ad_id integer, _email character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
	cnt int;
begin
	delete from stars where stars.starred_ad_id=_ad_id and stars.starrer_mail=_email;
	GET DIAGNOSTICS cnt = ROW_COUNT;
	return cnt;
end; $$;


ALTER FUNCTION public.star_remove_ad(_ad_id integer, _email character varying) OWNER TO postgres;

--
-- Name: chats; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chats (
    sender_mail character varying(255) NOT NULL,
    receiver_mail character varying(255) NOT NULL,
    message character varying(255) NOT NULL,
    "time" time(6) without time zone DEFAULT CURRENT_TIME NOT NULL,
    date date DEFAULT CURRENT_DATE NOT NULL
);


ALTER TABLE public.chats OWNER TO postgres;

--
-- Name: electronics_ads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.electronics_ads (
    brand character varying(255),
    model character varying(255),
    ad_id integer NOT NULL
);


ALTER TABLE public.electronics_ads OWNER TO postgres;

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
    is_promoted boolean,
    amount integer,
    transaction_id integer NOT NULL,
    "time" time(6) without time zone DEFAULT CURRENT_TIME NOT NULL,
    date date DEFAULT CURRENT_DATE NOT NULL
);


ALTER TABLE public.pay_history OWNER TO postgres;

--
-- Name: product_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_type (
    category character varying(255) NOT NULL,
    subcategory character varying(255) DEFAULT ''::character varying NOT NULL
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
    "time" time(6) without time zone DEFAULT CURRENT_TIME NOT NULL,
    date date DEFAULT CURRENT_DATE NOT NULL
);


ALTER TABLE public.reports OWNER TO postgres;

--
-- Name: stars; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stars (
    starred_ad_id integer NOT NULL,
    starrer_mail character varying(255) NOT NULL,
    "time" time(6) without time zone DEFAULT CURRENT_TIME NOT NULL,
    date date DEFAULT CURRENT_DATE NOT NULL
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

COPY public.ads (ad_id, buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, location, sublocation, poster_mail, approver_mail, "time", date) FROM stdin;
5	f	420	1	f	gari kinum, but taka nai	apni ki apnar shopner gariti harate chan? na harate chaile aji amake diye din.	vehicle	car	Dhaka	Malibagh	admin	\N	02:30:07.811652	2019-01-26
3	f	0	1	t	demo	demo	mobile	mobile_phone	BUET	CSE	admin	\N	10:09:15.500105	2019-01-24
1	t	0	10000	f	honda	\N	vehicle	motor_cycle	BUET	CSE	admin2	\N	09:32:48.96786	2019-01-24
4	t	1	1	t	testing edit_mobile_ad from php	1	mobile	mobile_phone	BUET	CSE	admin2	\N	11:44:10.383567	2019-01-24
2	t	0	1	t	untitled	testing edit	vehicle	car	BUET	CSE	admin	\N	10:09:15.500105	2019-01-24
\.


--
-- Data for Name: car_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.car_ads (brand, model, edition, model_year, condition, transmission, body_type, fuel_type, engine_capacity, kilometers_run, ad_id) FROM stdin;
Allah Hafej	2	3	4	5	6	7	8	9	10	5
toyota	corolla	2011	2010	vanga	manual	plastic	kerosine	0	100000	2
\.


--
-- Data for Name: chats; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chats (sender_mail, receiver_mail, message, "time", date) FROM stdin;
admin	admin2	'hello mugdho'	10:11:58.241146	2019-01-24
admin2	admin	'hello shafin'	10:11:58.241146	2019-01-24
admin	admin2	testingFunction	10:11:58.241146	2019-01-24
admin2	admin	testing2	10:11:58.241146	2019-01-24
admin	admin2	hello from php	11:14:03.481175	2019-01-25
admin	admin2	doesnot work if use some punctuations	11:20:07.664005	2019-01-25
admin	admin2	let's check apostrophe	13:28:05.727881	2019-01-25
admin	admin2	let's check other genjaimma punctuations: !@#$%^&*()_-+={[]};:'",<.>/?`~...	13:30:31.039192	2019-01-25
admin	admin2	hello tasin	05:07:05.533315	2019-01-26
\.


--
-- Data for Name: electronics_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.electronics_ads (brand, model, ad_id) FROM stdin;
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
\.


--
-- Data for Name: mobile_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mobile_ads (brand, model, edition, features, authenticity, condition, ad_id) FROM stdin;
nokia	1100	express	charge, no vanga	orginal	abar jigay	3
nokia	1200	express	memory card	nai	vanga	4
\.


--
-- Data for Name: motor_cycle_ads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.motor_cycle_ads (bike_type, brand, model, model_year, condition, engine_capacity, kilometers_run, ad_id) FROM stdin;
honda	honda	honda	1500	vanga	0	1000000	1
\.


--
-- Data for Name: pay_history; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pay_history (ad_id, is_promoted, amount, transaction_id, "time", date) FROM stdin;
\.


--
-- Data for Name: product_type; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_type (category, subcategory) FROM stdin;
vehicle	motor_cycle
vehicle	car
mobile	mobile_phone
electronics	computer
electronics	computer accessories
electronics	tv
electronics	others
others	others
\.


--
-- Data for Name: reports; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.reports (report_id, report_type, message, reporter_mail, reported_ad_id, "time", date) FROM stdin;
1	spam	faltu ad	admin	4	08:21:02.658913	2019-01-25
3	wrong category	sorry, just testing wrong category type.	admin	1	15:04:58.014406	2019-01-25
2	fraud	report my own ad XD to test punctuations: !@#$%^&*()_-+={[]};:'",<.>/?`~....	admin	3	13:32:32.438136	2019-01-25
\.


--
-- Data for Name: stars; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stars (starred_ad_id, starrer_mail, "time", date) FROM stdin;
4	admin	10:14:31.596875	2019-01-25
2	admin2	20:56:41.668591	2019-01-24
3	admin	07:51:13.063194	2019-01-24
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (email, password, name, is_admin, location, sublocation) FROM stdin;
admin2	adminMugdho	admin	t	\N	\N
admin	ki vabsila ami i admin	admin	t	Dhaka	Malibagh
nazrinshukti	lifeispink	nazrin shukti	f	\N	\N
lm10@gmail.com	I am the GOAT	lionel messi	f	\N	\N
cr7@gmail.com	cr7	cristiano ronaldo	f	\N	\N
\.


--
-- Name: ad_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ad_id_seq', 5, true);


--
-- Name: report_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.report_id_seq', 3, true);


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
-- Name: mobile_ads mobile_ads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mobile_ads
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
-- Name: mobile_ads mobile_ads_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mobile_ads
    ADD CONSTRAINT mobile_ads_ad_id_fkey FOREIGN KEY (ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: motor_cycle_ads motor_cycle_ads_ad_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.motor_cycle_ads
    ADD CONSTRAINT motor_cycle_ads_ad_id_fkey FOREIGN KEY (ad_id) REFERENCES public.ads(ad_id) ON UPDATE CASCADE ON DELETE CASCADE;


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

