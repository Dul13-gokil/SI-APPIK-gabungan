--
-- PostgreSQL database dump
--


-- Dumped from database version 18.6 (Debian 18.6-1.pgdg13+2)
-- Dumped by pg_dump version 18.6

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

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: ai_assessments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ai_assessments (
    id bigint NOT NULL,
    complaint_id bigint NOT NULL,
    model character varying(255) NOT NULL,
    task character varying(255) NOT NULL,
    result_json json NOT NULL,
    confidence numeric(5,4) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ai_assessments OWNER TO postgres;

--
-- Name: ai_assessments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ai_assessments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_assessments_id_seq OWNER TO postgres;

--
-- Name: ai_assessments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ai_assessments_id_seq OWNED BY public.ai_assessments.id;


--
-- Name: app_db_sync_marker; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.app_db_sync_marker (
    version character varying(64) NOT NULL,
    synced_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.app_db_sync_marker OWNER TO postgres;

--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    actor_id bigint,
    action character varying(255) NOT NULL,
    entity_type character varying(255) NOT NULL,
    entity_id bigint NOT NULL,
    before_json json,
    after_json json,
    ip character varying(45),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.audit_logs OWNER TO postgres;

--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.audit_logs_id_seq OWNER TO postgres;

--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    department_id bigint NOT NULL,
    severity_default integer DEFAULT 3 NOT NULL,
    ai_label character varying(255),
    sla_hours integer DEFAULT 72 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.categories OWNER TO postgres;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_id_seq OWNER TO postgres;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: chat_messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chat_messages (
    id bigint NOT NULL,
    chat_session_id uuid NOT NULL,
    sender character varying(255) NOT NULL,
    message text NOT NULL,
    payload_json json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.chat_messages OWNER TO postgres;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.chat_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chat_messages_id_seq OWNER TO postgres;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.chat_messages_id_seq OWNED BY public.chat_messages.id;


--
-- Name: chat_sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chat_sessions (
    id uuid NOT NULL,
    user_id bigint,
    current_state character varying(255) DEFAULT 'START'::character varying NOT NULL,
    draft_json json,
    is_completed boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.chat_sessions OWNER TO postgres;

--
-- Name: complaint_photos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.complaint_photos (
    id bigint NOT NULL,
    complaint_id bigint NOT NULL,
    storage_key character varying(255) NOT NULL,
    url character varying(255),
    type character varying(255) DEFAULT 'evidence_before'::character varying NOT NULL,
    mime character varying(255),
    size bigint,
    checksum character varying(255),
    captured_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    file_data text
);


ALTER TABLE public.complaint_photos OWNER TO postgres;

--
-- Name: complaint_photos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.complaint_photos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.complaint_photos_id_seq OWNER TO postgres;

--
-- Name: complaint_photos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.complaint_photos_id_seq OWNED BY public.complaint_photos.id;


--
-- Name: complaint_ratings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.complaint_ratings (
    id bigint NOT NULL,
    complaint_id bigint NOT NULL,
    user_id bigint NOT NULL,
    rating smallint NOT NULL,
    comment text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.complaint_ratings OWNER TO postgres;

--
-- Name: complaint_ratings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.complaint_ratings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.complaint_ratings_id_seq OWNER TO postgres;

--
-- Name: complaint_ratings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.complaint_ratings_id_seq OWNED BY public.complaint_ratings.id;


--
-- Name: complaint_status_histories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.complaint_status_histories (
    id bigint NOT NULL,
    complaint_id bigint NOT NULL,
    from_status character varying(255),
    to_status character varying(255) NOT NULL,
    note text,
    changed_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.complaint_status_histories OWNER TO postgres;

--
-- Name: complaint_status_histories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.complaint_status_histories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.complaint_status_histories_id_seq OWNER TO postgres;

--
-- Name: complaint_status_histories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.complaint_status_histories_id_seq OWNED BY public.complaint_status_histories.id;


--
-- Name: complaints; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.complaints (
    id bigint NOT NULL,
    public_code character varying(255) NOT NULL,
    user_id bigint NOT NULL,
    category_id bigint NOT NULL,
    region_id bigint,
    title character varying(255) NOT NULL,
    description text NOT NULL,
    address character varying(255) NOT NULL,
    latitude numeric(10,7) NOT NULL,
    longitude numeric(10,7) NOT NULL,
    status character varying(255) DEFAULT 'SUBMITTED'::character varying NOT NULL,
    priority_score numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    priority_level character varying(255) DEFAULT 'LOW'::character varying NOT NULL,
    ai_category character varying(255),
    ai_confidence numeric(5,4),
    is_anonymous boolean DEFAULT false NOT NULL,
    parent_complaint_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    geom text,
    damage_type character varying(255) DEFAULT 'minor'::character varying NOT NULL,
    road_length character varying(255),
    road_condition character varying(255),
    end_latitude numeric(10,7),
    end_longitude numeric(10,7),
    route_coordinates json,
    rejected_by_superadmin boolean DEFAULT false NOT NULL,
    admin_rejection_reason text,
    admin_rejected_at timestamp(0) without time zone,
    admin_rejected_by bigint
);


ALTER TABLE public.complaints OWNER TO postgres;

--
-- Name: complaints_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.complaints_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.complaints_id_seq OWNER TO postgres;

--
-- Name: complaints_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.complaints_id_seq OWNED BY public.complaints.id;


--
-- Name: departments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departments (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.departments OWNER TO postgres;

--
-- Name: departments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.departments_id_seq OWNER TO postgres;

--
-- Name: departments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departments_id_seq OWNED BY public.departments.id;


--
-- Name: dispositions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dispositions (
    id bigint NOT NULL,
    complaint_id bigint NOT NULL,
    department_id bigint NOT NULL,
    assigned_by bigint,
    assigned_to bigint,
    instruction text NOT NULL,
    deadline timestamp(0) without time zone,
    status character varying(255) DEFAULT 'PENDING'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    team_id bigint
);


ALTER TABLE public.dispositions OWNER TO postgres;

--
-- Name: dispositions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dispositions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.dispositions_id_seq OWNER TO postgres;

--
-- Name: dispositions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dispositions_id_seq OWNED BY public.dispositions.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    body text NOT NULL,
    data_json json,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.notifications OWNER TO postgres;

--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notifications_id_seq OWNER TO postgres;

--
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- Name: otp_verifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.otp_verifications (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    code character varying(6) NOT NULL,
    purpose character varying(50) NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    used_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    attempts smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE public.otp_verifications OWNER TO postgres;

--
-- Name: otp_verifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.otp_verifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.otp_verifications_id_seq OWNER TO postgres;

--
-- Name: otp_verifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.otp_verifications_id_seq OWNED BY public.otp_verifications.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: regions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.regions (
    id bigint NOT NULL,
    province character varying(255) DEFAULT 'Jawa Tengah'::character varying NOT NULL,
    regency character varying(255) NOT NULL,
    district character varying(255) NOT NULL,
    village character varying(255),
    latitude numeric(10,7),
    longitude numeric(10,7),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    geom text
);


ALTER TABLE public.regions OWNER TO postgres;

--
-- Name: regions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.regions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.regions_id_seq OWNER TO postgres;

--
-- Name: regions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.regions_id_seq OWNED BY public.regions.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: supports; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.supports (
    id bigint NOT NULL,
    complaint_id bigint NOT NULL,
    user_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.supports OWNER TO postgres;

--
-- Name: supports_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.supports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.supports_id_seq OWNER TO postgres;

--
-- Name: supports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.supports_id_seq OWNED BY public.supports.id;


--
-- Name: system_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.system_settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value text NOT NULL,
    type character varying(255) DEFAULT 'string'::character varying NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.system_settings OWNER TO postgres;

--
-- Name: system_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.system_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.system_settings_id_seq OWNER TO postgres;

--
-- Name: system_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.system_settings_id_seq OWNED BY public.system_settings.id;


--
-- Name: team_members; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.team_members (
    id bigint NOT NULL,
    team_id bigint NOT NULL,
    user_id bigint NOT NULL,
    role character varying(255) DEFAULT 'MEMBER'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.team_members OWNER TO postgres;

--
-- Name: team_members_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.team_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.team_members_id_seq OWNER TO postgres;

--
-- Name: team_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.team_members_id_seq OWNED BY public.team_members.id;


--
-- Name: teams; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.teams (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    district character varying(255) DEFAULT 'Semua Wilayah'::character varying,
    department_id bigint,
    leader_id bigint,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.teams OWNER TO postgres;

--
-- Name: teams_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.teams_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.teams_id_seq OWNER TO postgres;

--
-- Name: teams_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.teams_id_seq OWNED BY public.teams.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(255),
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    role character varying(255) DEFAULT 'WARGA'::character varying NOT NULL,
    region_id bigint,
    is_anonymous boolean DEFAULT false NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    avatar text,
    last_login_ip character varying(45),
    last_login_user_agent text,
    last_login_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    deactivation_reason text,
    deactivated_at timestamp(0) without time zone,
    deactivated_by bigint
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: ai_assessments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_assessments ALTER COLUMN id SET DEFAULT nextval('public.ai_assessments_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: chat_messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages ALTER COLUMN id SET DEFAULT nextval('public.chat_messages_id_seq'::regclass);


--
-- Name: complaint_photos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_photos ALTER COLUMN id SET DEFAULT nextval('public.complaint_photos_id_seq'::regclass);


--
-- Name: complaint_ratings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_ratings ALTER COLUMN id SET DEFAULT nextval('public.complaint_ratings_id_seq'::regclass);


--
-- Name: complaint_status_histories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_status_histories ALTER COLUMN id SET DEFAULT nextval('public.complaint_status_histories_id_seq'::regclass);


--
-- Name: complaints id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaints ALTER COLUMN id SET DEFAULT nextval('public.complaints_id_seq'::regclass);


--
-- Name: departments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments ALTER COLUMN id SET DEFAULT nextval('public.departments_id_seq'::regclass);


--
-- Name: dispositions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dispositions ALTER COLUMN id SET DEFAULT nextval('public.dispositions_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- Name: otp_verifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications ALTER COLUMN id SET DEFAULT nextval('public.otp_verifications_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: regions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regions ALTER COLUMN id SET DEFAULT nextval('public.regions_id_seq'::regclass);


--
-- Name: supports id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supports ALTER COLUMN id SET DEFAULT nextval('public.supports_id_seq'::regclass);


--
-- Name: system_settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_settings ALTER COLUMN id SET DEFAULT nextval('public.system_settings_id_seq'::regclass);


--
-- Name: team_members id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members ALTER COLUMN id SET DEFAULT nextval('public.team_members_id_seq'::regclass);


--
-- Name: teams id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teams ALTER COLUMN id SET DEFAULT nextval('public.teams_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: ai_assessments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ai_assessments (id, complaint_id, model, task, result_json, confidence, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: app_db_sync_marker; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.app_db_sync_marker (version, synced_at) FROM stdin;
20261119_kabupaten_v1	2026-11-19 11:52:56.728915
\.


--
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.audit_logs (id, actor_id, action, entity_type, entity_id, before_json, after_json, ip, created_at, updated_at) FROM stdin;
2	1	user.deactivated	App\\Models\\User	5	{"is_active":true}	{"is_active":false,"reason":"menyalahgunakan sistem"}	127.0.0.1	2026-11-16 21:07:12	2026-11-16 21:07:12
3	\N	complaint.cancelled	complaint	7	{"status":"SUBMITTED"}	{"status":"CANCELLED","reason":null}	\N	2026-11-19 14:19:41	2026-11-19 14:19:41
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
si-appik-cache-379afa7f5bbd695f0c722736a8d0be935c6ac79e:timer	i:1795082554;	1795082554
si-appik-cache-379afa7f5bbd695f0c722736a8d0be935c6ac79e	i:2;	1795082554
si-appik-cache-ip_geo_36.94.61.173	s:24:"Central Jakarta, Jakarta";	1790844237
si-appik-cache-rev_geo_-5.1476_119.4327	s:31:"Kota Makassar, Sulawesi Selatan";	1790844281
si-appik-cache-rev_geo_-3.0617_120.9167	s:17:"Sulawesi Tenggara";	1790844281
si-appik-cache-d5732758fceb88e09e5d336d57ccb64ad86d5f61:timer	i:1794986412;	1794986412
si-appik-cache-d5732758fceb88e09e5d336d57ccb64ad86d5f61	i:1;	1794986412
si-appik-cache-rev_geo_-5.1447_119.5235	s:23:"Maros, Sulawesi Selatan";	1790846083
si-appik-cache-my_dev_public_ip	s:14:"180.245.204.39";	1795012182
si-appik-cache-ip_geo_180.245.204.39	s:18:"Cirebon, West Java";	1795094983
si-appik-cache-ip_geo_100.82.242.85	N;	1795163044
si-appik-cache-5c785c036466adea360111aa28563bfd556b5fba:timer	i:1795078018;	1795078018
si-appik-cache-5c785c036466adea360111aa28563bfd556b5fba	i:1;	1795078018
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories (id, name, department_id, severity_default, ai_label, sla_hours, created_at, updated_at) FROM stdin;
1	Jalan & Jembatan	1	4	pothole	48	2026-09-02 10:39:13	2026-09-03 17:07:26
2	Penerangan Jalan Umum (PJU)	1	2	street_light_damaged	72	2026-09-02 10:39:13	2026-09-03 17:07:26
3	Fasilitas Umum	1	3	sidewalk_damaged	72	2026-09-04 04:39:56	2026-11-16 18:47:22
\.


--
-- Data for Name: chat_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chat_messages (id, chat_session_id, sender, message, payload_json, created_at, updated_at) FROM stdin;
13	01a062a6-583f-71d7-98f1-14f7a9f7458b	USER	Cara lapor jalan rusak?	\N	2026-09-02 15:10:10	2026-09-02 15:10:10
53	01a0677e-1abc-7392-b7bc-824335eae2f2	USER	Berapa lama penanganan dinas?	\N	2026-09-03 14:26:08	2026-09-03 14:26:08
84	01a08239-9791-705f-b9d4-f1e72e7adb35	USER	lapor	\N	2026-09-09 02:15:43	2026-09-09 02:15:43
1	01a061ca-8cf2-703e-bbf4-0b2b81143da0	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 11:04:19	2026-09-02 11:04:19
2	01a061cb-d7dc-7325-98fd-854e7cd74117	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 11:05:44	2026-09-02 11:05:44
3	01a06287-53c6-7175-9866-5715609473b9	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 14:30:31	2026-09-02 14:30:31
4	01a06287-a794-71ab-ae1f-3f288780ff76	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 14:30:52	2026-09-02 14:30:52
153	01a0cf77-e464-71fe-a1af-52ace3559e5f	USER	Reset Sandi via OTP	\N	2026-09-24 02:13:36	2026-09-24 02:13:36
5	01a06288-0f11-737c-b21b-5d6257bf550f	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 14:31:19	2026-09-02 14:31:19
6	01a06288-52a2-739f-9410-b4ce00816980	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 14:31:36	2026-09-02 14:31:36
7	01a06297-4077-71e4-9213-6c854f2899db	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 14:47:54	2026-09-02 14:47:54
167	01a0cfb3-0e61-730c-9a1b-7a812c7ae28a	USER	otp	\N	2026-09-24 03:17:49	2026-09-24 03:17:49
170	01a0cfbb-47f9-7396-a44e-916732872baa	USER	Tentang Aplikasi	\N	2026-09-24 03:26:16	2026-09-24 03:26:16
8	01a0629f-ea40-722d-863d-83f6b6a0c573	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 14:57:22	2026-09-02 14:57:22
9	01a062a0-3bbb-735f-948c-090b10439aa6	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 14:57:43	2026-09-02 14:57:43
10	01a062a2-85ba-7006-b5fd-35f1ed9be56a	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 15:00:13	2026-09-02 15:00:13
11	01a062a4-e1a1-73cb-baa0-cc73dc0ec9b1	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 15:02:48	2026-09-02 15:02:48
12	01a062a6-583f-71d7-98f1-14f7a9f7458b	BOT	Halo! Saya asisten pengaduan infrastruktur kabupaten. Fasilitas apa yang mengalami masalah? (Contoh: Jalan rusak, Jembatan, Lampu PJU, Irigasi/Banjir)	\N	2026-09-02 15:04:23	2026-09-02 15:04:23
14	01a062a6-583f-71d7-98f1-14f7a9f7458b	BOT	Baik, laporan terkait Cara lapor jalan rusak?. Bisa ceritakan bagaimana kondisi kerusakannya dan dampaknya?	{"category":"Cara lapor jalan rusak?"}	2026-09-02 15:10:10	2026-09-02 15:10:10
15	01a062a6-583f-71d7-98f1-14f7a9f7458b	USER	kerusakan nya lumayan parah	\N	2026-09-02 15:10:22	2026-09-02 15:10:22
16	01a062a6-583f-71d7-98f1-14f7a9f7458b	BOT	Terima kasih atas penjelasannya. Di mana lokasi tepatnya atau patokan alamat kejadian?	{"category":"Cara lapor jalan rusak?","description":"kerusakan nya lumayan parah"}	2026-09-02 15:10:22	2026-09-02 15:10:22
17	01a062a6-583f-71d7-98f1-14f7a9f7458b	USER	di lasusua	\N	2026-09-02 15:10:42	2026-09-02 15:10:42
18	01a062a6-583f-71d7-98f1-14f7a9f7458b	BOT	Draf laporan Anda siap:\n- Kategori: Cara lapor jalan rusak?\n- Detail: kerusakan nya lumayan parah\n- Lokasi: di lasusua\n\nApakah Anda ingin mengirimkan laporan ini sekarang? (Ketik 'Kirim' atau 'Batal')	{"category":"Cara lapor jalan rusak?","description":"kerusakan nya lumayan parah","address":"di lasusua"}	2026-09-02 15:10:42	2026-09-02 15:10:42
19	01a062b6-674b-7220-a598-57b9abe1a642	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:21:56	2026-09-02 15:21:56
20	01a062bb-d2ce-716d-b553-4bfbf65f09ae	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:27:51	2026-09-02 15:27:51
21	01a062ca-c753-71e0-8d04-f89f452316af	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:44:11	2026-09-02 15:44:11
22	01a062ce-faf1-70b5-ac90-f2b34de7eae3	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:48:47	2026-09-02 15:48:47
23	01a062cf-ccea-7370-94c8-97b1d7feaa30	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:49:40	2026-09-02 15:49:40
24	01a062d0-6634-70fd-8d18-9a7242e77863	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:50:20	2026-09-02 15:50:20
25	01a062d3-65ab-7262-bee0-1a7ba2f80c4d	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:53:36	2026-09-02 15:53:36
26	01a062d6-8ee5-717b-b735-fb91c962a352	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:57:03	2026-09-02 15:57:03
27	01a062d9-3139-73d8-9bb2-863a64968b2e	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 15:59:56	2026-09-02 15:59:56
28	01a062f2-b2b6-70fb-8ad9-048b750dc51e	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 16:27:47	2026-09-02 16:27:47
29	01a062f8-2caf-72d3-8235-472debef8742	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 16:33:46	2026-09-02 16:33:46
216	01a0f172-baa6-7206-946d-c6bb01f2d29a	USER	Cara Verifikasi OTP	\N	2026-09-30 16:37:13	2026-09-30 16:37:13
30	01a062f9-9010-7229-a044-ac6df46f094d	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 16:35:17	2026-09-02 16:35:17
227	01a120fa-afa1-7379-a953-2eb01cef7e50	USER	Lacak tiket aduan	\N	2026-10-09 22:05:37	2026-10-09 22:05:37
229	01a120fa-afa1-7379-a953-2eb01cef7e50	USER	ai	\N	2026-10-09 22:05:55	2026-10-09 22:05:55
233	01a133b8-c81f-7210-90ea-e831c44f103e	USER	tentang aplikasi	\N	2026-10-13 13:25:35	2026-10-13 13:25:35
31	01a062fc-9d42-73e9-9408-335a6e850138	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 16:38:37	2026-09-02 16:38:37
32	01a06323-bcfa-72d4-81d2-8c08d0157979	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 17:21:21	2026-09-02 17:21:21
33	01a06323-bcfa-72d4-81d2-8c08d0157979	USER	Cara lapor jalan rusak?	\N	2026-09-02 17:22:51	2026-09-02 17:22:51
34	01a06323-bcfa-72d4-81d2-8c08d0157979	BOT	📖 **Panduan & Fitur Aplikasi Pengaduan Kabupaten:**\n\n1. **Buat Laporan:** Tekan tombol **(+) Lapor** di tengah bawah. Ambil foto kerusakan, tentukan titik lokasi GPS di peta, dan kirim.\n2. **AI Analisis Kerusakan:** Sistem AI otomatis menghitung estimasi lebar, kedalaman, dan volume material kerusakan.\n3. **Peta GIS:** Lihat persebaran laporan se-kabupaten secara visual di tab **Peta GIS**.\n4. **Laporan Saya:** Lihat status semua laporan Anda di halaman Beranda tab **Laporan Saya**.\n5. **Notifikasi:** Anda mendapat pemberitahuan saat petugas mulai bekerja hingga selesai.\n\nKetik **'Lapor'** untuk dipandu membuat aduan sekarang!	[]	2026-09-02 17:22:51	2026-09-02 17:22:51
267	01a1edb3-338e-70b4-8d74-fdf06a93c896	USER	Tentang Aplikasi	\N	2026-11-18 16:11:57	2026-11-18 16:11:57
35	01a06330-ef04-703f-8fca-1098a8ca20cd	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 17:35:46	2026-09-02 17:35:46
36	01a06349-4ad2-73a7-bb94-faceb4e30e5a	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:02:22	2026-09-02 18:02:22
37	01a06349-f400-71a9-888e-a9ef3c0a1db4	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:03:06	2026-09-02 18:03:06
294	01a1efb4-7d65-7280-957d-bd02e6eae850	USER	Apa Itu Pekerja Lapangan	\N	2026-11-19 01:29:39	2026-11-19 01:29:39
38	01a0634b-f280-724c-90f9-0179380e07d9	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:05:16	2026-09-02 18:05:16
39	01a0634b-f280-724c-90f9-0179380e07d9	USER	Cara lapor jalan rusak?	\N	2026-09-02 18:05:51	2026-09-02 18:05:51
60	01a068e5-f80c-7280-acfb-f4c2ca71e3e3	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 04:11:36	2026-09-04 04:11:36
61	01a068e7-cd33-7334-921b-9c5483decce1	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 04:13:37	2026-09-04 04:13:37
62	01a068ef-7d55-7223-a819-04e9f73a7427	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 04:22:00	2026-09-04 04:22:00
63	01a068ff-b2e4-70c0-a2fe-b1a1e25b70d1	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 04:39:43	2026-09-04 04:39:43
64	01a06905-b82e-70af-8d8e-81a7069983b0	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 04:46:17	2026-09-04 04:46:17
40	01a0634b-f280-724c-90f9-0179380e07d9	BOT	📖 **Panduan & Fitur Aplikasi Pengaduan Kabupaten:**\n\n1. **Buat Laporan:** Tekan tombol **(+) Lapor** di tengah bawah. Ambil foto kerusakan, tentukan titik lokasi GPS di peta, dan kirim.\n2. **AI Analisis Kerusakan:** Sistem AI otomatis menghitung estimasi lebar, kedalaman, dan volume material kerusakan.\n3. **Peta GIS:** Lihat persebaran laporan se-kabupaten secara visual di tab **Peta GIS**.\n4. **Laporan Saya:** Lihat status semua laporan Anda di halaman Beranda tab **Laporan Saya**.\n5. **Notifikasi:** Anda mendapat pemberitahuan saat petugas mulai bekerja hingga selesai.\n\nKetik **'Lapor'** untuk dipandu membuat aduan sekarang!	[]	2026-09-02 18:05:51	2026-09-02 18:05:51
41	01a06356-7c28-7147-a8f9-c6b45b537ffc	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:16:47	2026-09-02 18:16:47
42	01a06359-0181-732a-8430-1b55c737120a	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:19:32	2026-09-02 18:19:32
367	01a1f967-e5fe-7326-9b8c-0855a284396a	USER	Tentang Aplikasi	\N	2026-11-20 22:45:51	2026-11-20 22:45:51
43	01a0636d-cc00-7367-93c4-313aefebddb3	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:42:15	2026-09-02 18:42:15
44	01a06378-f380-7267-9c7f-559a09d1394e	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:54:26	2026-09-02 18:54:26
45	01a06378-f380-7267-9c7f-559a09d1394e	USER	Cara lapor jalan rusak?	\N	2026-09-02 18:55:03	2026-09-02 18:55:03
46	01a06378-f380-7267-9c7f-559a09d1394e	BOT	📖 **Panduan & Fitur Aplikasi Pengaduan Kabupaten:**\n\n1. **Buat Laporan:** Tekan tombol **(+) Lapor** di tengah bawah. Ambil foto kerusakan, tentukan titik lokasi GPS di peta, dan kirim.\n2. **AI Analisis Kerusakan:** Sistem AI otomatis menghitung estimasi lebar, kedalaman, dan volume material kerusakan.\n3. **Peta GIS:** Lihat persebaran laporan se-kabupaten secara visual di tab **Peta GIS**.\n4. **Laporan Saya:** Lihat status semua laporan Anda di halaman Beranda tab **Laporan Saya**.\n5. **Notifikasi:** Anda mendapat pemberitahuan saat petugas mulai bekerja hingga selesai.\n\nKetik **'Lapor'** untuk dipandu membuat aduan sekarang!	[]	2026-09-02 18:55:03	2026-09-02 18:55:03
47	01a0637b-a0c6-707f-b2b5-c5cf1b5fef43	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 18:57:21	2026-09-02 18:57:21
48	01a06382-a0ca-7350-9d53-173076e74496	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-02 19:05:00	2026-09-02 19:05:00
49	01a066f4-e70e-71f0-b118-9d0271c2e37e	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 11:08:41	2026-09-03 11:08:41
50	01a066f6-2eda-70bf-b480-c4d242148e82	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 11:10:05	2026-09-03 11:10:05
51	01a06776-6e6f-7323-8134-83e1b25e3ee7	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 13:30:09	2026-09-03 13:30:09
52	01a0677e-1abc-7392-b7bc-824335eae2f2	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 13:38:32	2026-09-03 13:38:32
54	01a0677e-1abc-7392-b7bc-824335eae2f2	BOT	⏱️ **Alur & Standar Waktu Penanganan (SLA):**\n\n1. **Warga Melapor** → Masuk sistem (Status: *Submitted*).\n2. **Admin/Operator Verifikasi** → Dicek keabsahan data (< 2 jam).\n3. **Disposisi Dinas** → Diteruskan ke PUPR / DISHUB / DLH.\n4. **Pekerja Lapangan Dikerjakan** → Estimasi waktu:\n   • **Jalan Rusak/Berlubang:** Maks. 48 Jam\n   • **Jembatan Darurat:** Maks. 24 Jam (Kritis)\n   • **Lampu PJU Padam:** Maks. 72 Jam\n   • **Drainase/Banjir:** Maks. 48 Jam\n5. **Validasi & Selesai** → Notifikasi dikirim ke HP Anda.	[]	2026-09-03 14:26:08	2026-09-03 14:26:08
55	01a067bd-da6f-7026-b4e1-ed65e3364598	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 14:48:10	2026-09-03 14:48:10
56	01a06810-768b-710e-811c-fe6066125e27	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 16:18:24	2026-09-03 16:18:24
57	01a06861-125a-738b-8208-b87b8f540957	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 17:46:27	2026-09-03 17:46:27
58	01a06872-d058-7054-a620-0f5f67d16acf	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-03 18:05:50	2026-09-03 18:05:50
59	01a068ab-429d-7093-b5e7-2cb8887f30ab	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 03:07:29	2026-09-04 03:07:29
65	01a06909-4e8e-734d-8bec-b0a0ab86b838	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 04:50:12	2026-09-04 04:50:12
66	01a06912-6038-7210-8895-03dc2ecc5ccc	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 05:00:07	2026-09-04 05:00:07
67	01a06919-fa06-73f1-a1fb-0d1f857a179f	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 05:08:25	2026-09-04 05:08:25
68	01a06ae6-c5af-7104-bb96-502ea152cf66	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 13:31:44	2026-09-04 13:31:44
69	01a06aec-666f-70ec-be88-249402c01d55	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 13:37:52	2026-09-04 13:37:52
70	01a06b24-2a06-711b-abc5-6af9676d7009	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 14:38:47	2026-09-04 14:38:47
71	01a06b27-cece-7376-908b-317959f649f5	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-04 14:42:46	2026-09-04 14:42:46
72	01a080db-0228-71a9-9497-ebdaefae90b0	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 19:50:31	2026-09-08 19:50:31
73	01a08106-8385-72c2-a8ce-68eae09256a1	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 20:38:02	2026-09-08 20:38:02
74	01a08146-e558-7247-b0c4-10598706fe49	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 21:48:22	2026-09-08 21:48:22
75	01a08154-e98c-7187-9037-6c0a2ea719d5	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 22:03:40	2026-09-08 22:03:40
76	01a08166-0b98-7306-b200-b05477cb3db9	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 22:22:23	2026-09-08 22:22:23
77	01a08169-9d7b-70d1-96b7-c8787dbc7d60	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 22:26:17	2026-09-08 22:26:17
78	01a0817c-b7d2-723f-80a9-bec28304ff08	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 22:47:09	2026-09-08 22:47:09
79	01a081ac-cc9f-700e-a3b5-59581f4c03cd	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-08 23:39:40	2026-09-08 23:39:40
80	01a081cd-ca9e-7046-8597-3e4d64e48ded	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-09 00:15:42	2026-09-09 00:15:42
81	01a0821d-1888-735b-9f6e-ebc2f4b311d0	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-09 01:42:20	2026-09-09 01:42:20
82	01a08231-68f2-721e-b8db-1a3356a40a9e	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-09 02:04:31	2026-09-09 02:04:31
83	01a08239-9791-705f-b9d4-f1e72e7adb35	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda:\n\n• 📝 Ketik **'Lapor'** untuk dipandu membuat aduan\n• 🔍 Ketik kode tiket (misal: **ADU-0001**) untuk lacak status\n• 📖 Ketik **'Panduan'** untuk cara menggunakan aplikasi\n• ⏱️ Ketik **'SLA'** untuk standar waktu pengerjaan	\N	2026-09-09 02:13:27	2026-09-09 02:13:27
85	01a08239-9791-705f-b9d4-f1e72e7adb35	BOT	Baik, saya bantu susun draf aduannya! 📋\n\nFasilitas apa yang rusak? (Pilihan: **Jalan Berlubang**, **Jembatan**, **Lampu PJU**, **Drainase/Banjir**, atau **Sampah Liar**)	[]	2026-09-09 02:15:43	2026-09-09 02:15:43
86	01a0824d-94aa-705e-92cf-95310ba365f3	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 02:35:17	2026-09-09 02:35:17
87	01a08252-fcbb-7371-ba4d-6844bb8737f1	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 02:41:11	2026-09-09 02:41:11
88	01a08254-9d0b-71a0-ba6c-e3eefb2bfeb7	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 02:42:58	2026-09-09 02:42:58
89	01a08254-fd35-7171-91b7-cae7c4ea172c	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 02:43:23	2026-09-09 02:43:23
90	01a08257-a6bd-73b6-92fb-91680a90e683	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 02:46:17	2026-09-09 02:46:17
91	01a0825b-1753-7367-817b-27468ccdfc01	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 02:50:03	2026-09-09 02:50:03
92	01a0825c-bd6c-730c-ab80-119a93b71ff5	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 02:51:51	2026-09-09 02:51:51
93	01a08274-b268-7297-8450-a73f80b7e376	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 03:18:01	2026-09-09 03:18:01
94	01a08279-8787-713d-a909-216898dfe306	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 03:23:17	2026-09-09 03:23:17
95	01a08284-b2fd-7055-9d29-47e888b0fe32	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 03:35:29	2026-09-09 03:35:29
96	01a08285-6715-70e2-8df8-5b4b05980558	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 03:36:16	2026-09-09 03:36:16
97	01a08285-6715-70e2-8df8-5b4b05980558	USER	lapor	\N	2026-09-09 03:36:39	2026-09-09 03:36:39
98	01a08285-6715-70e2-8df8-5b4b05980558	BOT	Baik, saya siap membantu menyusun draf aduan Anda! 📋\n\nPertama, pilih **Tipe Pelaporan** Anda:\n1️⃣ Ketik **'Titik'** untuk Kerusakan Titik / Ringan (< 500 meter, misal: lubang jalan, lampu PJU, trotoar)\n2️⃣ Ketik **'Ruas Panjang'** untuk Kerusakan Ruas Panjang / Parah (≥ 500 meter, khusus jalan & jembatan)	[]	2026-09-09 03:36:39	2026-09-09 03:36:39
99	01a082a1-9979-73f1-b356-790c3a589a97	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 04:07:03	2026-09-09 04:07:03
100	01a082a7-f955-708e-aaca-dc17986a34d6	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 04:14:01	2026-09-09 04:14:01
101	01a082a8-947a-73c6-9da6-b5208d34f35c	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 04:14:41	2026-09-09 04:14:41
102	01a082ab-72b9-7124-99a0-9751611c4655	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 04:17:49	2026-09-09 04:17:49
103	01a082ab-cb5e-7183-9427-5a6a028d8ea8	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Titik vs Ruas Panjang\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-09 04:18:11	2026-09-09 04:18:11
104	01a0c931-db75-7024-b7c1-bf5afdd4856c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-22 20:58:03	2026-09-22 20:58:03
105	01a0c932-6ecd-704b-8102-720cccd0b357	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-22 20:58:40	2026-09-22 20:58:40
106	01a0c933-ac28-71a2-b5c0-723a71307935	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-22 21:00:01	2026-09-22 21:00:01
107	01a0c956-8b2d-7027-b017-3aac5c71938f	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-22 21:38:07	2026-09-22 21:38:07
108	01a0c958-42cc-7391-82fe-29309afad193	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-22 21:39:59	2026-09-22 21:39:59
109	01a0c971-1c17-725c-ae17-8c7897d43231	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-22 22:07:08	2026-09-22 22:07:08
110	01a0c981-9731-71a1-99ed-e135c07ae96c	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-22 22:25:08	2026-09-22 22:25:08
111	01a0c986-953c-7373-85ef-83fb764ce5a9	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-22 22:30:35	2026-09-22 22:30:35
112	01a0c98b-a938-71f5-8179-cdf53c9f0b31	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-22 22:36:08	2026-09-22 22:36:08
113	01a0c9a3-3cc7-70f9-b90b-8d87238aa45d	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-22 23:01:53	2026-09-22 23:01:53
114	01a0c9c4-08c2-71c8-824f-1ca350430bde	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-22 23:37:42	2026-09-22 23:37:42
115	01a0c9c4-d6ca-7373-97ba-e79a7e6e8f2f	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-22 23:38:35	2026-09-22 23:38:35
149	01a0cf5f-ccef-71d4-b4e7-efcbaa272c7d	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 01:45:57	2026-09-24 01:45:57
116	01a0c9db-cdf3-7201-b861-ad4f1f32de30	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 00:03:40	2026-09-23 00:03:40
117	01a0c9e5-ef1d-712c-9cf0-17233e32ca41	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-23 00:14:44	2026-09-23 00:14:44
118	01a0c9f0-db56-731f-9755-b7972eab9bda	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-23 00:26:40	2026-09-23 00:26:40
119	01a0c9f1-c5c2-733b-b8e0-37b7400b84c0	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 00:27:40	2026-09-23 00:27:40
120	01a0ca3e-2edb-7118-879e-fb362de47fba	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 01:51:08	2026-09-23 01:51:08
121	01a0ca4b-d4a7-72ec-aff1-833a11922244	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 02:06:02	2026-09-23 02:06:02
122	01a0ca4c-a993-70ab-9799-ff9b13a98eeb	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-23 02:06:56	2026-09-23 02:06:56
123	01a0ca4d-7a41-70c0-a617-3152db97c97b	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 02:07:50	2026-09-23 02:07:50
124	01a0ca5c-9402-7355-8d71-47d6bac9576e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 02:24:19	2026-09-23 02:24:19
125	01a0ca74-f1e9-7007-88b5-c98a922953f2	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 02:50:56	2026-09-23 02:50:56
150	01a0cf61-5080-71ab-970a-17b5834ca0c1	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 01:47:36	2026-09-24 01:47:36
126	01a0ca7a-0a62-72e8-a7dd-b05243938e50	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 02:56:30	2026-09-23 02:56:30
127	01a0ca82-f9a3-739e-9f53-4e412b7edd88	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 03:06:16	2026-09-23 03:06:16
128	01a0ca93-2902-7199-b7d3-03f70aae278f	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 03:23:57	2026-09-23 03:23:57
129	01a0ca95-14b0-7374-bb53-2c387d875df0	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-23 03:26:02	2026-09-23 03:26:02
130	01a0ca96-6a89-70d3-91e2-0405c903ad21	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 03:27:30	2026-09-23 03:27:30
131	01a0ca97-a89e-73ec-b6be-5dc689239e80	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 03:28:51	2026-09-23 03:28:51
143	01a0cf58-3e78-719f-89b8-23889198d193	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	[]	2026-09-24 01:37:42	2026-09-24 01:37:42
132	01a0cc60-f5c5-72db-89d8-cb636b6b4ca0	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 11:48:21	2026-09-23 11:48:21
133	01a0cc73-6124-7034-bf03-33753ee876b3	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 12:08:28	2026-09-23 12:08:28
134	01a0cc7f-fe28-72ac-8868-9a22851e536c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 12:22:15	2026-09-23 12:22:15
135	01a0cc8e-8793-7021-8e98-507eb45cee3f	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 12:38:08	2026-09-23 12:38:08
136	01a0cc98-ca3a-717c-89f8-53b4df2abe0c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 12:49:20	2026-09-23 12:49:20
137	01a0cca8-2534-71a3-ae37-c91d37da2e58	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 13:06:06	2026-09-23 13:06:06
138	01a0ccaf-8c94-702a-91c2-28ef5fbdebca	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 13:14:12	2026-09-23 13:14:12
139	01a0ccb3-4b6a-72e0-8de9-1b96b4c489f3	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 13:18:17	2026-09-23 13:18:17
140	01a0cce4-3828-7378-97de-bdaeb96c90e3	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 14:11:43	2026-09-23 14:11:43
141	01a0cd80-ecf4-7100-a271-1ab42be38020	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-23 17:02:53	2026-09-23 17:02:53
142	01a0cf58-3e78-719f-89b8-23889198d193	USER	Halo bot	\N	2026-09-24 01:37:42	2026-09-24 01:37:42
144	01a0cf58-7541-7207-9c45-1689bdfad78a	USER	Halo bot	\N	2026-09-24 01:37:56	2026-09-24 01:37:56
145	01a0cf58-7541-7207-9c45-1689bdfad78a	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	[]	2026-09-24 01:37:56	2026-09-24 01:37:56
146	01a0cf5e-71e3-7144-a155-721406f13a8e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-24 01:44:28	2026-09-24 01:44:28
147	01a0cf5e-71e3-7144-a155-721406f13a8e	USER	halo	\N	2026-09-24 01:44:42	2026-09-24 01:44:42
148	01a0cf5e-71e3-7144-a155-721406f13a8e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	[]	2026-09-24 01:44:42	2026-09-24 01:44:42
151	01a0cf64-2628-71b2-bede-5e5f907a7d79	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 01:50:42	2026-09-24 01:50:42
152	01a0cf77-e464-71fe-a1af-52ace3559e5f	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 02:12:16	2026-09-24 02:12:16
154	01a0cf77-e464-71fe-a1af-52ace3559e5f	BOT	🔐 **Panduan Reset Kata Sandi via Kode OTP (Warga & Pekerja Lapangan):**\n\n1. **Buka Layar Masuk:** Pada layar login aplikasi mobile, tekan tombol **'Lupa Sandi?'**.\n2. **Kirim Kode OTP:** Masukkan alamat email atau nomor WhatsApp akun terdaftar Anda, lalu tekan **'Kirim Kode OTP'** (6 digit kode verifikasi akan dikirimkan).\n3. **Verifikasi & Masukkan Sandi Baru:** Masukkan 6 digit kode OTP dan buat kata sandi baru (minimal 8 karakter).\n4. **Selesai:** Kata sandi Anda berhasil diperbarui dan Anda langsung dapat login kembali ke aplikasi.\n\nℹ️ *Catatan:* Pembuatan akun Pekerja Lapangan, Operator, dan Pimpinan dikelola oleh SuperAdmin di Web Panel, namun fitur reset sandi mandiri via OTP dapat dilakukan langsung oleh Petugas di aplikasi mobile.	[]	2026-09-24 02:13:36	2026-09-24 02:13:36
155	01a0cf87-7562-7199-85a6-506ddd411bfa	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 02:29:16	2026-09-24 02:29:16
156	01a0cf8b-3e45-7268-a280-2fa60f134ae1	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 02:33:24	2026-09-24 02:33:24
157	01a0cf8c-04b8-726f-b64b-cd2a4deef90c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-24 02:34:15	2026-09-24 02:34:15
158	01a0cf8c-04b8-726f-b64b-cd2a4deef90c	USER	Cara Mengambil Tugas	\N	2026-09-24 02:35:07	2026-09-24 02:35:07
159	01a0cf8c-04b8-726f-b64b-cd2a4deef90c	BOT	📋 **Panduan Mengambil & Memulai Tugas Lapangan:**\n\n1. **Buka Tab Beranda:** Masuk ke menu Beranda Pekerja Lapangan untuk melihat Daftar Perintah Kerja (SPK).\n2. **Pilih Penugasan:** Ketuk kartu tugas yang ingin Anda kerjakan atau pilih tab *Tugas Saya*.\n3. **Tekan 'Mulai Kerja Lapangan':** Tombol ini langsung mencatat waktu resmi mulai kerja dan membuka peta navigasi GIS ke titik lokasi kerusakan.\n4. **Batas Kuota:** Anda dibatasi maksimal mengerjakan **3 tugas aktif** secara serentak agar penyelesaian tetap fokus dan memenuhi target SLA.	[]	2026-09-24 02:35:07	2026-09-24 02:35:07
160	01a0cf8d-1e1e-708d-82f5-6e60aef7be80	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 02:35:27	2026-09-24 02:35:27
161	01a0cf94-9d99-725e-86cf-9da1e48bd148	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 02:43:38	2026-09-24 02:43:38
162	01a0cf99-2079-7093-b66c-594ef1e21be5	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **ADU-0001**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 02:48:34	2026-09-24 02:48:34
163	01a0cf9b-089c-718b-ac43-df9d9f889f72	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-24 02:50:39	2026-09-24 02:50:39
164	01a0cfa4-46b1-709b-be7d-9c78dca2ad0c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **ADU-0001**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-24 03:00:44	2026-09-24 03:00:44
165	01a0cfb1-9666-7117-8617-30a767f904ac	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Panduan Pakai Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota tugas aktif pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket aduan (misal: **0005/ringan/2026**) untuk mengecek status tiket\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-24 03:15:17	2026-09-24 03:15:17
166	01a0cfb3-0e61-730c-9a1b-7a812c7ae28a	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	\N	2026-09-24 03:16:53	2026-09-24 03:16:53
168	01a0cfb3-0e61-730c-9a1b-7a812c7ae28a	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Lapor'** untuk dipandu menyusun draf aduan interaktif\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten	[]	2026-09-24 03:17:49	2026-09-24 03:17:49
169	01a0cfbb-47f9-7396-a44e-916732872baa	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-24 03:25:52	2026-09-24 03:25:52
171	01a0cfbb-47f9-7396-a44e-916732872baa	BOT	📱 **Tentang Aplikasi Pengaduan Infrastruktur Kabupaten:**\n\nAplikasi ini merupakan platform digital terpadu milik Pemerintah Kabupaten (Dinas PUPR & PRKP) yang dirancang untuk mempercepat pelaporan dan penanganan kerusakan fasilitas publik secara transparan dan akuntabel.\n\n🌟 **Fitur Unggulan Aplikasi:**\n• 🤖 **AI Computer Vision (YOLOv8):** Mendeteksi lubang jalan, retakan aspal, lampu PJU rusak, jalan mulus, serta memfilter foto yang tidak relevan secara otomatis.\n• 🛣️ **2 Tipe Pelaporan:** Kerusakan Ringan (< 500m, 1 titik GPS) dan Kerusakan Berat (≥ 500m, polyline rute jalan nyata 2 titik GPS).\n• 📍 **Geofencing Kabupaten:** Menjamin seluruh aduan berada di dalam batas administratif 15 kecamatan Kabupaten.\n• ⏱️ **Transparansi SLA:** Batas waktu penanganan terbuka (Jembatan darurat 24 jam, Jalan berlubang 48 jam, Lampu PJU 72 jam).\n• 🔒 **Perlindungan Privasi:** Nomor HP pelapor disamarkan untuk sesama warga demi menjaga keamanan data pribadi.\n• 👷‍♂️ **Integrasi Pekerja Lapangan:** Petugas URC langsung menerima SPK disposisi dan menuju lokasi via navigasi GIS terintegrasi.	[]	2026-09-24 03:26:16	2026-09-24 03:26:16
172	01a0cfbc-cead-709a-ac42-d2800ddbf279	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-24 03:27:32	2026-09-24 03:27:32
173	01a0cfbd-78ed-7212-b36b-7465de8fc948	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-24 03:28:16	2026-09-24 03:28:16
174	01a0cfbd-78ed-7212-b36b-7465de8fc948	USER	Apa Itu Pekerja Lapangan	\N	2026-09-24 03:29:01	2026-09-24 03:29:01
249	01a1ed7f-4a77-703b-9096-683e3b40f050	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 15:11:44	2026-11-18 15:11:44
175	01a0cfbd-78ed-7212-b36b-7465de8fc948	BOT	👷‍♂️ **Mengenal Peran Pekerja Lapangan (Dinas PUPR & PRKP):**\n\nPekerja Lapangan adalah **tim teknis operasional resmi** Pemerintah Kabupaten yang bertugas langsung di lokasi kejadian untuk menangani perbaikan fisik infrastruktur publik (jalan, jembatan, lampu penerangan jalan, drainase) berdasarkan Surat Perintah Kerja (SPK) dari Operator Dinas.\n\n🔑 **Wewenang & Fitur Khusus di Aplikasi Mobile:**\n1. **Menerima Disposisi Tugas:** Memantau SPK penugasan pada tab *Tugas Aktif* lengkap dengan data koordinat GPS satelit dan foto laporan warga.\n2. **Navigasi Rute Nyata GIS (OSRM):** Menghubungkan langsung ke rute jalan nyata menuju titik kerusakan.\n3. **Tombol 'Mulai Kerja Lapangan':** Menandai mulainya pengerjaan fisik di lokasi sekaligus memulai pencatatan waktu SLA secara transparan.\n4. **Akses Kontak Pelapor:** Petugas berwenang melihat nomor telepon pelapor untuk keperluan konfirmasi lokasi darurat di lapangan.\n5. **Kirim Bukti Selesai:** Mengambil foto fisik hasil perbaikan (*After-Work*) dan melampirkan catatan teknis material kepada Operator Dinas.\n\n⚠️ **Aturan Disiplin Petugas:**\n• **Batas Kuota:** Maksimal mengerjakan **3 tugas aktif** secara bersamaan agar mutu pekerjaan terjaga.\n• **Isolasi Hak Akses (Team Shield):** Hanya petugas/regu yang ditugaskan resmi yang berhak membuka modul kerja dan mengunggah bukti pengerjaan.\n• **Validasi Mutu:** Pekerjaan tidak langsung selesai tuntas, melainkan diverifikasi terlebih dahulu oleh Operator Dinas.	[]	2026-09-24 03:29:01	2026-09-24 03:29:01
176	01a0cfbe-dba7-711f-b7f4-d40524671d48	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-24 03:29:46	2026-09-24 03:29:46
177	01a0d431-db60-73d4-a600-9a432586b952	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-25 00:13:52	2026-09-25 00:13:52
178	01a0d43b-c186-7332-afe9-b3a9b6b7d17c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-25 00:24:41	2026-09-25 00:24:41
179	01a0d44e-3b82-73da-86e1-2ab9ccf4586c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-25 00:44:51	2026-09-25 00:44:51
180	01a0d450-472e-73e8-9756-529af800b239	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 00:47:06	2026-09-25 00:47:06
181	01a0d456-02ef-71d1-bbed-4c873a3e32bd	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 00:53:21	2026-09-25 00:53:21
182	01a0d458-3d16-7098-bfc8-826e7b9a6b7d	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-25 00:55:47	2026-09-25 00:55:47
183	01a0d485-662c-7088-97e0-1c0a6401a9b4	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 01:45:07	2026-09-25 01:45:07
184	01a0d485-662c-7088-97e0-1c0a6401a9b4	USER	Lupa / Ganti Password	\N	2026-09-25 01:54:39	2026-09-25 01:54:39
185	01a0d485-662c-7088-97e0-1c0a6401a9b4	BOT	🔑 **Panduan Mengubah & Mengatur Ulang Kata Sandi:**\n\n1. **Jika Anda Sudah Login ke Aplikasi:**\n   • Buka tab **Profil** di pojok kanan bawah aplikasi.\n   • Pilih menu **'Ubah Profil & Kata Sandi'**.\n   • Masukkan kata sandi lama Anda, kemudian masukkan kata sandi baru (minimal 8 karakter) dan konfirmasi kata sandi baru.\n   • Tekan tombol **'Simpan Perubahan'**.\n\n2. **Jika Anda Lupa Sandi (Belum Login):**\n   • Pada layar Login aplikasi mobile, tekan tautan **'Lupa Kata Sandi?'** di bawah kolom input sandi.\n   • Masukkan alamat email akun Anda yang terdaftar.\n   • Buka email Anda dan salin 6 digit **Kode OTP** yang dikirimkan sistem.\n   • Masukkan kode OTP tersebut di aplikasi mobile dan masukkan kata sandi baru Anda.\n\n👷‍♂️ **Catatan Khusus Pekerja Lapangan:**\nMeskipun akun Petugas dibuatkan oleh Superadmin Dinas PUPR, Anda dapat mengubah atau mereset kata sandi Anda sendiri kapan saja melalui fitur di atas!	[]	2026-09-25 01:54:39	2026-09-25 01:54:39
186	01a0d485-662c-7088-97e0-1c0a6401a9b4	USER	15 Kecamatan Kabupaten	\N	2026-09-25 01:54:51	2026-09-25 01:54:51
187	01a0d485-662c-7088-97e0-1c0a6401a9b4	BOT	📍 **Cakupan Wilayah Kabupaten & Geofencing:**\n\nSistem pengaduan ini beroperasi secara terpadu khusus untuk wilayah administratif **Kabupaten**, Sulawesi Tenggara, yang mencakup 15 kecamatan:\n1. Lasusua (Ibu Kota Kabupaten)\n2. Wawo\n3. Ngapa\n4. Kodeoha\n5. Pakue\n6. Pakue Tengah\n7. Pakue Utara\n8. Tiwu\n9. Tolala\n10. Ranteangin\n11. Lambai\n12. Katoi\n13. Porehu\n14. Watunohu\n15. Batu Putih\n\n🛡️ **Sistem Geofencing:** Koordinat GPS di luar batas wilayah Kabupaten akan otomatis diberi peringatan/ditolak sistem agar tindak lanjut dinas teknis tepat sasaran.	[]	2026-09-25 01:54:51	2026-09-25 01:54:51
188	01a0d4e9-c066-7104-a3fd-0266beef4f67	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 03:34:44	2026-09-25 03:34:44
189	01a0d545-11de-7352-9433-f2b5b6a1fe60	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 05:14:28	2026-09-25 05:14:28
190	01a0d54b-551f-7214-968e-82d593c15ae2	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 05:21:19	2026-09-25 05:21:19
191	01a0d553-0779-7114-b94e-55869f91b241	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 05:29:43	2026-09-25 05:29:43
192	01a0d57b-fd33-7319-bf28-acd77e0ae14c	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 06:14:27	2026-09-25 06:14:27
193	01a0d57c-f327-7059-8b75-eed4730a3fee	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 06:15:30	2026-09-25 06:15:30
194	01a0d749-ea59-7055-86aa-fdacf3eefd84	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 14:39:00	2026-09-25 14:39:00
195	01a0d7ad-253c-738b-8867-e1170abce2f4	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 16:27:23	2026-09-25 16:27:23
196	01a0d7eb-4929-7237-b3d3-f417a3a3ee28	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-25 17:35:16	2026-09-25 17:35:16
198	01a0edf7-a4c7-7204-9e6c-387e227b135b	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-30 00:20:24	2026-09-30 00:20:24
205	01a0ee2d-fb7f-703c-9b94-e84c3ab74a36	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-30 01:19:46	2026-09-30 01:19:46
208	01a0ee5d-6a17-71ee-9cb7-ac76843eb40d	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-30 02:11:34	2026-09-30 02:11:34
209	01a0ee7b-bbf0-73ce-bbbb-87d8511f8191	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-30 02:44:41	2026-09-30 02:44:41
213	01a0f148-366f-7094-a8bb-053a226a1e47	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-30 15:47:16	2026-09-30 15:47:16
214	01a0f16a-6554-72d9-8247-442dcc15e54f	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten AI Tim Teknis Lapangan** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-09-30 16:24:36	2026-09-30 16:24:36
215	01a0f172-baa6-7206-946d-c6bb01f2d29a	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-30 16:33:43	2026-09-30 16:33:43
217	01a0f172-baa6-7206-946d-c6bb01f2d29a	BOT	✉️ **Panduan Lengkap Verifikasi Kode OTP:**\n\nKode OTP (*One-Time Password*) adalah 6 digit angka rahasia yang dikirimkan ke email terdaftar untuk memastikan keamanan akun Anda.\n\n1. **Untuk Pendaftaran Akun Warga Baru:**\n   • Isi formulir pendaftaran (Nama, Email, Nomor Telepon, Kata Sandi).\n   • Sistem akan otomatis mengirimkan kode OTP 6 digit ke email Anda.\n   • Masukkan kode OTP pada halaman verifikasi di aplikasi mobile sebelum waktu kedaluwarsa (5 menit).\n   • Akun Anda langsung aktif dan resmi menjadi **Warga Terdaftar**.\n\n2. **Untuk Lupa / Reset Kata Sandi (Warga & Pekerja Lapangan):**\n   • Pada halaman login aplikasi, tekan **'Lupa Kata Sandi?'**.\n   • Masukkan alamat email akun terdaftar Anda.\n   • Cek kotak masuk (Inbox) atau folder Spam pada email Anda untuk melihat 6 digit kode OTP.\n   • Masukkan kode OTP di aplikasi mobile, lalu tentukan kata sandi baru Anda.\n\n💡 **Tips Keamanan OTP:**\n• Jangan pernah membagikan kode OTP kepada siapa pun, termasuk pihak yang mengatasnamakan dinas/petugas.\n• Jika kode tidak muncul dalam 1 menit, tekan tombol **'Kirim Ulang OTP'** di aplikasi.	[]	2026-09-30 16:37:13	2026-09-30 16:37:13
218	01a0f184-2645-739d-b949-56933581bf1c	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-30 16:52:44	2026-09-30 16:52:44
219	01a0f193-bfbb-71ba-a606-25630597fdec	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-09-30 17:09:47	2026-09-30 17:09:47
220	01a0f60f-b93a-7279-aaf4-da13056a8f51	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-01 14:03:40	2026-10-01 14:03:40
221	01a0f64b-14cf-7282-bad5-a8a2afc85694	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-01 15:08:30	2026-10-01 15:08:30
222	01a0f660-f96c-71dc-8365-edffb4452e82	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-01 15:32:25	2026-10-01 15:32:25
223	01a0f661-d66c-721a-be34-bc51c99b37c1	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-01 15:33:22	2026-10-01 15:33:22
224	01a0fcdb-a275-73b5-bb2b-d3e3ed90ed11	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-02 21:44:07	2026-10-02 21:44:07
225	01a120e4-7909-736a-b8fa-4a85a490208c	BOT	Halo! Saya Asisten AI Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-09 21:40:06	2026-10-09 21:40:06
226	01a120fa-afa1-7379-a953-2eb01cef7e50	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-09 22:04:22	2026-10-09 22:04:22
228	01a120fa-afa1-7379-a953-2eb01cef7e50	BOT	Maaf, nomor tiket aduan **aduan** tidak ditemukan di database. Pastikan format nomor tiket sudah tepat (contoh: ADU-0001 atau ADU-202609-0001).	[]	2026-10-09 22:05:37	2026-10-09 22:05:37
230	01a120fa-afa1-7379-a953-2eb01cef7e50	BOT	🤖 **Teknologi AI Computer Vision & Tips Foto Bukti:**\n\nAplikasi menggunakan multi-model AI Computer Vision (YOLOv8 ONNX) yang berjalan otomatis saat Anda mengunggah foto:\n• **Deteksi Kerusakan:** Mendeteksi lubang jalan (*potholes*), retakan jalan (*cracks, alligator cracking* RDD2022), tiang lampu PJU rusak, serta saluran drainase.\n• **Deteksi Jalan Mulus:** Mampu memverifikasi jika jalan sudah dalam kondisi baik (*Smooth Road*).\n• **Penyaring Cerdas:** Otomatis menolak foto non-infrastruktur seperti wajah orang/selfie, hewan, makanan, kendaraan, atau ruangan dalam rumah.\n\n📸 **Tips Mengambil Foto yang Baik:**\n1. Ambil foto di tempat terang (siang hari atau dengan pencahayaan cukup).\n2. Arahkan kamera langsung ke kerusakan jalan/fasilitas.\n3. Hindari mengambil foto wajah orang di depan kamera.\n4. Pastikan gambar tidak blur agar AI dapat mendeteksi dengan akurasi tinggi.	[]	2026-10-09 22:05:55	2026-10-09 22:05:55
231	01a133a2-297d-72b2-8b50-d4e542c16b15	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-13 13:00:27	2026-10-13 13:00:27
232	01a133b8-c81f-7210-90ea-e831c44f103e	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-13 13:25:10	2026-10-13 13:25:10
234	01a133b8-c81f-7210-90ea-e831c44f103e	BOT	📱 **Tentang Aplikasi Pengaduan Infrastruktur Kabupaten:**\n\nAplikasi ini merupakan platform digital terpadu milik Pemerintah Kabupaten (Dinas PUPR & PRKP) yang dirancang untuk mempercepat pelaporan dan penanganan kerusakan fasilitas publik secara transparan dan akuntabel.\n\n🌟 **Fitur Unggulan Aplikasi:**\n• 🤖 **AI Computer Vision (YOLOv8):** Mendeteksi lubang jalan, retakan aspal, lampu PJU rusak, jalan mulus, serta memfilter foto yang tidak relevan secara otomatis.\n• 🛣️ **2 Tipe Pelaporan:** Kerusakan Ringan (< 500m, 1 titik GPS) dan Kerusakan Berat (≥ 500m, polyline rute jalan nyata 2 titik GPS).\n• 📍 **Geofencing Kabupaten:** Menjamin seluruh aduan berada di dalam batas administratif 15 kecamatan Kabupaten.\n• ⏱️ **Transparansi SLA:** Batas waktu penanganan terbuka (Jembatan darurat 24 jam, Jalan berlubang 48 jam, Lampu PJU 72 jam).\n• 🔒 **Perlindungan Privasi:** Nomor HP pelapor disamarkan untuk sesama warga demi menjaga keamanan data pribadi.\n• 👷‍♂️ **Integrasi Pekerja Lapangan:** Petugas URC langsung menerima SPK disposisi dan menuju lokasi via navigasi GIS terintegrasi.	[]	2026-10-13 13:25:35	2026-10-13 13:25:35
235	01a133c0-1bf7-7176-8260-82ae2a70d5f4	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-13 13:33:10	2026-10-13 13:33:10
236	01a133c5-e0b6-725f-b117-7d4356de2485	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-13 13:39:28	2026-10-13 13:39:28
237	01a18d06-79e4-7097-a4cf-9ec0e6715d77	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-10-30 21:36:14	2026-10-30 21:36:14
238	01a18d07-7300-70dc-b2bb-18e7ddd21f9d	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-10-30 21:37:18	2026-10-30 21:37:18
239	01a19b75-ab32-709b-966c-972016bb7201	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 16:52:22	2026-11-02 16:52:22
240	01a19bb1-4692-71f8-8217-ca60754d83aa	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 17:57:28	2026-11-02 17:57:28
241	01a19bb3-7de9-70f7-ace5-20c157c35f67	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 17:59:54	2026-11-02 17:59:54
242	01a19bc4-a2e3-732f-b3f6-88d6a70e9cc8	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 18:18:37	2026-11-02 18:18:37
243	01a19c10-82b7-7353-9083-731d77d969b4	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 19:41:30	2026-11-02 19:41:30
244	01a19c50-896d-71e5-9203-9c26b505576b	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 20:51:26	2026-11-02 20:51:26
245	01a19c65-41ae-713e-a95f-b8f566dda6ee	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 21:14:04	2026-11-02 21:14:04
246	01a19c96-4684-7090-9ecc-d579bb38270a	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-02 22:07:36	2026-11-02 22:07:36
247	01a1aca6-eda6-718e-96a2-fee4e92724a6	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-06 00:59:43	2026-11-06 00:59:43
248	01a1ed7e-8d5d-7141-9296-1910e9618a8b	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 15:10:56	2026-11-18 15:10:56
250	01a1ed82-e382-7383-aa50-85869514917b	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 15:15:40	2026-11-18 15:15:40
251	01a1ed83-d2ef-71d4-89fe-6b9eed6d7fac	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 15:16:41	2026-11-18 15:16:41
252	01a1ed85-959f-72d9-8a2d-9c7073295520	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 15:18:37	2026-11-18 15:18:37
253	01a1ed86-3541-722a-a65e-0a9ed37544da	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:19:18	2026-11-18 15:19:18
254	01a1ed90-6cce-73ed-853d-6bcdef31a057	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:30:27	2026-11-18 15:30:27
255	01a1ed90-6cce-73ed-853d-6bcdef31a057	USER	Lupa / Ganti Password	\N	2026-11-18 15:31:37	2026-11-18 15:31:37
256	01a1ed90-6cce-73ed-853d-6bcdef31a057	BOT	🔑 **Panduan Mengubah & Mengatur Ulang Kata Sandi:**\n\n1. **Jika Anda Sudah Login ke Aplikasi:**\n   • Buka tab **Profil** di pojok kanan bawah aplikasi.\n   • Pilih menu **'Ubah Profil & Kata Sandi'**.\n   • Masukkan kata sandi lama Anda, kemudian masukkan kata sandi baru (minimal 8 karakter) dan konfirmasi kata sandi baru.\n   • Tekan tombol **'Simpan Perubahan'**.\n\n2. **Jika Anda Lupa Sandi (Belum Login):**\n   • Pada layar Login aplikasi mobile, tekan tautan **'Lupa Kata Sandi?'** di bawah kolom input sandi.\n   • Masukkan alamat email akun Anda yang terdaftar.\n   • Buka email Anda dan salin 6 digit **Kode OTP** yang dikirimkan sistem.\n   • Masukkan kode OTP tersebut di aplikasi mobile dan masukkan kata sandi baru Anda.\n\n👷‍♂️ **Catatan Khusus Pekerja Lapangan:**\nMeskipun akun Petugas dibuatkan oleh Superadmin Dinas PUPR, Anda dapat mengubah atau mereset kata sandi Anda sendiri kapan saja melalui fitur di atas!	[]	2026-11-18 15:31:37	2026-11-18 15:31:37
257	01a1ed92-0024-720a-9745-f034f75b8be7	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:32:10	2026-11-18 15:32:10
258	01a1ed99-4c93-71df-a4ee-48f206fadb1e	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:40:09	2026-11-18 15:40:09
259	01a1eda2-bcc6-7058-9b61-efef9ee9f707	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:50:27	2026-11-18 15:50:27
260	01a1eda2-bcc6-7058-9b61-efef9ee9f707	USER	Cara Verifikasi OTP	\N	2026-11-18 15:50:54	2026-11-18 15:50:54
261	01a1eda2-bcc6-7058-9b61-efef9ee9f707	BOT	✉️ **Panduan Lengkap Verifikasi Kode OTP:**\n\nKode OTP (*One-Time Password*) adalah 6 digit angka rahasia yang dikirimkan ke email terdaftar untuk memastikan keamanan akun Anda.\n\n1. **Untuk Pendaftaran Akun Warga Baru:**\n   • Isi formulir pendaftaran (Nama, Email, Nomor Telepon, Kata Sandi).\n   • Sistem akan otomatis mengirimkan kode OTP 6 digit ke email Anda.\n   • Masukkan kode OTP pada halaman verifikasi di aplikasi mobile sebelum waktu kedaluwarsa (5 menit).\n   • Akun Anda langsung aktif dan resmi menjadi **Warga Terdaftar**.\n\n2. **Untuk Lupa / Reset Kata Sandi (Warga & Pekerja Lapangan):**\n   • Pada halaman login aplikasi, tekan **'Lupa Kata Sandi?'**.\n   • Masukkan alamat email akun terdaftar Anda.\n   • Cek kotak masuk (Inbox) atau folder Spam pada email Anda untuk melihat 6 digit kode OTP.\n   • Masukkan kode OTP di aplikasi mobile, lalu tentukan kata sandi baru Anda.\n\n💡 **Tips Keamanan OTP:**\n• Jangan pernah membagikan kode OTP kepada siapa pun, termasuk pihak yang mengatasnamakan dinas/petugas.\n• Jika kode tidak muncul dalam 1 menit, tekan tombol **'Kirim Ulang OTP'** di aplikasi.	[]	2026-11-18 15:50:54	2026-11-18 15:50:54
262	01a1eda3-8837-70c5-90a2-59d9367213d7	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:51:19	2026-11-18 15:51:19
263	01a1eda6-d6d5-731b-80e8-d8eeecd3ab81	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:54:56	2026-11-18 15:54:56
264	01a1eda9-7794-72fd-89ae-4817f737a270	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 15:57:48	2026-11-18 15:57:48
265	01a1edb0-d3a1-728b-be96-f4dd403cb0b4	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 16:05:51	2026-11-18 16:05:51
266	01a1edb3-338e-70b4-8d74-fdf06a93c896	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 16:08:26	2026-11-18 16:08:26
352	01a1f3a9-952b-7259-93da-cc2c8c61d15e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 19:55:39	2026-11-19 19:55:39
268	01a1edb3-338e-70b4-8d74-fdf06a93c896	BOT	📱 **Tentang Aplikasi Pengaduan Infrastruktur Kabupaten:**\n\nAplikasi ini merupakan platform digital terpadu milik Pemerintah Kabupaten (Dinas PUPR & PRKP) yang dirancang untuk mempercepat pelaporan dan penanganan kerusakan fasilitas publik secara transparan dan akuntabel.\n\n🌟 **Fitur Unggulan Aplikasi:**\n• 🤖 **AI Computer Vision (YOLOv8):** Mendeteksi lubang jalan, retakan aspal, lampu PJU rusak, jalan mulus, serta memfilter foto yang tidak relevan secara otomatis.\n• 🛣️ **2 Tipe Pelaporan:** Kerusakan Ringan (< 500m, 1 titik GPS) dan Kerusakan Berat (≥ 500m, polyline rute jalan nyata 2 titik GPS).\n• 📍 **Geofencing Kabupaten:** Menjamin seluruh aduan berada di dalam batas administratif 15 kecamatan Kabupaten.\n• ⏱️ **Transparansi SLA:** Batas waktu penanganan terbuka (Jembatan darurat 24 jam, Jalan berlubang 48 jam, Lampu PJU 72 jam).\n• 🔒 **Perlindungan Privasi:** Nomor HP pelapor disamarkan untuk sesama warga demi menjaga keamanan data pribadi.\n• 👷‍♂️ **Integrasi Pekerja Lapangan:** Petugas URC langsung menerima SPK disposisi dan menuju lokasi via navigasi GIS terintegrasi.	[]	2026-11-18 16:11:57	2026-11-18 16:11:57
269	01a1edc1-9728-725c-9fbe-f59dff0c531a	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 16:24:09	2026-11-18 16:24:09
270	01a1eed7-a9d3-707b-9701-f9f29209fcc3	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 21:27:53	2026-11-18 21:27:53
271	01a1eee4-6db6-701c-9a99-f2f2c602abc5	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 21:41:50	2026-11-18 21:41:50
272	01a1eee5-5208-709a-b27e-21e534a92d04	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 21:42:48	2026-11-18 21:42:48
273	01a1ef00-e4c8-723a-bc41-4622a549ddbd	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 22:12:55	2026-11-18 22:12:55
274	01a1ef01-94d5-739f-88d2-864ccdc037d3	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 22:13:40	2026-11-18 22:13:40
275	01a1ef13-60ad-71cc-adc3-b9115d58c0d1	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 22:33:07	2026-11-18 22:33:07
276	01a1ef14-d4be-733b-b11e-d31946908e98	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 22:34:42	2026-11-18 22:34:42
277	01a1ef1a-4629-73c5-80fe-5497d066d67d	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 22:40:38	2026-11-18 22:40:38
278	01a1ef1a-ae9f-7215-bab2-1da2940fc4fb	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 22:41:05	2026-11-18 22:41:05
279	01a1ef1b-8a03-7090-ae64-3ecbcc2ce61c	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 22:42:01	2026-11-18 22:42:01
280	01a1ef31-413b-7246-8404-8b859ec539f2	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 23:05:45	2026-11-18 23:05:45
281	01a1ef31-413b-7246-8404-8b859ec539f2	USER	Tips Foto AI Computer Vision	\N	2026-11-18 23:09:09	2026-11-18 23:09:09
282	01a1ef31-413b-7246-8404-8b859ec539f2	BOT	🤖 **Teknologi AI Computer Vision & Tips Foto Bukti:**\n\nAplikasi menggunakan multi-model AI Computer Vision (YOLOv8 ONNX) yang berjalan otomatis saat Anda mengunggah foto:\n• **Deteksi Kerusakan:** Mendeteksi lubang jalan (*potholes*), retakan jalan (*cracks, alligator cracking* RDD2022), tiang lampu PJU rusak, serta saluran drainase.\n• **Deteksi Jalan Mulus:** Mampu memverifikasi jika jalan sudah dalam kondisi baik (*Smooth Road*).\n• **Penyaring Cerdas:** Otomatis menolak foto non-infrastruktur seperti wajah orang/selfie, hewan, makanan, kendaraan, atau ruangan dalam rumah.\n\n📸 **Tips Mengambil Foto yang Baik:**\n1. Ambil foto di tempat terang (siang hari atau dengan pencahayaan cukup).\n2. Arahkan kamera langsung ke kerusakan jalan/fasilitas.\n3. Hindari mengambil foto wajah orang di depan kamera.\n4. Pastikan gambar tidak blur agar AI dapat mendeteksi dengan akurasi tinggi.	[]	2026-11-18 23:09:09	2026-11-18 23:09:09
283	01a1ef31-413b-7246-8404-8b859ec539f2	USER	15 Kecamatan Kabupaten	\N	2026-11-18 23:09:30	2026-11-18 23:09:30
284	01a1ef31-413b-7246-8404-8b859ec539f2	BOT	📍 **Cakupan Wilayah Kabupaten & Geofencing:**\n\nSistem pengaduan ini beroperasi secara terpadu khusus untuk wilayah administratif **Kabupaten**, Sulawesi Tenggara, yang mencakup 15 kecamatan:\n1. Lasusua (Ibu Kota Kabupaten)\n2. Wawo\n3. Ngapa\n4. Kodeoha\n5. Pakue\n6. Pakue Tengah\n7. Pakue Utara\n8. Tiwu\n9. Tolala\n10. Ranteangin\n11. Lambai\n12. Katoi\n13. Porehu\n14. Watunohu\n15. Batu Putih\n\n🛡️ **Sistem Geofencing:** Koordinat GPS di luar batas wilayah Kabupaten akan otomatis diberi peringatan/ditolak sistem agar tindak lanjut dinas teknis tepat sasaran.	[]	2026-11-18 23:09:30	2026-11-18 23:09:30
285	01a1ef31-413b-7246-8404-8b859ec539f2	USER	apakah kau single	\N	2026-11-18 23:10:16	2026-11-18 23:10:16
286	01a1ef31-413b-7246-8404-8b859ec539f2	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	[]	2026-11-18 23:10:16	2026-11-18 23:10:16
287	01a1ef3b-2883-7329-adeb-1cd6c217dc73	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 23:16:34	2026-11-18 23:16:34
288	01a1ef3d-4f9a-7142-8330-7b95fbd965c9	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 23:18:55	2026-11-18 23:18:55
289	01a1ef45-3a00-732e-989f-de6a02c53e96	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 23:27:33	2026-11-18 23:27:33
290	01a1ef45-8224-73dc-a16f-fa113e8b3bef	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-18 23:27:52	2026-11-18 23:27:52
291	01a1efaf-7951-73da-941a-51f38f8f483f	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 01:23:36	2026-11-19 01:23:36
292	01a1efb2-9be9-72c9-8b89-a902a426a021	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 01:27:02	2026-11-19 01:27:02
293	01a1efb4-7d65-7280-957d-bd02e6eae850	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 01:29:05	2026-11-19 01:29:05
353	01a1f7d4-d8df-73af-a8a6-325cbfa67fd8	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-20 15:21:23	2026-11-20 15:21:23
295	01a1efb4-7d65-7280-957d-bd02e6eae850	BOT	👷‍♂️ **Mengenal Peran Pekerja Lapangan (Dinas PUPR & PRKP):**\n\nPekerja Lapangan adalah **tim teknis operasional resmi** Pemerintah Kabupaten yang bertugas langsung di lokasi kejadian untuk menangani perbaikan fisik infrastruktur publik (jalan, jembatan, lampu penerangan jalan, drainase) berdasarkan Surat Perintah Kerja (SPK) dari Operator Dinas.\n\n🔑 **Wewenang & Fitur Khusus di Aplikasi Mobile:**\n1. **Menerima Disposisi Tugas:** Memantau SPK penugasan pada tab *Tugas Aktif* lengkap dengan data koordinat GPS satelit dan foto laporan warga.\n2. **Navigasi Rute Nyata GIS (OSRM):** Menghubungkan langsung ke rute jalan nyata menuju titik kerusakan.\n3. **Tombol 'Mulai Kerja Lapangan':** Menandai mulainya pengerjaan fisik di lokasi sekaligus memulai pencatatan waktu SLA secara transparan.\n4. **Akses Kontak Pelapor:** Petugas berwenang melihat nomor telepon pelapor untuk keperluan konfirmasi lokasi darurat di lapangan.\n5. **Kirim Bukti Selesai:** Mengambil foto fisik hasil perbaikan (*After-Work*) dan melampirkan catatan teknis material kepada Operator Dinas.\n\n⚠️ **Aturan Disiplin Petugas:**\n• **Batas Kuota:** Maksimal mengerjakan **3 tugas aktif** secara bersamaan agar mutu pekerjaan terjaga.\n• **Isolasi Hak Akses (Team Shield):** Hanya petugas/regu yang ditugaskan resmi yang berhak membuka modul kerja dan mengunggah bukti pengerjaan.\n• **Validasi Mutu:** Pekerjaan tidak langsung selesai tuntas, melainkan diverifikasi terlebih dahulu oleh Operator Dinas.	[]	2026-11-19 01:29:39	2026-11-19 01:29:39
296	01a1efc2-f6cf-7132-9f62-3c0f71532efc	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 01:44:54	2026-11-19 01:44:54
297	01a1f23d-0363-7389-8241-8e04e4b5cc9d	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 13:17:27	2026-11-19 13:17:27
298	01a1f25c-96db-70e7-9e72-7a22864fd3e5	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 13:51:56	2026-11-19 13:51:56
299	01a1f25d-9980-725f-8548-b014d6d33221	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 13:53:02	2026-11-19 13:53:02
300	01a1f25f-2227-70bc-8c84-d463b351528b	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 13:54:43	2026-11-19 13:54:43
301	01a1f270-7f46-7051-8d9f-e9fa5a3f907e	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 14:13:41	2026-11-19 14:13:41
302	01a1f273-5cb8-71cf-8e69-6582af2552e6	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 14:16:49	2026-11-19 14:16:49
303	01a1f28a-a32a-7347-84fa-43aafd84c029	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 14:42:14	2026-11-19 14:42:14
304	01a1f28a-e873-7277-a3a3-760009a4f18e	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 14:42:32	2026-11-19 14:42:32
305	01a1f2a4-6cff-72a8-b3b6-bf1ba46564cf	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 15:10:24	2026-11-19 15:10:24
306	01a1f2a8-6a21-72c2-817b-59e79741904f	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 15:14:45	2026-11-19 15:14:45
307	01a1f2c3-d118-71aa-abe4-0464e8d880e4	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 15:44:41	2026-11-19 15:44:41
308	01a1f2c3-d690-72dd-8b3c-3653a6cbe178	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 15:44:43	2026-11-19 15:44:43
309	01a1f2c7-873e-71d6-9efd-14f5b787b585	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 15:48:45	2026-11-19 15:48:45
310	01a1f2cd-47cb-73aa-bdd5-ea3fb4de8b03	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 15:55:01	2026-11-19 15:55:01
311	01a1f2ce-7699-7031-934e-24c03433687a	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 15:56:19	2026-11-19 15:56:19
312	01a1f2d5-71f5-729a-9552-c0a97bd5ce64	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 16:03:57	2026-11-19 16:03:57
313	01a1f2d7-fd47-7232-9386-bff96ffb7a04	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:06:43	2026-11-19 16:06:43
314	01a1f2e6-39d5-73b9-9327-541a5686fe97	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:22:16	2026-11-19 16:22:16
315	01a1f2e6-3b95-7316-834a-27f448e96b79	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 16:22:17	2026-11-19 16:22:17
316	01a1f2e7-744d-72d5-8ff5-8e86c91fbf42	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:23:37	2026-11-19 16:23:37
317	01a1f2e9-360a-734b-b688-1e7d55ade76e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:25:32	2026-11-19 16:25:32
318	01a1f2ef-6d89-7152-b1de-9b5d6b76f0ab	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:32:19	2026-11-19 16:32:19
319	01a1f2f0-e890-723d-848b-b3a759488e43	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:33:56	2026-11-19 16:33:56
320	01a1f2f1-340d-71a3-b1a9-031f1cae847e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:34:16	2026-11-19 16:34:16
321	01a1f2f2-067f-7349-8a2e-9687a0c56706	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:35:10	2026-11-19 16:35:10
322	01a1f2f2-513d-723a-9240-1e9d20c0ae3e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:35:29	2026-11-19 16:35:29
323	01a1f2f7-9c47-733d-b2ce-bc4f3dc2ad3b	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:41:16	2026-11-19 16:41:16
324	01a1f2f8-8b40-731f-a557-7a9e20092531	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:42:17	2026-11-19 16:42:17
325	01a1f2fb-2abb-73c6-bd14-d82136989bcd	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:45:09	2026-11-19 16:45:09
326	01a1f2fc-108b-727d-98ed-162b6318d24d	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 16:46:08	2026-11-19 16:46:08
327	01a1f30a-b304-7208-900c-b2aa273477ff	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:02:07	2026-11-19 17:02:07
328	01a1f30c-6335-73eb-802e-e178df54b5a3	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:03:57	2026-11-19 17:03:57
329	01a1f31a-112a-703e-9c19-7a1c22abda67	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:18:54	2026-11-19 17:18:54
330	01a1f31c-f6f5-70e2-8a2d-5ec4fbeebdd3	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:22:04	2026-11-19 17:22:04
331	01a1f325-d4f6-7130-b58b-f04c9fc56edb	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:31:45	2026-11-19 17:31:45
332	01a1f326-0f6d-718d-a0c5-1a9fec160e01	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:32:00	2026-11-19 17:32:00
333	01a1f327-ca70-733b-a47f-d25fdb996b2a	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 17:33:53	2026-11-19 17:33:53
334	01a1f32a-27e8-733a-80dd-ec98d489e4e2	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:36:28	2026-11-19 17:36:28
335	01a1f32a-74af-70a3-b334-f05912754a46	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 17:36:48	2026-11-19 17:36:48
336	01a1f32c-4320-7348-b239-42d5a3d34190	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:38:46	2026-11-19 17:38:46
337	01a1f32f-27f6-71e8-a228-e41d9a9c8049	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 17:41:56	2026-11-19 17:41:56
338	01a1f332-39f5-7044-94ec-2ca0d7e8e9a1	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 17:45:17	2026-11-19 17:45:17
339	01a1f336-065a-7080-b10a-32b0928267be	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:49:26	2026-11-19 17:49:26
340	01a1f339-4f3e-7267-b683-bf3dbe3a0c66	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:53:01	2026-11-19 17:53:01
341	01a1f33b-3886-71cf-82db-01693d088135	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 17:55:07	2026-11-19 17:55:07
342	01a1f341-4140-7176-b0d4-338160271a02	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-19 18:01:42	2026-11-19 18:01:42
343	01a1f341-ca3d-72e5-b1e2-6814c1407562	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 18:02:17	2026-11-19 18:02:17
344	01a1f34a-293d-722d-bf93-825f27725e7b	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 18:11:26	2026-11-19 18:11:26
345	01a1f34a-ceab-7229-aec9-c588a71a18b1	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 18:12:08	2026-11-19 18:12:08
346	01a1f34b-d271-7317-86d9-b0cf0fd85d30	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 18:13:15	2026-11-19 18:13:15
347	01a1f352-b366-71d2-a5ce-5322d69d7c55	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 18:20:45	2026-11-19 18:20:45
348	01a1f35b-08de-737f-9d7c-8fd90f553672	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 18:29:51	2026-11-19 18:29:51
349	01a1f35d-249c-715c-9cba-ebef1ebe1099	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 18:32:10	2026-11-19 18:32:10
350	01a1f38e-066d-7034-890c-c292305d2b44	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 19:25:33	2026-11-19 19:25:33
351	01a1f38f-f615-7016-b1b8-51cfd189fe4d	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-19 19:27:40	2026-11-19 19:27:40
354	01a1f7e2-9035-726a-a6fa-14be96bbb27a	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 15:36:22	2026-11-20 15:36:22
360	01a1f953-8755-769f-83fc-b8ea9201bdb2	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 22:19:23	2026-11-20 22:19:23
368	01a1f967-e5fe-7326-9b8c-0855a284396a	BOT	📱 **Tentang Aplikasi Pengaduan Infrastruktur Kabupaten:**\n\nAplikasi ini merupakan platform digital terpadu milik Pemerintah Kabupaten (Dinas PUPR & PRKP) yang dirancang untuk mempercepat pelaporan dan penanganan kerusakan fasilitas publik secara transparan dan akuntabel.\n\n🌟 **Fitur Unggulan Aplikasi:**\n• 🤖 **AI Computer Vision (YOLOv8):** Mendeteksi lubang jalan, retakan aspal, lampu PJU rusak, jalan mulus, serta memfilter foto yang tidak relevan secara otomatis.\n• 🛣️ **2 Tipe Pelaporan:** Kerusakan Ringan (< 500m, 1 titik GPS) dan Kerusakan Berat (≥ 500m, polyline rute jalan nyata 2 titik GPS).\n• 📍 **Geofencing Kabupaten:** Menjamin seluruh aduan berada di dalam batas administratif 15 kecamatan Kabupaten.\n• ⏱️ **Transparansi SLA:** Batas waktu penanganan terbuka (Jembatan darurat 24 jam, Jalan berlubang 48 jam, Lampu PJU 72 jam).\n• 🔒 **Perlindungan Privasi:** Nomor HP pelapor disamarkan untuk sesama warga demi menjaga keamanan data pribadi.\n• 👷‍♂️ **Integrasi Pekerja Lapangan:** Petugas URC langsung menerima SPK disposisi dan menuju lokasi via navigasi GIS terintegrasi.	[]	2026-11-20 22:45:51	2026-11-20 22:45:51
374	01a1f987-530e-7d58-8a66-a0429a0427c5	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 23:15:57	2026-11-20 23:15:57
355	01a1f805-44a0-7142-8fd5-3a618aeb9566	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-20 16:14:17	2026-11-20 16:14:17
361	01a1f95a-4d1c-7595-818a-0eb501ed523d	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-20 22:26:47	2026-11-20 22:26:47
369	01a1f96c-f8da-70f1-a715-fdf38bccc584	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 22:47:10	2026-11-20 22:47:10
375	01a1f988-2088-7760-bd56-46022482ab09	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-20 23:16:50	2026-11-20 23:16:50
356	01a1f822-141d-7943-a490-acae8ae8cd15	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 16:45:45	2026-11-20 16:45:45
362	01a1f95f-b97e-79ae-95ca-a8cfe78c35a6	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 22:32:42	2026-11-20 22:32:42
370	01a1f96e-14cb-7116-9916-fe1630d9230d	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-20 22:48:23	2026-11-20 22:48:23
376	01a1f98a-27ad-73ca-86a7-20d4865af185	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 23:19:03	2026-11-20 23:19:03
357	01a1f846-53b5-7c20-be98-b88c437ddb22	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 17:25:20	2026-11-20 17:25:20
363	01a1f967-c06a-7b02-9a76-0c8193f84ad7	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 22:41:28	2026-11-20 22:41:28
371	01a1f96e-c035-7ad9-974d-2ef7943f54ac	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 22:49:07	2026-11-20 22:49:07
358	01a1f868-3f64-7c83-971c-23991e3ef4af	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 18:02:23	2026-11-20 18:02:23
364	01a1f967-e5fe-7326-9b8c-0855a284396a	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 22:41:38	2026-11-20 22:41:38
372	01a1f96f-ec8a-7a72-939f-b1dab20fce4e	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-20 22:50:24	2026-11-20 22:50:24
359	01a1f94e-36f9-708f-aa42-3d39beb1f62c	BOT	Halo! Saya **Asisten SI-APPIK** Kabupaten. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kabupaten\n• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\nAda yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kabupaten?	\N	2026-11-20 22:13:35	2026-11-20 22:13:35
365	01a1f967-e5fe-7326-9b8c-0855a284396a	USER	Tentang Aplikasi	\N	2026-11-20 22:45:49	2026-11-20 22:45:49
366	01a1f967-e5fe-7326-9b8c-0855a284396a	BOT	📱 **Tentang Aplikasi Pengaduan Infrastruktur Kabupaten:**\n\nAplikasi ini merupakan platform digital terpadu milik Pemerintah Kabupaten (Dinas PUPR & PRKP) yang dirancang untuk mempercepat pelaporan dan penanganan kerusakan fasilitas publik secara transparan dan akuntabel.\n\n🌟 **Fitur Unggulan Aplikasi:**\n• 🤖 **AI Computer Vision (YOLOv8):** Mendeteksi lubang jalan, retakan aspal, lampu PJU rusak, jalan mulus, serta memfilter foto yang tidak relevan secara otomatis.\n• 🛣️ **2 Tipe Pelaporan:** Kerusakan Ringan (< 500m, 1 titik GPS) dan Kerusakan Berat (≥ 500m, polyline rute jalan nyata 2 titik GPS).\n• 📍 **Geofencing Kabupaten:** Menjamin seluruh aduan berada di dalam batas administratif 15 kecamatan Kabupaten.\n• ⏱️ **Transparansi SLA:** Batas waktu penanganan terbuka (Jembatan darurat 24 jam, Jalan berlubang 48 jam, Lampu PJU 72 jam).\n• 🔒 **Perlindungan Privasi:** Nomor HP pelapor disamarkan untuk sesama warga demi menjaga keamanan data pribadi.\n• 👷‍♂️ **Integrasi Pekerja Lapangan:** Petugas URC langsung menerima SPK disposisi dan menuju lokasi via navigasi GIS terintegrasi.	[]	2026-11-20 22:45:49	2026-11-20 22:45:49
373	01a1f977-d465-7cd0-9c25-5491082eeae5	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-20 22:59:02	2026-11-20 22:59:02
\.


--
-- Data for Name: chat_sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chat_sessions (id, user_id, current_state, draft_json, is_completed, created_at, updated_at) FROM stdin;
01a061ca-8cf2-703e-bbf4-0b2b81143da0	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 11:04:19	2026-09-02 11:04:19
01a061cb-d7dc-7325-98fd-854e7cd74117	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 11:05:44	2026-09-02 11:05:44
01a06287-53c6-7175-9866-5715609473b9	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 14:30:31	2026-09-02 14:30:31
01a06287-a794-71ab-ae1f-3f288780ff76	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 14:30:52	2026-09-02 14:30:52
01a06288-0f11-737c-b21b-5d6257bf550f	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 14:31:19	2026-09-02 14:31:19
01a06288-52a2-739f-9410-b4ce00816980	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 14:31:36	2026-09-02 14:31:36
01a06297-4077-71e4-9213-6c854f2899db	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 14:47:54	2026-09-02 14:47:54
01a0629f-ea40-722d-863d-83f6b6a0c573	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 14:57:22	2026-09-02 14:57:22
01a062a0-3bbb-735f-948c-090b10439aa6	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 14:57:43	2026-09-02 14:57:43
01a062a2-85ba-7006-b5fd-35f1ed9be56a	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 15:00:13	2026-09-02 15:00:13
01a062a4-e1a1-73cb-baa0-cc73dc0ec9b1	1	IDENTIFY_CATEGORY	[]	f	2026-09-02 15:02:47	2026-09-02 15:02:48
01a08239-9791-705f-b9d4-f1e72e7adb35	1	IDENTIFY_CATEGORY	[]	f	2026-09-09 02:13:27	2026-09-09 02:15:43
01a0824d-94aa-705e-92cf-95310ba365f3	1	START	[]	f	2026-09-09 02:35:17	2026-09-09 02:35:17
01a08252-fcbb-7371-ba4d-6844bb8737f1	1	START	[]	f	2026-09-09 02:41:11	2026-09-09 02:41:11
01a062a6-583f-71d7-98f1-14f7a9f7458b	1	CONFIRM	{"category":"Cara lapor jalan rusak?","description":"kerusakan nya lumayan parah","address":"di lasusua"}	f	2026-09-02 15:04:23	2026-09-02 15:10:42
01a062b6-674b-7220-a598-57b9abe1a642	1	START	[]	f	2026-09-02 15:21:56	2026-09-02 15:21:56
01a062bb-d2ce-716d-b553-4bfbf65f09ae	1	START	[]	f	2026-09-02 15:27:51	2026-09-02 15:27:51
01a062ca-c753-71e0-8d04-f89f452316af	1	START	[]	f	2026-09-02 15:44:11	2026-09-02 15:44:11
01a062ce-faf1-70b5-ac90-f2b34de7eae3	1	START	[]	f	2026-09-02 15:48:46	2026-09-02 15:48:46
01a062cf-ccea-7370-94c8-97b1d7feaa30	1	START	[]	f	2026-09-02 15:49:40	2026-09-02 15:49:40
01a062d0-6634-70fd-8d18-9a7242e77863	1	START	[]	f	2026-09-02 15:50:19	2026-09-02 15:50:19
01a062d3-65ab-7262-bee0-1a7ba2f80c4d	1	START	[]	f	2026-09-02 15:53:36	2026-09-02 15:53:36
01a062d6-8ee5-717b-b735-fb91c962a352	1	START	[]	f	2026-09-02 15:57:03	2026-09-02 15:57:03
01a062d9-3139-73d8-9bb2-863a64968b2e	1	START	[]	f	2026-09-02 15:59:56	2026-09-02 15:59:56
01a062f2-b2b6-70fb-8ad9-048b750dc51e	1	START	[]	f	2026-09-02 16:27:47	2026-09-02 16:27:47
01a062f8-2caf-72d3-8235-472debef8742	1	START	[]	f	2026-09-02 16:33:46	2026-09-02 16:33:46
01a062f9-9010-7229-a044-ac6df46f094d	1	START	[]	f	2026-09-02 16:35:17	2026-09-02 16:35:17
01a062fc-9d42-73e9-9408-335a6e850138	1	START	[]	f	2026-09-02 16:38:37	2026-09-02 16:38:37
01a06323-bcfa-72d4-81d2-8c08d0157979	1	START	[]	f	2026-09-02 17:21:21	2026-09-02 17:21:21
01a06330-ef04-703f-8fca-1098a8ca20cd	1	START	[]	f	2026-09-02 17:35:46	2026-09-02 17:35:46
01a06349-4ad2-73a7-bb94-faceb4e30e5a	1	START	[]	f	2026-09-02 18:02:22	2026-09-02 18:02:22
01a06349-f400-71a9-888e-a9ef3c0a1db4	1	START	[]	f	2026-09-02 18:03:06	2026-09-02 18:03:06
01a0634b-f280-724c-90f9-0179380e07d9	1	START	[]	f	2026-09-02 18:05:16	2026-09-02 18:05:16
01a06356-7c28-7147-a8f9-c6b45b537ffc	1	START	[]	f	2026-09-02 18:16:47	2026-09-02 18:16:47
01a06359-0181-732a-8430-1b55c737120a	1	START	[]	f	2026-09-02 18:19:32	2026-09-02 18:19:32
01a0636d-cc00-7367-93c4-313aefebddb3	1	START	[]	f	2026-09-02 18:42:15	2026-09-02 18:42:15
01a06378-f380-7267-9c7f-559a09d1394e	1	START	[]	f	2026-09-02 18:54:26	2026-09-02 18:54:26
01a0637b-a0c6-707f-b2b5-c5cf1b5fef43	1	START	[]	f	2026-09-02 18:57:21	2026-09-02 18:57:21
01a06382-a0ca-7350-9d53-173076e74496	1	START	[]	f	2026-09-02 19:05:00	2026-09-02 19:05:00
01a066f4-e70e-71f0-b118-9d0271c2e37e	1	START	[]	f	2026-09-03 11:08:41	2026-09-03 11:08:41
01a066f6-2eda-70bf-b480-c4d242148e82	1	START	[]	f	2026-09-03 11:10:05	2026-09-03 11:10:05
01a06776-6e6f-7323-8134-83e1b25e3ee7	1	START	[]	f	2026-09-03 13:30:09	2026-09-03 13:30:09
01a0677e-1abc-7392-b7bc-824335eae2f2	1	START	[]	f	2026-09-03 13:38:32	2026-09-03 13:38:32
01a067bd-da6f-7026-b4e1-ed65e3364598	1	START	[]	f	2026-09-03 14:48:10	2026-09-03 14:48:10
01a06810-768b-710e-811c-fe6066125e27	1	START	[]	f	2026-09-03 16:18:24	2026-09-03 16:18:24
01a06861-125a-738b-8208-b87b8f540957	1	START	[]	f	2026-09-03 17:46:27	2026-09-03 17:46:27
01a06872-d058-7054-a620-0f5f67d16acf	1	START	[]	f	2026-09-03 18:05:50	2026-09-03 18:05:50
01a068ab-429d-7093-b5e7-2cb8887f30ab	1	START	[]	f	2026-09-04 03:07:29	2026-09-04 03:07:29
01a068e5-f80c-7280-acfb-f4c2ca71e3e3	1	START	[]	f	2026-09-04 04:11:36	2026-09-04 04:11:36
01a068e7-cd33-7334-921b-9c5483decce1	1	START	[]	f	2026-09-04 04:13:36	2026-09-04 04:13:36
01a068ef-7d55-7223-a819-04e9f73a7427	1	START	[]	f	2026-09-04 04:22:00	2026-09-04 04:22:00
01a068ff-b2e4-70c0-a2fe-b1a1e25b70d1	1	START	[]	f	2026-09-04 04:39:43	2026-09-04 04:39:43
01a06905-b82e-70af-8d8e-81a7069983b0	1	START	[]	f	2026-09-04 04:46:17	2026-09-04 04:46:17
01a06909-4e8e-734d-8bec-b0a0ab86b838	1	START	[]	f	2026-09-04 04:50:12	2026-09-04 04:50:12
01a06912-6038-7210-8895-03dc2ecc5ccc	1	START	[]	f	2026-09-04 05:00:07	2026-09-04 05:00:07
01a06919-fa06-73f1-a1fb-0d1f857a179f	1	START	[]	f	2026-09-04 05:08:25	2026-09-04 05:08:25
01a06ae6-c5af-7104-bb96-502ea152cf66	1	START	[]	f	2026-09-04 13:31:43	2026-09-04 13:31:43
01a06aec-666f-70ec-be88-249402c01d55	1	START	[]	f	2026-09-04 13:37:52	2026-09-04 13:37:52
01a06b24-2a06-711b-abc5-6af9676d7009	1	START	[]	f	2026-09-04 14:38:47	2026-09-04 14:38:47
01a06b27-cece-7376-908b-317959f649f5	1	START	[]	f	2026-09-04 14:42:46	2026-09-04 14:42:46
01a080db-0228-71a9-9497-ebdaefae90b0	1	START	[]	f	2026-09-08 19:50:31	2026-09-08 19:50:31
01a08106-8385-72c2-a8ce-68eae09256a1	1	START	[]	f	2026-09-08 20:38:02	2026-09-08 20:38:02
01a08146-e558-7247-b0c4-10598706fe49	1	START	[]	f	2026-09-08 21:48:22	2026-09-08 21:48:22
01a08154-e98c-7187-9037-6c0a2ea719d5	1	START	[]	f	2026-09-08 22:03:40	2026-09-08 22:03:40
01a08166-0b98-7306-b200-b05477cb3db9	1	START	[]	f	2026-09-08 22:22:23	2026-09-08 22:22:23
01a08169-9d7b-70d1-96b7-c8787dbc7d60	1	START	[]	f	2026-09-08 22:26:17	2026-09-08 22:26:17
01a0817c-b7d2-723f-80a9-bec28304ff08	1	START	[]	f	2026-09-08 22:47:09	2026-09-08 22:47:09
01a081ac-cc9f-700e-a3b5-59581f4c03cd	1	START	[]	f	2026-09-08 23:39:40	2026-09-08 23:39:40
01a081cd-ca9e-7046-8597-3e4d64e48ded	1	START	[]	f	2026-09-09 00:15:42	2026-09-09 00:15:42
01a0821d-1888-735b-9f6e-ebc2f4b311d0	1	START	[]	f	2026-09-09 01:42:20	2026-09-09 01:42:20
01a08231-68f2-721e-b8db-1a3356a40a9e	1	START	[]	f	2026-09-09 02:04:31	2026-09-09 02:04:31
01a08254-9d0b-71a0-ba6c-e3eefb2bfeb7	1	START	[]	f	2026-09-09 02:42:58	2026-09-09 02:42:58
01a08254-fd35-7171-91b7-cae7c4ea172c	1	START	[]	f	2026-09-09 02:43:23	2026-09-09 02:43:23
01a08257-a6bd-73b6-92fb-91680a90e683	1	START	[]	f	2026-09-09 02:46:17	2026-09-09 02:46:17
01a0825b-1753-7367-817b-27468ccdfc01	1	START	[]	f	2026-09-09 02:50:02	2026-09-09 02:50:02
01a0825c-bd6c-730c-ab80-119a93b71ff5	1	START	[]	f	2026-09-09 02:51:51	2026-09-09 02:51:51
01a08274-b268-7297-8450-a73f80b7e376	1	START	[]	f	2026-09-09 03:18:01	2026-09-09 03:18:01
01a08279-8787-713d-a909-216898dfe306	1	START	[]	f	2026-09-09 03:23:17	2026-09-09 03:23:17
01a08284-b2fd-7055-9d29-47e888b0fe32	1	START	[]	f	2026-09-09 03:35:29	2026-09-09 03:35:29
01a08285-6715-70e2-8df8-5b4b05980558	1	IDENTIFY_TYPE	[]	f	2026-09-09 03:36:15	2026-09-09 03:36:39
01a082a1-9979-73f1-b356-790c3a589a97	1	START	[]	f	2026-09-09 04:07:03	2026-09-09 04:07:03
01a082a7-f955-708e-aaca-dc17986a34d6	1	START	[]	f	2026-09-09 04:14:01	2026-09-09 04:14:01
01a082a8-947a-73c6-9da6-b5208d34f35c	1	START	[]	f	2026-09-09 04:14:41	2026-09-09 04:14:41
01a082ab-72b9-7124-99a0-9751611c4655	1	START	[]	f	2026-09-09 04:17:49	2026-09-09 04:17:49
01a082ab-cb5e-7183-9427-5a6a028d8ea8	1	START	[]	f	2026-09-09 04:18:11	2026-09-09 04:18:11
01a0c931-db75-7024-b7c1-bf5afdd4856c	1	START	[]	f	2026-09-22 20:58:02	2026-09-22 20:58:02
01a0c932-6ecd-704b-8102-720cccd0b357	6	START	[]	f	2026-09-22 20:58:40	2026-09-22 20:58:40
01a0c933-ac28-71a2-b5c0-723a71307935	4	START	[]	f	2026-09-22 21:00:01	2026-09-22 21:00:01
01a0c956-8b2d-7027-b017-3aac5c71938f	4	START	[]	f	2026-09-22 21:38:07	2026-09-22 21:38:07
01a0c958-42cc-7391-82fe-29309afad193	6	START	[]	f	2026-09-22 21:39:59	2026-09-22 21:39:59
01a0c971-1c17-725c-ae17-8c7897d43231	6	START	[]	f	2026-09-22 22:07:08	2026-09-22 22:07:08
01a0c981-9731-71a1-99ed-e135c07ae96c	6	START	[]	f	2026-09-22 22:25:08	2026-09-22 22:25:08
01a0c986-953c-7373-85ef-83fb764ce5a9	4	START	[]	f	2026-09-22 22:30:35	2026-09-22 22:30:35
01a0c98b-a938-71f5-8179-cdf53c9f0b31	6	START	[]	f	2026-09-22 22:36:08	2026-09-22 22:36:08
01a0c9a3-3cc7-70f9-b90b-8d87238aa45d	6	START	[]	f	2026-09-22 23:01:53	2026-09-22 23:01:53
01a0c9c4-08c2-71c8-824f-1ca350430bde	6	START	[]	f	2026-09-22 23:37:42	2026-09-22 23:37:42
01a0c9c4-d6ca-7373-97ba-e79a7e6e8f2f	4	START	[]	f	2026-09-22 23:38:35	2026-09-22 23:38:35
01a0c9db-cdf3-7201-b861-ad4f1f32de30	4	START	[]	f	2026-09-23 00:03:40	2026-09-23 00:03:40
01a0c9e5-ef1d-712c-9cf0-17233e32ca41	6	START	[]	f	2026-09-23 00:14:44	2026-09-23 00:14:44
01a0c9f0-db56-731f-9755-b7972eab9bda	6	START	[]	f	2026-09-23 00:26:40	2026-09-23 00:26:40
01a0c9f1-c5c2-733b-b8e0-37b7400b84c0	4	START	[]	f	2026-09-23 00:27:40	2026-09-23 00:27:40
01a0ca3e-2edb-7118-879e-fb362de47fba	4	START	[]	f	2026-09-23 01:51:07	2026-09-23 01:51:07
01a0ca4b-d4a7-72ec-aff1-833a11922244	4	START	[]	f	2026-09-23 02:06:02	2026-09-23 02:06:02
01a0ca4c-a993-70ab-9799-ff9b13a98eeb	6	START	[]	f	2026-09-23 02:06:56	2026-09-23 02:06:56
01a0ca4d-7a41-70c0-a617-3152db97c97b	4	START	[]	f	2026-09-23 02:07:50	2026-09-23 02:07:50
01a0ca5c-9402-7355-8d71-47d6bac9576e	4	START	[]	f	2026-09-23 02:24:19	2026-09-23 02:24:19
01a0ca74-f1e9-7007-88b5-c98a922953f2	4	START	[]	f	2026-09-23 02:50:56	2026-09-23 02:50:56
01a0ca7a-0a62-72e8-a7dd-b05243938e50	4	START	[]	f	2026-09-23 02:56:30	2026-09-23 02:56:30
01a0ca82-f9a3-739e-9f53-4e412b7edd88	4	START	[]	f	2026-09-23 03:06:16	2026-09-23 03:06:16
01a0ca93-2902-7199-b7d3-03f70aae278f	4	START	[]	f	2026-09-23 03:23:57	2026-09-23 03:23:57
01a0ca95-14b0-7374-bb53-2c387d875df0	6	START	[]	f	2026-09-23 03:26:02	2026-09-23 03:26:02
01a0ca96-6a89-70d3-91e2-0405c903ad21	4	START	[]	f	2026-09-23 03:27:30	2026-09-23 03:27:30
01a0ca97-a89e-73ec-b6be-5dc689239e80	13	START	[]	f	2026-09-23 03:28:51	2026-09-23 03:28:51
01a0cc60-f5c5-72db-89d8-cb636b6b4ca0	13	START	[]	f	2026-09-23 11:48:21	2026-09-23 11:48:21
01a0cc73-6124-7034-bf03-33753ee876b3	13	START	[]	f	2026-09-23 12:08:28	2026-09-23 12:08:28
01a0cc7f-fe28-72ac-8868-9a22851e536c	13	START	[]	f	2026-09-23 12:22:15	2026-09-23 12:22:15
01a0cc8e-8793-7021-8e98-507eb45cee3f	13	START	[]	f	2026-09-23 12:38:08	2026-09-23 12:38:08
01a0cc98-ca3a-717c-89f8-53b4df2abe0c	13	START	[]	f	2026-09-23 12:49:20	2026-09-23 12:49:20
01a0cca8-2534-71a3-ae37-c91d37da2e58	13	START	[]	f	2026-09-23 13:06:06	2026-09-23 13:06:06
01a0ccaf-8c94-702a-91c2-28ef5fbdebca	13	START	[]	f	2026-09-23 13:14:11	2026-09-23 13:14:11
01a0ccb3-4b6a-72e0-8de9-1b96b4c489f3	13	START	[]	f	2026-09-23 13:18:17	2026-09-23 13:18:17
01a0cce4-3828-7378-97de-bdaeb96c90e3	13	START	[]	f	2026-09-23 14:11:43	2026-09-23 14:11:43
01a0cd80-ecf4-7100-a271-1ab42be38020	13	START	[]	f	2026-09-23 17:02:53	2026-09-23 17:02:53
01a0cf58-3e78-719f-89b8-23889198d193	1	START	[]	f	2026-09-24 01:37:42	2026-09-24 01:37:42
01a0cf58-7541-7207-9c45-1689bdfad78a	1	START	[]	f	2026-09-24 01:37:56	2026-09-24 01:37:56
01a0cf5e-71e3-7144-a155-721406f13a8e	13	START	[]	f	2026-09-24 01:44:28	2026-09-24 01:44:28
01a0cf5f-ccef-71d4-b4e7-efcbaa272c7d	6	START	[]	f	2026-09-24 01:45:57	2026-09-24 01:45:57
01a0cf61-5080-71ab-970a-17b5834ca0c1	5	START	[]	f	2026-09-24 01:47:36	2026-09-24 01:47:36
01a0cf64-2628-71b2-bede-5e5f907a7d79	6	START	[]	f	2026-09-24 01:50:42	2026-09-24 01:50:42
01a0cf77-e464-71fe-a1af-52ace3559e5f	6	START	[]	f	2026-09-24 02:12:16	2026-09-24 02:12:16
01a0cf87-7562-7199-85a6-506ddd411bfa	6	START	[]	f	2026-09-24 02:29:16	2026-09-24 02:29:16
01a0cf8b-3e45-7268-a280-2fa60f134ae1	5	START	[]	f	2026-09-24 02:33:24	2026-09-24 02:33:24
01a0cf8c-04b8-726f-b64b-cd2a4deef90c	13	START	[]	f	2026-09-24 02:34:15	2026-09-24 02:34:15
01a0cf8d-1e1e-708d-82f5-6e60aef7be80	6	START	[]	f	2026-09-24 02:35:27	2026-09-24 02:35:27
01a0cf94-9d99-725e-86cf-9da1e48bd148	6	START	[]	f	2026-09-24 02:43:38	2026-09-24 02:43:38
01a0cf99-2079-7093-b66c-594ef1e21be5	5	START	[]	f	2026-09-24 02:48:34	2026-09-24 02:48:34
01a0cf9b-089c-718b-ac43-df9d9f889f72	13	START	[]	f	2026-09-24 02:50:39	2026-09-24 02:50:39
01a0cfa4-46b1-709b-be7d-9c78dca2ad0c	13	START	[]	f	2026-09-24 03:00:44	2026-09-24 03:00:44
01a0cfb1-9666-7117-8617-30a767f904ac	13	START	[]	f	2026-09-24 03:15:17	2026-09-24 03:15:17
01a0cfb3-0e61-730c-9a1b-7a812c7ae28a	5	START	[]	f	2026-09-24 03:16:53	2026-09-24 03:16:53
01a0cfbb-47f9-7396-a44e-916732872baa	6	START	[]	f	2026-09-24 03:25:52	2026-09-24 03:25:52
01a0cfbc-cead-709a-ac42-d2800ddbf279	5	START	[]	f	2026-09-24 03:27:32	2026-09-24 03:27:32
01a0cfbd-78ed-7212-b36b-7465de8fc948	13	START	[]	f	2026-09-24 03:28:16	2026-09-24 03:28:16
01a0cfbe-dba7-711f-b7f4-d40524671d48	4	START	[]	f	2026-09-24 03:29:46	2026-09-24 03:29:46
01a0d431-db60-73d4-a600-9a432586b952	4	START	[]	f	2026-09-25 00:13:52	2026-09-25 00:13:52
01a0d43b-c186-7332-afe9-b3a9b6b7d17c	4	START	[]	f	2026-09-25 00:24:41	2026-09-25 00:24:41
01a0d44e-3b82-73da-86e1-2ab9ccf4586c	4	START	[]	f	2026-09-25 00:44:51	2026-09-25 00:44:51
01a0d450-472e-73e8-9756-529af800b239	6	START	[]	f	2026-09-25 00:47:06	2026-09-25 00:47:06
01a0d456-02ef-71d1-bbed-4c873a3e32bd	6	START	[]	f	2026-09-25 00:53:21	2026-09-25 00:53:21
01a0d458-3d16-7098-bfc8-826e7b9a6b7d	4	START	[]	f	2026-09-25 00:55:47	2026-09-25 00:55:47
01a0d485-662c-7088-97e0-1c0a6401a9b4	6	START	[]	f	2026-09-25 01:45:07	2026-09-25 01:45:07
01a0d4e9-c066-7104-a3fd-0266beef4f67	6	START	[]	f	2026-09-25 03:34:44	2026-09-25 03:34:44
01a0d545-11de-7352-9433-f2b5b6a1fe60	6	START	[]	f	2026-09-25 05:14:28	2026-09-25 05:14:28
01a0d54b-551f-7214-968e-82d593c15ae2	5	START	[]	f	2026-09-25 05:21:19	2026-09-25 05:21:19
01a0d553-0779-7114-b94e-55869f91b241	1	START	[]	f	2026-09-25 05:29:43	2026-09-25 05:29:43
01a0d57b-fd33-7319-bf28-acd77e0ae14c	6	START	[]	f	2026-09-25 06:14:27	2026-09-25 06:14:27
01a0d57c-f327-7059-8b75-eed4730a3fee	6	START	[]	f	2026-09-25 06:15:30	2026-09-25 06:15:30
01a0d749-ea59-7055-86aa-fdacf3eefd84	6	START	[]	f	2026-09-25 14:39:00	2026-09-25 14:39:00
01a0d7ad-253c-738b-8867-e1170abce2f4	6	START	[]	f	2026-09-25 16:27:23	2026-09-25 16:27:23
01a0d7eb-4929-7237-b3d3-f417a3a3ee28	6	START	[]	f	2026-09-25 17:35:16	2026-09-25 17:35:16
01a0edf7-a4c7-7204-9e6c-387e227b135b	6	START	[]	f	2026-09-30 00:20:24	2026-09-30 00:20:24
01a0ee2d-fb7f-703c-9b94-e84c3ab74a36	6	START	[]	f	2026-09-30 01:19:46	2026-09-30 01:19:46
01a0ee5d-6a17-71ee-9cb7-ac76843eb40d	6	START	[]	f	2026-09-30 02:11:34	2026-09-30 02:11:34
01a0ee7b-bbf0-73ce-bbbb-87d8511f8191	6	START	[]	f	2026-09-30 02:44:41	2026-09-30 02:44:41
01a0f148-366f-7094-a8bb-053a226a1e47	24	START	[]	f	2026-09-30 15:47:16	2026-09-30 15:47:16
01a0f16a-6554-72d9-8247-442dcc15e54f	24	START	[]	f	2026-09-30 16:24:36	2026-09-30 16:24:36
01a0f172-baa6-7206-946d-c6bb01f2d29a	6	START	[]	f	2026-09-30 16:33:43	2026-09-30 16:33:43
01a0f184-2645-739d-b949-56933581bf1c	6	START	[]	f	2026-09-30 16:52:44	2026-09-30 16:52:44
01a0f193-bfbb-71ba-a606-25630597fdec	6	START	[]	f	2026-09-30 17:09:47	2026-09-30 17:09:47
01a0f60f-b93a-7279-aaf4-da13056a8f51	6	START	[]	f	2026-10-01 14:03:40	2026-10-01 14:03:40
01a0f64b-14cf-7282-bad5-a8a2afc85694	6	START	[]	f	2026-10-01 15:08:30	2026-10-01 15:08:30
01a0f660-f96c-71dc-8365-edffb4452e82	6	START	[]	f	2026-10-01 15:32:25	2026-10-01 15:32:25
01a0f661-d66c-721a-be34-bc51c99b37c1	6	START	[]	f	2026-10-01 15:33:22	2026-10-01 15:33:22
01a0fcdb-a275-73b5-bb2b-d3e3ed90ed11	6	START	[]	f	2026-10-02 21:44:07	2026-10-02 21:44:07
01a120e4-7909-736a-b8fa-4a85a490208c	6	START	[]	f	2026-10-09 21:40:06	2026-10-09 21:40:06
01a120fa-afa1-7379-a953-2eb01cef7e50	6	START	[]	f	2026-10-09 22:04:22	2026-10-09 22:04:22
01a133a2-297d-72b2-8b50-d4e542c16b15	6	START	[]	f	2026-10-13 13:00:27	2026-10-13 13:00:27
01a133b8-c81f-7210-90ea-e831c44f103e	6	START	[]	f	2026-10-13 13:25:10	2026-10-13 13:25:10
01a133c0-1bf7-7176-8260-82ae2a70d5f4	6	START	[]	f	2026-10-13 13:33:10	2026-10-13 13:33:10
01a133c5-e0b6-725f-b117-7d4356de2485	6	START	[]	f	2026-10-13 13:39:28	2026-10-13 13:39:28
01a18d06-79e4-7097-a4cf-9ec0e6715d77	6	START	[]	f	2026-10-30 21:36:14	2026-10-30 21:36:14
01a18d07-7300-70dc-b2bb-18e7ddd21f9d	4	START	[]	f	2026-10-30 21:37:18	2026-10-30 21:37:18
01a19b75-ab32-709b-966c-972016bb7201	6	START	[]	f	2026-11-02 16:52:22	2026-11-02 16:52:22
01a19bb1-4692-71f8-8217-ca60754d83aa	6	START	[]	f	2026-11-02 17:57:28	2026-11-02 17:57:28
01a19bb3-7de9-70f7-ace5-20c157c35f67	6	START	[]	f	2026-11-02 17:59:54	2026-11-02 17:59:54
01a19bc4-a2e3-732f-b3f6-88d6a70e9cc8	6	START	[]	f	2026-11-02 18:18:37	2026-11-02 18:18:37
01a19c10-82b7-7353-9083-731d77d969b4	6	START	[]	f	2026-11-02 19:41:30	2026-11-02 19:41:30
01a19c50-896d-71e5-9203-9c26b505576b	6	START	[]	f	2026-11-02 20:51:26	2026-11-02 20:51:26
01a19c65-41ae-713e-a95f-b8f566dda6ee	6	START	[]	f	2026-11-02 21:14:04	2026-11-02 21:14:04
01a19c96-4684-7090-9ecc-d579bb38270a	6	START	[]	f	2026-11-02 22:07:36	2026-11-02 22:07:36
01a1aca6-eda6-718e-96a2-fee4e92724a6	6	START	[]	f	2026-11-06 00:59:43	2026-11-06 00:59:43
01a1ed7e-8d5d-7141-9296-1910e9618a8b	24	START	[]	f	2026-11-18 15:10:56	2026-11-18 15:10:56
01a1ed7f-4a77-703b-9096-683e3b40f050	24	START	[]	f	2026-11-18 15:11:44	2026-11-18 15:11:44
01a1ed82-e382-7383-aa50-85869514917b	24	START	[]	f	2026-11-18 15:15:40	2026-11-18 15:15:40
01a1ed83-d2ef-71d4-89fe-6b9eed6d7fac	4	START	[]	f	2026-11-18 15:16:41	2026-11-18 15:16:41
01a1ed85-959f-72d9-8a2d-9c7073295520	4	START	[]	f	2026-11-18 15:18:37	2026-11-18 15:18:37
01a1ed86-3541-722a-a65e-0a9ed37544da	6	START	[]	f	2026-11-18 15:19:18	2026-11-18 15:19:18
01a1ed90-6cce-73ed-853d-6bcdef31a057	6	START	[]	f	2026-11-18 15:30:27	2026-11-18 15:30:27
01a1ed92-0024-720a-9745-f034f75b8be7	6	START	[]	f	2026-11-18 15:32:10	2026-11-18 15:32:10
01a1ed99-4c93-71df-a4ee-48f206fadb1e	6	START	[]	f	2026-11-18 15:40:09	2026-11-18 15:40:09
01a1eda2-bcc6-7058-9b61-efef9ee9f707	6	START	[]	f	2026-11-18 15:50:27	2026-11-18 15:50:27
01a1eda3-8837-70c5-90a2-59d9367213d7	6	START	[]	f	2026-11-18 15:51:19	2026-11-18 15:51:19
01a1eda6-d6d5-731b-80e8-d8eeecd3ab81	6	START	[]	f	2026-11-18 15:54:56	2026-11-18 15:54:56
01a1eda9-7794-72fd-89ae-4817f737a270	6	START	[]	f	2026-11-18 15:57:48	2026-11-18 15:57:48
01a1edb0-d3a1-728b-be96-f4dd403cb0b4	6	START	[]	f	2026-11-18 16:05:51	2026-11-18 16:05:51
01a1edb3-338e-70b4-8d74-fdf06a93c896	6	START	[]	f	2026-11-18 16:08:26	2026-11-18 16:08:26
01a1edc1-9728-725c-9fbe-f59dff0c531a	6	START	[]	f	2026-11-18 16:24:09	2026-11-18 16:24:09
01a1eed7-a9d3-707b-9701-f9f29209fcc3	24	START	[]	f	2026-11-18 21:27:53	2026-11-18 21:27:53
01a1eee4-6db6-701c-9a99-f2f2c602abc5	4	START	[]	f	2026-11-18 21:41:50	2026-11-18 21:41:50
01a1eee5-5208-709a-b27e-21e534a92d04	4	START	[]	f	2026-11-18 21:42:48	2026-11-18 21:42:48
01a1ef00-e4c8-723a-bc41-4622a549ddbd	24	START	[]	f	2026-11-18 22:12:55	2026-11-18 22:12:55
01a1ef01-94d5-739f-88d2-864ccdc037d3	6	START	[]	f	2026-11-18 22:13:40	2026-11-18 22:13:40
01a1ef13-60ad-71cc-adc3-b9115d58c0d1	6	START	[]	f	2026-11-18 22:33:06	2026-11-18 22:33:06
01a1ef14-d4be-733b-b11e-d31946908e98	24	START	[]	f	2026-11-18 22:34:42	2026-11-18 22:34:42
01a1ef1a-4629-73c5-80fe-5497d066d67d	24	START	[]	f	2026-11-18 22:40:38	2026-11-18 22:40:38
01a1ef1a-ae9f-7215-bab2-1da2940fc4fb	24	START	[]	f	2026-11-18 22:41:05	2026-11-18 22:41:05
01a1ef1b-8a03-7090-ae64-3ecbcc2ce61c	24	START	[]	f	2026-11-18 22:42:01	2026-11-18 22:42:01
01a1ef31-413b-7246-8404-8b859ec539f2	6	START	[]	f	2026-11-18 23:05:45	2026-11-18 23:05:45
01a1ef3b-2883-7329-adeb-1cd6c217dc73	24	START	[]	f	2026-11-18 23:16:34	2026-11-18 23:16:34
01a1ef3d-4f9a-7142-8330-7b95fbd965c9	6	START	[]	f	2026-11-18 23:18:55	2026-11-18 23:18:55
01a1ef45-3a00-732e-989f-de6a02c53e96	24	START	[]	f	2026-11-18 23:27:33	2026-11-18 23:27:33
01a1ef45-8224-73dc-a16f-fa113e8b3bef	6	START	[]	f	2026-11-18 23:27:52	2026-11-18 23:27:52
01a1efaf-7951-73da-941a-51f38f8f483f	6	START	[]	f	2026-11-19 01:23:36	2026-11-19 01:23:36
01a1efb2-9be9-72c9-8b89-a902a426a021	6	START	[]	f	2026-11-19 01:27:02	2026-11-19 01:27:02
01a1efb4-7d65-7280-957d-bd02e6eae850	24	START	[]	f	2026-11-19 01:29:05	2026-11-19 01:29:05
01a1efc2-f6cf-7132-9f62-3c0f71532efc	6	START	[]	f	2026-11-19 01:44:54	2026-11-19 01:44:54
01a1f23d-0363-7389-8241-8e04e4b5cc9d	6	START	[]	f	2026-11-19 13:17:27	2026-11-19 13:17:27
01a1f25c-96db-70e7-9e72-7a22864fd3e5	6	START	[]	f	2026-11-19 13:51:56	2026-11-19 13:51:56
01a1f25d-9980-725f-8548-b014d6d33221	6	START	[]	f	2026-11-19 13:53:02	2026-11-19 13:53:02
01a1f25f-2227-70bc-8c84-d463b351528b	6	START	[]	f	2026-11-19 13:54:43	2026-11-19 13:54:43
01a1f270-7f46-7051-8d9f-e9fa5a3f907e	6	START	[]	f	2026-11-19 14:13:41	2026-11-19 14:13:41
01a1f273-5cb8-71cf-8e69-6582af2552e6	6	START	[]	f	2026-11-19 14:16:49	2026-11-19 14:16:49
01a1f28a-a32a-7347-84fa-43aafd84c029	6	START	[]	f	2026-11-19 14:42:14	2026-11-19 14:42:14
01a1f28a-e873-7277-a3a3-760009a4f18e	6	START	[]	f	2026-11-19 14:42:32	2026-11-19 14:42:32
01a1f2a4-6cff-72a8-b3b6-bf1ba46564cf	6	START	[]	f	2026-11-19 15:10:24	2026-11-19 15:10:24
01a1f2a8-6a21-72c2-817b-59e79741904f	6	START	[]	f	2026-11-19 15:14:45	2026-11-19 15:14:45
01a1f2c3-d118-71aa-abe4-0464e8d880e4	6	START	[]	f	2026-11-19 15:44:41	2026-11-19 15:44:41
01a1f2c3-d690-72dd-8b3c-3653a6cbe178	6	START	[]	f	2026-11-19 15:44:43	2026-11-19 15:44:43
01a1f2c7-873e-71d6-9efd-14f5b787b585	24	START	[]	f	2026-11-19 15:48:44	2026-11-19 15:48:44
01a1f2cd-47cb-73aa-bdd5-ea3fb4de8b03	6	START	[]	f	2026-11-19 15:55:01	2026-11-19 15:55:01
01a1f2ce-7699-7031-934e-24c03433687a	24	START	[]	f	2026-11-19 15:56:19	2026-11-19 15:56:19
01a1f2d5-71f5-729a-9552-c0a97bd5ce64	6	START	[]	f	2026-11-19 16:03:57	2026-11-19 16:03:57
01a1f2d7-fd47-7232-9386-bff96ffb7a04	24	START	[]	f	2026-11-19 16:06:43	2026-11-19 16:06:43
01a1f2e6-39d5-73b9-9327-541a5686fe97	24	START	[]	f	2026-11-19 16:22:16	2026-11-19 16:22:16
01a1f2e6-3b95-7316-834a-27f448e96b79	6	START	[]	f	2026-11-19 16:22:17	2026-11-19 16:22:17
01a1f2e7-744d-72d5-8ff5-8e86c91fbf42	4	START	[]	f	2026-11-19 16:23:37	2026-11-19 16:23:37
01a1f2e9-360a-734b-b688-1e7d55ade76e	24	START	[]	f	2026-11-19 16:25:32	2026-11-19 16:25:32
01a1f2ef-6d89-7152-b1de-9b5d6b76f0ab	4	START	[]	f	2026-11-19 16:32:19	2026-11-19 16:32:19
01a1f2f0-e890-723d-848b-b3a759488e43	24	START	[]	f	2026-11-19 16:33:56	2026-11-19 16:33:56
01a1f2f1-340d-71a3-b1a9-031f1cae847e	4	START	[]	f	2026-11-19 16:34:16	2026-11-19 16:34:16
01a1f2f2-067f-7349-8a2e-9687a0c56706	24	START	[]	f	2026-11-19 16:35:10	2026-11-19 16:35:10
01a1f2f2-513d-723a-9240-1e9d20c0ae3e	4	START	[]	f	2026-11-19 16:35:29	2026-11-19 16:35:29
01a1f2f7-9c47-733d-b2ce-bc4f3dc2ad3b	4	START	[]	f	2026-11-19 16:41:16	2026-11-19 16:41:16
01a1f2f8-8b40-731f-a557-7a9e20092531	4	START	[]	f	2026-11-19 16:42:17	2026-11-19 16:42:17
01a1f2fb-2abb-73c6-bd14-d82136989bcd	24	START	[]	f	2026-11-19 16:45:09	2026-11-19 16:45:09
01a1f2fc-108b-727d-98ed-162b6318d24d	4	START	[]	f	2026-11-19 16:46:08	2026-11-19 16:46:08
01a1f30a-b304-7208-900c-b2aa273477ff	4	START	[]	f	2026-11-19 17:02:07	2026-11-19 17:02:07
01a1f30c-6335-73eb-802e-e178df54b5a3	4	START	[]	f	2026-11-19 17:03:57	2026-11-19 17:03:57
01a1f31a-112a-703e-9c19-7a1c22abda67	4	START	[]	f	2026-11-19 17:18:54	2026-11-19 17:18:54
01a1f31c-f6f5-70e2-8a2d-5ec4fbeebdd3	4	START	[]	f	2026-11-19 17:22:04	2026-11-19 17:22:04
01a1f325-d4f6-7130-b58b-f04c9fc56edb	4	START	[]	f	2026-11-19 17:31:45	2026-11-19 17:31:45
01a1f326-0f6d-718d-a0c5-1a9fec160e01	4	START	[]	f	2026-11-19 17:32:00	2026-11-19 17:32:00
01a1f327-ca70-733b-a47f-d25fdb996b2a	6	START	[]	f	2026-11-19 17:33:53	2026-11-19 17:33:53
01a1f32a-27e8-733a-80dd-ec98d489e4e2	4	START	[]	f	2026-11-19 17:36:28	2026-11-19 17:36:28
01a1f32a-74af-70a3-b334-f05912754a46	6	START	[]	f	2026-11-19 17:36:48	2026-11-19 17:36:48
01a1f32c-4320-7348-b239-42d5a3d34190	4	START	[]	f	2026-11-19 17:38:46	2026-11-19 17:38:46
01a1f32f-27f6-71e8-a228-e41d9a9c8049	6	START	[]	f	2026-11-19 17:41:56	2026-11-19 17:41:56
01a1f332-39f5-7044-94ec-2ca0d7e8e9a1	6	START	[]	f	2026-11-19 17:45:17	2026-11-19 17:45:17
01a1f336-065a-7080-b10a-32b0928267be	4	START	[]	f	2026-11-19 17:49:26	2026-11-19 17:49:26
01a1f339-4f3e-7267-b683-bf3dbe3a0c66	4	START	[]	f	2026-11-19 17:53:01	2026-11-19 17:53:01
01a1f33b-3886-71cf-82db-01693d088135	4	START	[]	f	2026-11-19 17:55:07	2026-11-19 17:55:07
01a1f341-4140-7176-b0d4-338160271a02	6	START	[]	f	2026-11-19 18:01:42	2026-11-19 18:01:42
01a1f341-ca3d-72e5-b1e2-6814c1407562	4	START	[]	f	2026-11-19 18:02:17	2026-11-19 18:02:17
01a1f34a-293d-722d-bf93-825f27725e7b	4	START	[]	f	2026-11-19 18:11:26	2026-11-19 18:11:26
01a1f34a-ceab-7229-aec9-c588a71a18b1	4	START	[]	f	2026-11-19 18:12:08	2026-11-19 18:12:08
01a1f34b-d271-7317-86d9-b0cf0fd85d30	4	START	[]	f	2026-11-19 18:13:14	2026-11-19 18:13:14
01a1f352-b366-71d2-a5ce-5322d69d7c55	4	START	[]	f	2026-11-19 18:20:45	2026-11-19 18:20:45
01a1f35b-08de-737f-9d7c-8fd90f553672	4	START	[]	f	2026-11-19 18:29:51	2026-11-19 18:29:51
01a1f35d-249c-715c-9cba-ebef1ebe1099	4	START	[]	f	2026-11-19 18:32:10	2026-11-19 18:32:10
01a1f38e-066d-7034-890c-c292305d2b44	4	START	[]	f	2026-11-19 19:25:33	2026-11-19 19:25:33
01a1f38f-f615-7016-b1b8-51cfd189fe4d	4	START	[]	f	2026-11-19 19:27:40	2026-11-19 19:27:40
01a1f3a9-952b-7259-93da-cc2c8c61d15e	4	START	[]	f	2026-11-19 19:55:39	2026-11-19 19:55:39
01a1f7d4-d8df-73af-a8a6-325cbfa67fd8	4	START	[]	f	2026-11-20 15:21:23	2026-11-20 15:21:23
01a1f7e2-9035-726a-a6fa-14be96bbb27a	6	START	[]	f	2026-11-20 15:36:22	2026-11-20 15:36:22
01a1f805-44a0-7142-8fd5-3a618aeb9566	4	START	[]	f	2026-11-20 16:14:17	2026-11-20 16:14:17
01a1f822-141d-7943-a490-acae8ae8cd15	6	START	[]	f	2026-11-20 16:45:45	2026-11-20 16:45:45
01a1f846-53b5-7c20-be98-b88c437ddb22	6	START	[]	f	2026-11-20 17:25:20	2026-11-20 17:25:20
01a1f868-3f64-7c83-971c-23991e3ef4af	6	START	[]	f	2026-11-20 18:02:23	2026-11-20 18:02:23
01a1f94e-36f9-708f-aa42-3d39beb1f62c	6	START	[]	f	2026-11-20 22:13:35	2026-11-20 22:13:35
01a1f953-8755-769f-83fc-b8ea9201bdb2	6	START	[]	f	2026-11-20 22:19:23	2026-11-20 22:19:23
01a1f95a-4d1c-7595-818a-0eb501ed523d	4	START	[]	f	2026-11-20 22:26:47	2026-11-20 22:26:47
01a1f95f-b97e-79ae-95ca-a8cfe78c35a6	6	START	[]	f	2026-11-20 22:32:42	2026-11-20 22:32:42
01a1f967-c06a-7b02-9a76-0c8193f84ad7	6	START	[]	f	2026-11-20 22:41:28	2026-11-20 22:41:28
01a1f967-e5fe-7326-9b8c-0855a284396a	6	START	[]	f	2026-11-20 22:41:38	2026-11-20 22:41:38
01a1f96c-f8da-70f1-a715-fdf38bccc584	6	START	[]	f	2026-11-20 22:47:10	2026-11-20 22:47:10
01a1f96e-14cb-7116-9916-fe1630d9230d	4	START	[]	f	2026-11-20 22:48:23	2026-11-20 22:48:23
01a1f96e-c035-7ad9-974d-2ef7943f54ac	6	START	[]	f	2026-11-20 22:49:07	2026-11-20 22:49:07
01a1f96f-ec8a-7a72-939f-b1dab20fce4e	4	START	[]	f	2026-11-20 22:50:24	2026-11-20 22:50:24
01a1f977-d465-7cd0-9c25-5491082eeae5	4	START	[]	f	2026-11-20 22:59:02	2026-11-20 22:59:02
01a1f987-530e-7d58-8a66-a0429a0427c5	6	START	[]	f	2026-11-20 23:15:57	2026-11-20 23:15:57
01a1f988-2088-7760-bd56-46022482ab09	4	START	[]	f	2026-11-20 23:16:50	2026-11-20 23:16:50
01a1f98a-27ad-73ca-86a7-20d4865af185	6	START	[]	f	2026-11-20 23:19:03	2026-11-20 23:19:03
\.


--
-- Data for Name: complaint_photos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.complaint_photos (id, complaint_id, storage_key, url, type, mime, size, checksum, captured_at, created_at, updated_at, file_data) FROM stdin;
1	2	complaints/YTY05ZmGe8DiG81cKAF2MPRvPuaiFVnqZUuspRW7.jpg	\N	evidence_before	image/jpeg	255944	499c171dffb24d1ecb78d270758813c1	2026-09-04 04:53:10	2026-09-04 04:53:10	2026-09-04 04:53:10	\N
2	3	complaints/2pZwgsqDyPVMW7Ly63uStIB0NljYnMd6wy1Cvs5U.jpg	\N	evidence_before	image/jpeg	171385	e951ef5e5eb8b64b6323a1e6b03edb1a	2026-09-04 05:04:25	2026-09-04 05:04:25	2026-09-04 05:04:25	\N
3	4	complaints/X2rFxXUprodbQfHkK9BX3nLWokFhIpRUTI22N5IA.jpg	\N	evidence_before	image/jpeg	171385	e951ef5e5eb8b64b6323a1e6b03edb1a	2026-09-10 03:07:11	2026-09-10 03:07:11	2026-09-10 03:07:11	\N
4	4	complaints/aChH1MOWExGfPb96m7IDUTRJ4gc8Bz7DzqIDuiTI.jpg	\N	evidence_before	image/jpeg	466607	03fd4821957e8e35996bbc43add26c3f	2026-09-10 03:07:11	2026-09-10 03:07:11	2026-09-10 03:07:11	\N
5	4	complaints/tqg7KJDGQIvTzhGvAwCT9OpzqKmWh64EngPbI01T.jpg	\N	evidence_before	image/jpeg	72615	dfb8acf4a7eeb7e8c3935fccf17ba07c	2026-09-10 03:07:11	2026-09-10 03:07:11	2026-09-10 03:07:11	\N
6	2	resolutions/qg4IvSn37JCr2QrrpTFQIyMIVmsHa7GPEZm2wuQW.png	\N	resolution_after	image/png	466456	734193127c10c5e65376deca4b4bc62a	2026-09-11 01:32:16	2026-09-11 01:32:16	2026-09-11 01:32:16	\N
7	5	complaints/eBDYOFeTSoLGXeXpPPbuvMr1KWvckZECLuj9UgFk.jpg	\N	evidence_before	image/jpeg	466607	03fd4821957e8e35996bbc43add26c3f	2026-09-11 01:57:16	2026-09-11 01:57:16	2026-09-11 01:57:16	\N
8	5	resolutions/XqCus6CrtF7K7horpTz8QddQYTcPyCo8tF3SGkFJ.png	\N	resolution_after	image/png	466456	734193127c10c5e65376deca4b4bc62a	2026-09-11 02:03:58	2026-09-11 02:03:58	2026-09-11 02:03:58	\N
9	6	complaints/2pZwgsqDyPVMW7Ly63uStIB0NljYnMd6wy1Cvs5U.jpg	\N	evidence_before	image/jpeg	171385	\N	2026-11-18 04:21:00	2026-11-18 04:21:00	2026-11-18 04:21:00	\N
10	7	complaints/YTY05ZmGe8DiG81cKAF2MPRvPuaiFVnqZUuspRW7.jpg	\N	evidence_before	\N	\N	\N	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N
11	8	complaints/2pZwgsqDyPVMW7Ly63uStIB0NljYnMd6wy1Cvs5U.jpg	\N	evidence_before	\N	\N	\N	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N
12	9	complaints/X2rFxXUprodbQfHkK9BX3nLWokFhIpRUTI22N5IA.jpg	\N	evidence_before	\N	\N	\N	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N
13	10	complaints/aChH1MOWExGfPb96m7IDUTRJ4gc8Bz7DzqIDuiTI.jpg	\N	evidence_before	\N	\N	\N	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N
14	11	complaints/tqg7KJDGQIvTzhGvAwCT9OpzqKmWh64EngPbI01T.jpg	\N	evidence_before	\N	\N	\N	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N
15	12	complaints/YTY05ZmGe8DiG81cKAF2MPRvPuaiFVnqZUuspRW7.jpg	\N	evidence_before	\N	\N	\N	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N
16	13	complaints/DGdO3m5Ky2JwjGgCXfEP4XGrFZdEyAGHFdY0W5dS.jpg	\N	evidence_before	image/jpeg	66976	8b2fb9d1f2846ae109a12f6fdb0ced45	2026-11-20 22:22:38	2026-11-20 22:22:38	2026-11-20 22:22:38	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
\.


--
-- Data for Name: complaint_ratings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.complaint_ratings (id, complaint_id, user_id, rating, comment, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: complaint_status_histories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.complaint_status_histories (id, complaint_id, from_status, to_status, note, changed_by, created_at, updated_at) FROM stdin;
1	2	\N	SUBMITTED	Laporan dibuat oleh warga dan menunggu verifikasi administratif.	6	2026-09-04 04:53:09	2026-09-04 04:53:09
2	2	SUBMITTED	DISPATCHED	Laporan diverifikasi valid oleh Operator & ditugaskan dengan tenggat: 11 September 2026 05:01. Instruksi: Lakukan perbaikan dan penanganan fisik sesuai standar teknis	1	2026-09-04 05:01:49	2026-09-04 05:01:49
3	3	\N	SUBMITTED	Laporan dibuat oleh warga dan menunggu verifikasi administratif.	6	2026-09-04 05:04:25	2026-09-04 05:04:25
5	3	SUBMITTED	DISPATCHED	Laporan diverifikasi valid oleh Operator & ditugaskan dengan tenggat: 13 September 2026 03:01. Instruksi: Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis	1	2026-09-10 03:01:20	2026-09-10 03:01:20
6	4	\N	SUBMITTED	Laporan dibuat oleh warga dan menunggu verifikasi administratif.	6	2026-09-10 03:07:10	2026-09-10 03:07:10
7	2	IN_PROGRESS	WAITING_VERIFICATION	Pekerjaan lapangan selesai: Selesai. Menunggu validasi Operator.	1	2026-09-11 01:32:16	2026-09-11 01:32:16
8	2	WAITING_VERIFICATION	COMPLETED	Operator memvalidasi pekerjaan lapangan telah selesai dan sesuai standar kelayakan teknis.	1	2026-09-11 01:40:43	2026-09-11 01:40:43
9	5	\N	SUBMITTED	Laporan dibuat oleh warga dan menunggu verifikasi administratif.	6	2026-09-11 01:57:16	2026-09-11 01:57:16
10	5	SUBMITTED	DISPATCHED	Laporan diverifikasi valid oleh Operator & ditugaskan dengan tenggat: 16 September 2026 02:02. Instruksi: Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis	1	2026-09-11 02:02:44	2026-09-11 02:02:44
12	5	IN_PROGRESS	WAITING_VERIFICATION	Pekerjaan lapangan selesai: asdad. Menunggu validasi Operator.	1	2026-09-11 02:03:58	2026-09-11 02:03:58
14	4	SUBMITTED	REJECTED	Ditolak oleh Super Admin: Laporan Hokas	\N	2026-11-16 21:04:55	2026-11-16 21:04:55
15	7	\N	SUBMITTED	Laporan pengaduan dikirim oleh warga dan menunggu verifikasi Operator.	6	2026-11-19 13:59:44	2026-11-19 13:59:44
16	8	\N	SUBMITTED	Laporan pengaduan dikirim oleh warga dan menunggu verifikasi Operator.	6	2026-11-19 13:59:44	2026-11-19 13:59:44
17	9	\N	SUBMITTED	Laporan pengaduan dikirim oleh warga dan menunggu verifikasi Operator.	5	2026-11-19 13:59:44	2026-11-19 13:59:44
18	10	\N	SUBMITTED	Laporan pengaduan dikirim oleh warga dan menunggu verifikasi Operator.	25	2026-11-19 13:59:44	2026-11-19 13:59:44
19	11	\N	SUBMITTED	Laporan pengaduan dikirim oleh warga dan menunggu verifikasi Operator.	26	2026-11-19 13:59:44	2026-11-19 13:59:44
20	12	\N	SUBMITTED	Laporan pengaduan dikirim oleh warga dan menunggu verifikasi Operator.	27	2026-11-19 13:59:44	2026-11-19 13:59:44
21	7	SUBMITTED	CANCELLED	Laporan dibatalkan oleh pelapor sebelum diverifikasi.	6	2026-11-19 14:19:41	2026-11-19 14:19:41
22	12	SUBMITTED	REJECTED	Laporan ditolak oleh Operator: Laporan tidak memenuhi kriteria kelayakan atau bukti lapangan tidak sesuai.	1	2026-11-19 14:31:34	2026-11-19 14:31:34
4	2	DISPATCHED	IN_PROGRESS	Pekerja lapangan (Pekerja Lapangan) telah mengambil laporan dan memulai proses perbaikan fisik di lokasi.	\N	2026-09-09 21:30:48	2026-09-09 21:30:48
11	5	DISPATCHED	IN_PROGRESS	Pekerja lapangan (Super Administrator) telah mengambil laporan dan memulai proses penanganan fisik di lokasi.	1	2026-09-11 02:03:24	2026-09-11 02:03:24
13	3	DISPATCHED	IN_PROGRESS	Pekerja Lapangan telah mengambil laporan dan memulai proses perbaikan fisik di lokasi.	\N	2026-09-23 02:56:39	2026-09-23 02:56:39
23	13	\N	SUBMITTED	Laporan dibuat oleh warga dan menunggu verifikasi administratif.	6	2026-11-20 22:22:38	2026-11-20 22:22:38
24	13	SUBMITTED	DISPATCHED	Laporan diverifikasi valid oleh Operator & ditugaskan ke Regu Regu Aspal 01 - Lasusua dengan tenggat: 27 November 2026 22:25. Instruksi: Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis	1	2026-11-20 22:25:28	2026-11-20 22:25:28
25	13	DISPATCHED	IN_PROGRESS	Pekerja lapangan Tri Susilo Muin dari Regu Aspal 01 - Lasusua telah mengambil laporan dan memulai proses perbaikan fisik di lokasi.	4	2026-11-20 22:28:31	2026-11-20 22:28:31
\.


--
-- Data for Name: complaints; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.complaints (id, public_code, user_id, category_id, region_id, title, description, address, latitude, longitude, status, priority_score, priority_level, ai_category, ai_confidence, is_anonymous, parent_complaint_id, created_at, updated_at, geom, damage_type, road_length, road_condition, end_latitude, end_longitude, route_coordinates, rejected_by_superadmin, admin_rejection_reason, admin_rejected_at, admin_rejected_by) FROM stdin;
2	0002/ringan/2026	6	2	1	Laporan Penerangan Jalan Umum (PJU)	Lampu Penerangan Jalan Umum (PJU) mengalami gangguan teknis / mati. Kondisi ini menyebabkan ruas jalan menjadi gelap pada malam hari, mengganggu pandangan pengendara dan meningkatkan potensi kerawanan kecelakaan lalu lintas. Diharapkan dinas terkait segera melakukan perbaikan armatur atau penggantian lampu.	Jalan Poros Lasusua, Desa/Kel. Punggiha, Kec. Lasusua, Kabupaten	-3.4899917	120.8799655	COMPLETED	24.00	LOW	\N	\N	f	\N	2026-09-04 04:53:09	2026-09-24 03:11:37	0101000020E6100000E9A9C85A51385E40C87FFDC880EB0BC0	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
5	0005/ringan/2026	6	3	1	Laporan Perbaikan Susunan Paving Block Trotoar	Permukaan trotoar mengalami kerusakan berupa susunan paving block yang lepas, pecah, dan bergelombang. Kondisi ini mengganggu akses jalur pedestrian dan rawan menyebabkan pejalan kaki tersandung.	Jalan Poros Lasusua, Desa/Kel. Lasusua, Kec. Lasusua, Kabupaten	-3.5041989	120.8914163	WAITING_VERIFICATION	31.00	MEDIUM	\N	\N	f	\N	2026-09-11 01:57:16	2026-09-24 03:11:37	0101000020E6100000E25817F70C395E40B3C0227599080CC0	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
3	0003/ringan/2026	6	1	12	Lubang Aspal	bahaya ini coba diperbaiki secepatnya	Jalan Poros Katoi, Desa/Kel. Tobaku, Kec. Katoi, Kabupaten	-3.4253785	120.8692418	IN_PROGRESS	38.00	MEDIUM	\N	\N	f	\N	2026-09-04 05:04:25	2026-09-24 03:11:37	0101000020E6100000EB7132A8A1375E40F94B70DB2C670BC0	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
4	0004/berat/2026	6	1	1	Laporan Kerusakan Lubang Jalan (Pothole)	Permukaan perkerasan jalan berlubang. Kerusakan terdeteksi dengan bentang sekitar 65 cm, panjang sekitar 31 cm, dan estimasi kedalaman sekitar 9 cm. Kondisi ini berisiko membahayakan kendaraan yang melintas dan perlu segera ditangani.	Jalan Poros Lasusua, Desa/Kel. Lasusua, Kec. Lasusua, Kabupaten	-3.5060100	120.8878000	REJECTED	38.00	MEDIUM	\N	\N	f	\N	2026-09-10 03:07:10	2026-11-16 21:04:55	0101000020E6100000E25817B7D1385E4078978BF84E0C0CC0	major	618 Meter	Aspal Hancur Bergelombang	-3.5007156	120.8861063	[[-3.506042,120.887737],[-3.505971,120.887701],[-3.505301,120.887309],[-3.504999,120.887138],[-3.504541,120.886969],[-3.504233,120.886747],[-3.503723,120.886531],[-3.503427,120.886409],[-3.503046,120.886283],[-3.502715,120.886204],[-3.502392,120.88614],[-3.502269,120.886134],[-3.502075,120.886137],[-3.501889,120.886146],[-3.501686,120.88613],[-3.501561,120.886104],[-3.501376,120.886039],[-3.501138,120.885921],[-3.500896,120.88579]]	t	Laporan Hokas	2026-11-16 21:04:55	1
6	0006/ringan/2026	5	1	1	Kerusakan Bahu Jalan & Drainase Pasar Sentral Lasusua	Bahu jalan berlubang dan saluran drainase tersumbat di depan area Pasar Sentral Lasusua sehingga menimbulkan genangan air saat hujan.	Jalan Trans Sulawesi, Desa/Kel. Lasusua, Kec. Lasusua, Kabupaten	-3.5012340	120.8845670	SUBMITTED	45.00	LOW	\N	\N	f	\N	2026-11-18 04:21:00	2026-11-18 04:21:00	\N	POINT	\N	\N	\N	\N	\N	f	\N	\N	\N
8	0008/ringan/2026	6	2	3	Lampu PJU Padam di Jalan Poros Lapai	Tiga titik lampu penerangan jalan umum padam total sejak seminggu terakhir sehingga jalan gelap di malam hari.	Jalan Poros Lapai, Kel. Lapai, Kec. Ngapa, Kabupaten	-3.3712000	120.9125000	SUBMITTED	0.00	LOW	\N	\N	f	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
9	0009/berat/2026	5	1	4	Kerusakan Aspal Ruas Jalan Olo-Oloho	Permukaan aspal terkelupas dan bergelombang sepanjang sekitar 850 meter menuju pelabuhan rakyat Pakue.	Jalan Poros Olo-Oloho, Kel. Olo-Oloho, Kec. Pakue, Kabupaten	-3.1924000	120.9310000	SUBMITTED	0.00	LOW	\N	\N	f	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N	major	850 meter	Aspal Rusak Berat	\N	\N	\N	f	\N	\N	\N
10	0010/ringan/2026	25	3	10	Drainase Tersumbat & Meluap di Mala-Mala	Saluran drainase di pinggir jalan desa pecah dan tersumbat material sehingga air meluap ke badan jalan saat hujan.	Jalan Poros Mala-Mala, Desa Mala-Mala, Kec. Kodeoha, Kabupaten	-3.4415000	120.9012000	SUBMITTED	0.00	LOW	\N	\N	f	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
11	0011/ringan/2026	26	1	14	Oprit Jembatan Rante Angin Retak	Sambungan aspal di ujung jembatan mengalami penurunan tanah dan retak cukup lebar.	Jalan Poros Rante Angin, Kel. Rante Angin, Kec. Rante Angin, Kabupaten	-3.6214000	120.9452000	SUBMITTED	0.00	LOW	\N	\N	f	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
7	0007/ringan/2026	6	1	1	Jalan Berlubang di Simpang Pasar Lasusua	Lubang sedalam sekitar 20 cm di jalur utama menuju Pasar Lasusua yang membahayakan pengendara motor saat hujan.	Jalan Trans Sulawesi, Kel. Lasusua, Kec. Lasusua, Kabupaten	-3.5082000	120.8841000	CANCELLED	0.00	LOW	\N	\N	f	\N	2026-11-19 13:59:44	2026-11-19 14:19:41	\N	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
12	0012/ringan/2026	27	2	13	Tiang Lampu Jalan Miring di Desa Lambai	Tiang PJU di tikungan desa miring akibat tanah tergerus hujan dan kabel mulai kendur.	Jalan Poros Lambai, Desa Lambai, Kec. Lambai, Kabupaten	-3.5742000	120.9188000	REJECTED	0.00	LOW	\N	\N	f	\N	2026-11-19 13:59:44	2026-11-19 14:31:34	\N	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
13	0013/ringan/2026	6	1	1	jalan wilayah ini rusak	kerusakan jalan parah	Jalan Poros Lasusua, Desa/Kel. Punggiha, Kec. Lasusua, Kabupaten, Desa/Kel. Lasusua, Kec. Lasusua, Kabupaten	-3.4908558	120.8753850	IN_PROGRESS	38.00	MEDIUM	\N	\N	f	\N	2026-11-20 22:22:38	2026-11-20 22:28:31	\N	minor	\N	\N	\N	\N	\N	f	\N	\N	\N
\.


--
-- Data for Name: departments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departments (id, name, code, description, created_at, updated_at) FROM stdin;
1	Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)	PUPR	Penanganan jalan, jembatan, irigasi, dan tata ruang.	2026-09-02 10:39:13	2026-09-02 10:39:13
\.


--
-- Data for Name: dispositions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dispositions (id, complaint_id, department_id, assigned_by, assigned_to, instruction, deadline, status, created_at, updated_at, team_id) FROM stdin;
2	2	1	1	4	Lakukan perbaikan dan penanganan fisik sesuai standar teknis	2026-09-11 05:01:49	COMPLETED	2026-09-04 05:01:49	2026-09-23 02:32:41	1
4	5	1	1	4	Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis	2026-09-16 02:02:44	WAITING_VERIFICATION	2026-09-11 02:02:44	2026-09-23 02:32:41	1
3	3	1	1	4	Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis	2026-09-13 03:01:20	IN_PROGRESS	2026-09-10 03:01:20	2026-09-23 02:56:39	1
5	13	1	1	4	Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis	2026-11-27 22:25:28	IN_PROGRESS	2026-11-20 22:25:28	2026-11-20 22:28:31	1
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_09_02_000001_create_regions_table	1
5	2026_09_02_000002_create_departments_table	1
6	2026_09_02_000003_create_categories_table	1
7	2026_09_02_000004_create_complaints_table	1
8	2026_09_02_000005_create_complaint_photos_table	1
9	2026_09_02_000006_create_complaint_status_histories_table	1
10	2026_09_02_000007_create_dispositions_table	1
11	2026_09_02_000008_create_supports_table	1
12	2026_09_02_000009_create_notifications_table	1
13	2026_09_02_000010_create_ai_assessments_table	1
14	2026_09_02_000011_create_audit_logs_table	1
15	2026_09_02_000012_create_chat_sessions_and_messages_table	1
16	2026_09_02_000013_create_system_settings_table	1
17	2026_09_08_201147_add_damage_segment_fields_to_complaints_table	2
18	2026_09_09_022304_add_avatar_to_users_table	3
19	2019_12_14_000001_create_personal_access_tokens_table	4
20	2026_09_23_000001_create_teams_and_team_members_tables	5
21	2026_09_24_000001_create_otp_verifications_table	6
22	2026_09_30_000001_add_security_and_otp_attempts_fields	7
23	2026_11_16_000001_add_governance_deactivation_and_admin_rejection	8
24	2026_11_16_000002_add_file_data_to_complaint_photos_table	9
25	2026_11_19_000001_create_complaint_ratings_table	10
26	2026_11_20_000001_make_chat_sessions_user_id_nullable	11
\.


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, user_id, type, title, body, data_json, read_at, created_at, updated_at) FROM stdin;
13	4	FIELD_TASK_ASSIGNED	Tugas Lapangan Baru	Laporan #ADU/0005/11/09/2026/RINGAN ("Laporan Perbaikan Susunan Paving Block Trotoar") telah ditugaskan. Silakan mengambil dan mengerjakan laporan ini dengan batas waktu penanganan hingga 16 September 2026, 02:02 (4 hari dari sekarang).	{"complaint_id":5,"public_code":"ADU\\/0005\\/11\\/09\\/2026\\/RINGAN","deadline":"2026-09-16T02:02:44+08:00"}	2026-09-11 17:19:35	2026-09-11 02:02:44	2026-09-11 17:19:35
10	4	FIELD_TASK_ASSIGNED	Tugas Lapangan Baru	Laporan #ADU-0003 ("Lubang Aspal") telah ditugaskan. Silakan mengambil dan mengerjakan laporan ini dengan batas waktu penanganan hingga 13 September 2026, 03:01 (2 hari dari sekarang).	{"complaint_id":3,"public_code":"ADU-0003","deadline":"2026-09-13T03:01:20+08:00"}	2026-09-11 17:19:41	2026-09-10 03:01:20	2026-09-11 17:19:41
17	27	COMPLAINT_REJECTED	Laporan Ditolak Operator	Laporan #0012/ringan/2026 tidak dapat diproses. Alasan: Laporan tidak memenuhi kriteria kelayakan atau bukti lapangan tidak sesuai.	{"complaint_id":12}	\N	2026-11-19 14:31:34	2026-11-19 14:31:34
16	6	COMPLAINT_IN_PROGRESS	Laporan Sedang Dikerjakan	Pekerja Lapangan telah mengambil laporan Anda (#ADU-0003: "Lubang Aspal") dan saat ini berstatus Dikerjakan untuk segera dilakukan perbaikan fisik di lokasi.	{"complaint_id":3,"public_code":"ADU-0003","complaint_title":"Lubang Aspal"}	2026-09-23 03:26:38	2026-09-23 02:56:39	2026-09-23 03:26:38
19	9	FIELD_TASK_ASSIGNED	Tugas Lapangan untuk Regu Aspal 01 - Lasusua	Laporan #0013/ringan/2026 ("jalan wilayah ini rusak") ditugaskan ke regu Anda dengan batas waktu penanganan hingga 27 November 2026, 22:25 (6 hari dari sekarang).	{"complaint_id":13,"public_code":"0013\\/ringan\\/2026","team_id":1,"team_name":"Regu Aspal 01 - Lasusua","deadline":"2026-11-27T22:25:28+08:00"}	\N	2026-11-20 22:25:28	2026-11-20 22:25:28
20	10	FIELD_TASK_ASSIGNED	Tugas Lapangan untuk Regu Aspal 01 - Lasusua	Laporan #0013/ringan/2026 ("jalan wilayah ini rusak") ditugaskan ke regu Anda dengan batas waktu penanganan hingga 27 November 2026, 22:25 (6 hari dari sekarang).	{"complaint_id":13,"public_code":"0013\\/ringan\\/2026","team_id":1,"team_name":"Regu Aspal 01 - Lasusua","deadline":"2026-11-27T22:25:28+08:00"}	\N	2026-11-20 22:25:28	2026-11-20 22:25:28
21	11	FIELD_TASK_ASSIGNED	Tugas Lapangan untuk Regu Aspal 01 - Lasusua	Laporan #0013/ringan/2026 ("jalan wilayah ini rusak") ditugaskan ke regu Anda dengan batas waktu penanganan hingga 27 November 2026, 22:25 (6 hari dari sekarang).	{"complaint_id":13,"public_code":"0013\\/ringan\\/2026","team_id":1,"team_name":"Regu Aspal 01 - Lasusua","deadline":"2026-11-27T22:25:28+08:00"}	\N	2026-11-20 22:25:28	2026-11-20 22:25:28
22	12	FIELD_TASK_ASSIGNED	Tugas Lapangan untuk Regu Aspal 01 - Lasusua	Laporan #0013/ringan/2026 ("jalan wilayah ini rusak") ditugaskan ke regu Anda dengan batas waktu penanganan hingga 27 November 2026, 22:25 (6 hari dari sekarang).	{"complaint_id":13,"public_code":"0013\\/ringan\\/2026","team_id":1,"team_name":"Regu Aspal 01 - Lasusua","deadline":"2026-11-27T22:25:28+08:00"}	\N	2026-11-20 22:25:28	2026-11-20 22:25:28
23	6	COMPLAINT_DISPATCHED	Laporan Diverifikasi & Ditugaskan	Laporan #0013/ringan/2026 telah diverifikasi oleh Operator dan ditugaskan ke tim teknis lapangan (Regu Aspal 01 - Lasusua) dengan estimasi selesai 6 hari dari sekarang.	{"complaint_id":13}	2026-11-20 22:26:07	2026-11-20 22:25:28	2026-11-20 22:26:07
18	4	FIELD_TASK_ASSIGNED	Tugas Lapangan untuk Regu Aspal 01 - Lasusua	Laporan #0013/ringan/2026 ("jalan wilayah ini rusak") ditugaskan ke regu Anda dengan batas waktu penanganan hingga 27 November 2026, 22:25 (6 hari dari sekarang).	{"complaint_id":13,"public_code":"0013\\/ringan\\/2026","team_id":1,"team_name":"Regu Aspal 01 - Lasusua","deadline":"2026-11-27T22:25:28+08:00"}	2026-11-20 22:28:01	2026-11-20 22:25:28	2026-11-20 22:28:01
24	6	COMPLAINT_IN_PROGRESS	Laporan Sedang Dikerjakan	Pekerja Lapangan Tri Susilo Muin dari Regu Aspal 01 - Lasusua telah mengambil laporan Anda (#0013/ringan/2026: "jalan wilayah ini rusak") dan saat ini berstatus Dikerjakan untuk segera dilakukan perbaikan fisik di lokasi.	{"complaint_id":13,"public_code":"0013\\/ringan\\/2026","complaint_title":"jalan wilayah ini rusak"}	2026-11-20 22:28:45	2026-11-20 22:28:31	2026-11-20 22:28:45
\.


--
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.otp_verifications (id, email, code, purpose, expires_at, used_at, created_at, updated_at, attempts) FROM stdin;
2	warga@example.com	257825	FORGOT_PASSWORD	2026-09-24 01:47:42	2026-09-24 01:37:56	2026-09-24 01:37:42	2026-09-24 01:37:56	0
4	warga@example.com	559014	FORGOT_PASSWORD	2026-09-24 01:47:56	\N	2026-09-24 01:37:56	2026-09-24 01:37:56	0
5	dularafat@gmail.com	816904	FORGOT_PASSWORD	2026-09-24 01:55:04	2026-09-25 14:51:09	2026-09-24 01:45:04	2026-09-25 14:51:09	0
6	dularafat@gmail.com	310187	FORGOT_PASSWORD	2026-09-25 15:01:09	2026-09-29 23:11:00	2026-09-25 14:51:09	2026-09-29 23:11:00	0
7	dularafat@gmail.com	570407	FORGOT_PASSWORD	2026-09-29 23:21:00	2026-09-29 23:37:50	2026-09-29 23:11:00	2026-09-29 23:37:50	0
8	dularafat@gmail.com	181589	FORGOT_PASSWORD	2026-09-29 23:47:50	2026-09-29 23:43:14	2026-09-29 23:37:50	2026-09-29 23:43:14	0
9	dularafat@gmail.com	459290	FORGOT_PASSWORD	2026-09-30 00:02:27	2026-09-29 23:54:37	2026-09-29 23:52:27	2026-09-29 23:54:37	0
10	dularafat@gmail.com	945628	FORGOT_PASSWORD	2026-09-30 00:04:37	2026-09-29 23:57:36	2026-09-29 23:54:37	2026-09-29 23:57:36	0
12	budi@gmail.com	385093	FORGOT_PASSWORD	2026-09-30 00:08:09	\N	2026-09-29 23:58:09	2026-09-29 23:58:09	0
11	dularafat@gmail.com	882007	FORGOT_PASSWORD	2026-09-30 00:07:37	2026-09-30 00:00:24	2026-09-29 23:57:37	2026-09-30 00:00:24	0
13	dularafat@gmail.com	792689	FORGOT_PASSWORD	2026-09-30 00:10:24	2026-09-30 00:01:20	2026-09-30 00:00:24	2026-09-30 00:01:20	0
14	dularafat@gmail.com	242152	FORGOT_PASSWORD	2026-09-30 00:11:20	2026-09-30 00:15:12	2026-09-30 00:01:20	2026-09-30 00:15:12	0
15	dularafat@gmail.com	131442	FORGOT_PASSWORD	2026-09-30 00:25:12	2026-09-30 00:16:37	2026-09-30 00:15:12	2026-09-30 00:16:37	0
16	dularafat@gmail.com	937537	FORGOT_PASSWORD	2026-09-30 00:26:37	2026-09-30 00:34:03	2026-09-30 00:16:37	2026-09-30 00:34:03	0
17	dularafat@gmail.com	881043	FORGOT_PASSWORD	2026-09-30 00:44:03	2026-09-30 00:46:03	2026-09-30 00:34:03	2026-09-30 00:46:03	0
19	letsgetreal61@gmail.com	258815	REGISTER	2026-09-30 01:33:54	2026-09-30 01:26:09	2026-09-30 01:23:54	2026-09-30 01:26:09	0
20	letsgetreal61@gmail.com	126135	REGISTER	2026-09-30 01:36:09	2026-09-30 01:26:52	2026-09-30 01:26:09	2026-09-30 01:26:52	0
18	dularafat@gmail.com	963208	FORGOT_PASSWORD	2026-09-30 00:56:03	2026-09-30 01:47:25	2026-09-30 00:46:03	2026-09-30 01:47:25	0
22	dularafat@gmail.com	889471	FORGOT_PASSWORD	2026-09-30 01:57:25	2026-09-30 01:47:55	2026-09-30 01:47:25	2026-09-30 01:47:55	0
21	letsgetreal61@gmail.com	236192	REGISTER	2026-09-30 01:36:52	2026-09-30 02:04:38	2026-09-30 01:26:52	2026-09-30 02:04:38	0
25	letsgetreal61@gmail.com	452577	REGISTER	2026-09-30 02:14:38	\N	2026-09-30 02:04:38	2026-09-30 02:04:38	0
23	dularafat@gmail.com	201825	FORGOT_PASSWORD	2026-09-30 01:57:55	2026-09-30 02:06:06	2026-09-30 01:47:55	2026-09-30 02:06:06	0
26	dularafat@gmail.com	527949	FORGOT_PASSWORD	2026-09-30 02:16:06	2026-09-30 02:06:55	2026-09-30 02:06:06	2026-09-30 02:06:55	1
27	letsgetreal61@gmail.com	218771	FORGOT_PASSWORD	2026-11-19 01:51:05	\N	2026-11-19 01:41:05	2026-11-19 01:41:05	0
1	audit_tester@kabupaten.go.id	983431	REGISTER	2026-09-24 01:47:42	2026-09-24 01:37:42	2026-09-24 01:37:42	2026-09-24 01:37:42	0
3	audit_tester@kabupaten.go.id	929382	REGISTER	2026-09-24 01:47:56	2026-09-24 01:37:56	2026-09-24 01:37:56	2026-09-24 01:37:56	0
28	muindulafat@gmail.com	121720	REGISTER	2026-11-20 16:54:49	\N	2026-11-20 16:44:49	2026-11-20 16:44:49	0
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
2	App\\Models\\User	1	auth-token	34c92185b896f16a82b344b663b9442654b11aafc7389cf0ce6a0199af438de7	["*"]	\N	\N	2026-09-22 20:54:29	2026-09-22 20:54:29
3	App\\Models\\User	1	auth-token	f4547426bb46e0e72dbefe4354dc0bfcdcc6164abee449e529748fb24c9c45c1	["*"]	\N	\N	2026-09-22 20:54:46	2026-09-22 20:54:46
4	App\\Models\\User	1	auth-token	722224889a177f9e9c40556c24c9f0718db725f8d4da2ac221c5457613a729df	["*"]	\N	\N	2026-09-22 20:55:03	2026-09-22 20:55:03
72	App\\Models\\User	24	auth-token	b098f91ac3ddd809ac171bde250fbf80b596e5064b538d568dc3323d06a97c65	["*"]	2026-11-19 00:07:33	\N	2026-11-18 22:40:28	2026-11-19 00:07:33
18	App\\Models\\User	13	auth-token	3c6d2b1fe9266f38cd7e89d390a5951e565f6d01e097590de9172dd4623f2409	["*"]	\N	\N	2026-09-23 03:28:45	2026-09-23 03:28:45
58	App\\Models\\User	6	auth-token	90783b629e6b234c94ae7ea99852e96387f6e07ab4c8cc6ef31174b0e0ffcbc4	["*"]	2026-10-13 13:39:29	\N	2026-10-13 13:39:21	2026-10-13 13:39:29
23	App\\Models\\User	13	auth-token	7f593b9f5680af37d4dea794b7fb058c777aca3940280a33c2d90cc0965514a1	["*"]	\N	\N	2026-09-24 02:34:10	2026-09-24 02:34:10
26	App\\Models\\User	13	auth-token	2fc41e0035d0110c40b9d154f46a706a5f866ab8aec2d76b01ad472ffdba3038	["*"]	\N	\N	2026-09-24 02:50:34	2026-09-24 02:50:34
30	App\\Models\\User	13	auth-token	1fd7b3799602f56ba9805c27b7aa3127b59882410b8734f873c2acec81397387	["*"]	\N	\N	2026-09-24 03:28:12	2026-09-24 03:28:12
62	App\\Models\\User	6	auth-token	e16e46922e19d47fa0ae91a296f21d3cde72b1954602b8e91af842e9e8d9a7c2	["*"]	2026-11-06 01:12:49	\N	2026-11-02 19:41:12	2026-11-06 01:12:49
47	App\\Models\\User	18	auth-token	03dcbb7057466bd17afab09ac607885d36f19490e91ef091852086e36e51c46c	["*"]	2026-09-30 00:29:17	\N	2026-09-30 00:29:10	2026-09-30 00:29:17
85	App\\Models\\User	4	auth-token	de46cb977ecf758df6d696dd921a9d167c13fda86d5ec4b1fac5e1fd9e720592	["*"]	2026-11-20 15:22:16	\N	2026-11-19 18:02:10	2026-11-20 15:22:16
93	App\\Models\\User	4	auth-token	0935a4ac90496ad9d3d582865d5783ed12537042e0ff71301d0aa72c31247ba6	["*"]	2026-11-20 23:17:59	\N	2026-11-20 22:50:22	2026-11-20 23:17:59
90	App\\Models\\User	4	auth-token	3a7a8137d3176e89e75d26b5c61deb86f5365e78c2b8f5281b1c298e755240d1	["*"]	2026-11-20 22:30:37	\N	2026-11-20 22:26:43	2026-11-20 22:30:37
95	App\\Models\\User	6	auth-token	a0a41c85a3373d1f00d1a7f2c1d28f32702225304c89cb2c9df75f7ea047fb28	["*"]	2026-11-20 23:19:43	\N	2026-11-20 23:18:59	2026-11-20 23:19:43
\.


--
-- Data for Name: regions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.regions (id, province, regency, district, village, latitude, longitude, created_at, updated_at, geom) FROM stdin;
11	Sulawesi Tenggara	Kabupaten	Kecamatan Tiwu	Desa Tiwu	-3.6163000	120.9765000	2026-09-04 03:10:59	2026-09-04 03:36:19	\N
8	Sulawesi Tenggara	Kabupaten	Kecamatan Tolala	Desa Tolala	-2.5732000	121.1485000	2026-09-04 03:10:59	2026-09-04 03:36:19	\N
1	Sulawesi Tenggara	Kabupaten	Kecamatan Lasusua	Kel. Lasusua	-3.5020452	120.8865523	2026-09-04 03:10:59	2026-09-04 03:40:38	\N
15	Sulawesi Tenggara	Kabupaten	Kecamatan Wawo	Desa Wawo	-3.6855285	121.0387784	2026-09-04 03:10:59	2026-09-04 03:46:00	\N
2	Sulawesi Tenggara	Kabupaten	Kecamatan Watunohu	Desa Watunohu	-3.3023861	120.9768644	2026-09-04 03:10:59	2026-09-04 03:47:23	\N
3	Sulawesi Tenggara	Kabupaten	Kecamatan Ngapa	Kel. Lapai	-3.2156586	121.0464536	2026-09-04 03:10:59	2026-09-04 03:47:35	\N
4	Sulawesi Tenggara	Kabupaten	Kecamatan Pakue	Kel. Olo-Oloho	-3.1142656	121.0742209	2026-09-04 03:10:59	2026-09-04 03:47:53	\N
5	Sulawesi Tenggara	Kabupaten	Kecamatan Pakue Tengah	Desa Latali	-3.0841141	121.0766454	2026-09-04 03:10:59	2026-09-04 03:48:02	\N
6	Sulawesi Tenggara	Kabupaten	Kecamatan Pakue Utara	Desa Pakue	-3.0417490	121.1003393	2026-09-04 03:10:59	2026-09-04 03:48:10	\N
12	Sulawesi Tenggara	Kabupaten	Kecamatan Katoi	Desa Katoi	-3.4682000	120.8931000	2026-09-04 03:10:59	2026-09-04 03:36:19	\N
10	Sulawesi Tenggara	Kabupaten	Kecamatan Kodeoha	Desa Mala-Mala	-3.3986000	120.9168000	2026-09-04 03:10:59	2026-09-04 03:36:19	\N
13	Sulawesi Tenggara	Kabupaten	Kecamatan Lambai	Desa Lambai	-3.5781000	120.9189000	2026-09-04 03:10:59	2026-09-04 03:36:19	\N
9	Sulawesi Tenggara	Kabupaten	Kecamatan Porehu	Desa Porehu	-2.9682000	121.2189000	2026-09-04 03:10:59	2026-09-04 03:36:19	\N
14	Sulawesi Tenggara	Kabupaten	Kecamatan Rante Angin	Kel. Rante Angin	-3.6425000	121.0112000	2026-09-04 03:10:59	2026-09-04 03:36:19	\N
7	Sulawesi Tenggara	Kabupaten	Kecamatan Batu Putih	Kel. Batu Putih	-3.0095527	121.0953903	2026-09-04 03:10:59	2026-09-04 03:48:17	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
rl3mXYI1O2KBgz8U0XZclM0lVaMtXCjlMk0XELsQ	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJIRTBXcklBalBRdTQ3M29WS3ZPOE56V0hQMEZUdWRnckd6OFluT2h4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9tb2JpbGUtcHJpbnRcL3BldHVnYXMtcGVyZm9ybWFuY2VcLzI0P2V4cGlyZXM9MTc5NTA3ODUzNSZtb2RlPWluZGl2aWR1YWwmc2lnbmF0dXJlPWFiYWYxMThhZTk2YjU4MWUyYzhiMjdmMzA4MTE3ZDY2ZmVjZGM1NzA2MDU1MWNkZjBmOTdiOThkNzg2MWZkOTciLCJyb3V0ZSI6Im1vYmlsZS5wZXR1Z2FzLnBlcmZvcm1hbmNlLnByaW50In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=	1795076821
3PeBD7HPTYelf1gRYZDaYStPk7js9s1061zmkW65	\N	100.82.242.85	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Mobile Safari/537.36	eyJfdG9rZW4iOiJLa0lxTXNIR2pFQXVaWGxhM1VvdVJ0TFRLYzlsQUdsaGRGNklNTFFjIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEwMC44Ni4zMC40Mjo4MDAwXC9tb2JpbGUtcHJpbnRcL3BldHVnYXMtcGVyZm9ybWFuY2VcLzQ/ZXhwaXJlcz0xNzk1MDc5MTI4Jm1vZGU9aW5kaXZpZHVhbCZzaWduYXR1cmU9ZTNiYjQ5ODczMzk5OGY5MTFlODMwMzFkNmQ0YjE5NzBkMzFiZjgxMThiMDBiZTlmOTQwNzcxMjFmMDhlYzlhYiIsInJvdXRlIjoibW9iaWxlLnBldHVnYXMucGVyZm9ybWFuY2UucHJpbnQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==	1795077360
nau5n9djarw7WsTYRc1IvbfwVFVgfcHEP9aioMOo	\N	34.91.19.29	Scrapy/2.17.0 (+https://scrapy.org)	eyJfdG9rZW4iOiJFUmhxWmJ5OVI4R0xkaDh3c3dpU2ZEVDZkRE84NzVZa2FCRnUwMEtKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795072591
cgGUXOLIFSjUvNep38zVWZw2daSmFKm97jOzZVrk	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJGOWJaSk5ESmVaVkJUcEZjZVpBQ3RwUUJpNHUzWHBpdDRSVXplZG45IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795080642
hbpCiTfta1vgSdPrE8XTaB674h3BXrLITqm8xd9Z	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiI4Mng0bDRQOHhSTGJyTG5zZEtXcXBvdUFJZ2tsdWxiTUtIMWFoUFlLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795080645
Xfa8IdrcLlUe6mKpRj4IFSZx2ucQ2GexXdyi6Jhb	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJZWVF6bk1MZG8yZ1lOc2Z2dDRNZTlOUHNJZ1RRU21hTFNaaTJHTG95IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795080646
1vVGy1Nd3GGyk4vscHHTmleuRkkqUz0RjYpBRJ7y	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJwcWU0Y2VrdjZOWG1nUlg0QnVsWHZJWjNRdHJoNWJ4TnBFT29CZEpNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795080647
LUhdG0PbsL41kLJMZMtWMeGc726cXokJr6ORYvYs	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJQUFZHV1l1RWhaTWN2SGhsM0kwUzRTU1ZQWjFOU2I1QWllTHZxSE5uIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795080648
wkBuapRUZ2U02WNnZUwTuSAXvv7f7ZnvB8HVVNpO	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJKWUhVQ0g1Sks4THZHakdsYVg0UldBWThPWkNkejBEZlZ6bmlNdlVnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldFwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=	1795080649
xk5btkOpcNMwOErvHRsc7E6oJC1DgrfjLDAVjOKi	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJQcVJjNHl6NXdJellpUmZNc3I4UnFOelk2dDBlRWlSZE93OXU2cmtzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldFwvZG93bmxvYWQtYXBrIiwicm91dGUiOiJkb3dubG9hZC5hcGsifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==	1795080651
p9Qtlmny34GoaskeEtkzrhTqFzM7JLbThr2vF4X0	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJqVTc5aTRGcGo3MzIwQWJBZkM2VkhjaUxEbTBIZVBuNUFFNmd0UmxaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795080652
GqybhLlRjiVGGD3HbnOR4fSkXQYemGUE9igvi6H3	\N	5.102.169.102	Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36	eyJfdG9rZW4iOiIzQzZ5WnMyN05hMEFOU0l4V1hkVkYycXA1bTc1M1BtQW9yQkxXdndHIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9kdWwtMTM3OC50YWlsMDliZDdhLnRzLm5ldCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1795080653
Vnjbo2voHC8n9CRcnirCzCfaV3RP6N90T0P5rpzQ	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJxQkhTTzFOS1NtV1NpR0kxY2JxaVp1d2NnaWJLREJaWldtSFhRQldzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=	1795088489
\.


--
-- Data for Name: supports; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.supports (id, complaint_id, user_id, created_at, updated_at) FROM stdin;
2	8	6	2026-11-20 15:42:02	2026-11-20 15:42:02
\.


--
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.system_settings (id, key, value, type, description, created_at, updated_at) FROM stdin;
1	duplicate_radius_m	100	integer	Radius pencarian duplikasi laporan dalam meter	2026-09-02 10:39:13	2026-09-02 10:39:13
2	upload_max_mb	10	integer	Batas maksimal ukuran upload foto dalam MB	2026-09-02 10:39:13	2026-09-02 10:39:13
3	priority_weights	{"severity_weight":0.35,"support_weight":0.2,"location_weight":0.2,"age_weight":0.15,"duplicate_weight":0.1}	json	Bobot kalkulasi skor prioritas	2026-09-02 10:39:13	2026-09-02 10:39:13
4	dispatch_mode	HYBRID	string	Mode Penugasan Regu Lapangan	2026-09-23 02:32:39	2026-09-23 03:22:26
\.


--
-- Data for Name: team_members; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.team_members (id, team_id, user_id, role, created_at, updated_at) FROM stdin;
1	1	4	LEADER	2026-09-23 02:32:41	2026-09-23 02:32:41
3	1	9	MEMBER	2026-09-23 02:32:41	2026-09-23 02:32:41
4	1	10	MEMBER	2026-09-23 02:32:41	2026-09-23 02:32:41
5	1	11	MEMBER	2026-09-23 02:32:41	2026-09-23 02:32:41
6	1	12	MEMBER	2026-09-23 02:32:41	2026-09-23 02:32:41
8	2	14	MEMBER	2026-09-23 02:32:41	2026-09-23 02:32:41
10	2	16	MEMBER	2026-09-23 02:32:41	2026-09-23 02:32:41
11	2	8	MEMBER	2026-09-25 00:42:25	2026-09-25 00:42:25
7	2	13	MEMBER	2026-09-23 02:32:41	2026-11-18 06:52:44
9	2	15	LEADER	2026-09-23 02:32:41	2026-11-18 06:52:44
\.


--
-- Data for Name: teams; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.teams (id, name, code, district, department_id, leader_id, description, is_active, created_at, updated_at) FROM stdin;
1	Regu Aspal 01 - Lasusua	REGU-01	Kecamatan Lasusua	1	4	Regu spesialis tambal aspal (coldmix/hotmix), perataan badan jalan, dan armada dump truck wilayah Lasusua dan sekitarnya.	t	2026-09-23 02:32:41	2026-09-23 02:32:41
2	Regu Reaksi Cepat 02 - Semua Wilayah	REGU-02	Semua Wilayah	1	15	Regu mobil tangga hidrolik penerangan jalan umum, pembersihan longsoran bahu jalan darurat, dan jembatan.	t	2026-09-23 02:32:41	2026-11-18 06:52:44
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, phone, email_verified_at, password, role, region_id, is_anonymous, remember_token, created_at, updated_at, avatar, last_login_ip, last_login_user_agent, last_login_at, is_active, deactivation_reason, deactivated_at, deactivated_by) FROM stdin;
7	Operator Verifikasi	operator@kabupaten.go.id	\N	\N	$2y$12$3LoNzvLZcUuEzx3KZPkrA.WQ.H8vivQ5mPSebWGxJ0xN5R15a.INi	OPERATOR	1	f	saD1rvf73tOpgZUqjA8mdHwyN5FR7Wb8RMsObpBLTDW24LxJiiBoc2s8NLA8	2026-09-02 16:10:17	2026-11-18 06:02:29	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-18 06:02:29	t	\N	\N	\N
2	Kepala Daerah Kabupaten	bupati@kabupaten.go.id	081100000002	\N	$2y$12$iTuACittsfeL6rGSfcRUL.wvQwuyxFrckm6xhTE58LF7ugbmDE0zu	KEPALA_DAERAH	1	f	mYN9yd6grpx0StxL14ZAoqCt0Ezowymwu1LLe27ZDX2reVaQTUjjIzYzCKjr	2026-09-02 10:39:12	2026-11-18 06:02:03	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-18 06:02:03	t	\N	\N	\N
24	Abdullah	letsgetreal61@gmail.com	081100001323	\N	$2y$12$gP4YdIM6jsMEgxiTM8KeQ.FmLZIcW2.ypGjGtafcGfl2IUkjCbTYC	PETUGAS	\N	f	2HzLFKq0yVO4UVR85mVga6K5EgoUQATI5IE8lHNpB3DqMTCtj88WJoX1UkZC	2026-09-30 15:42:57	2026-11-19 16:08:42	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-19 16:08:42	t	\N	\N	\N
5	Budi	budi@gmail.com	081234567890	\N	$2y$12$Y47ezet7Ua1kq9zkvya.VeFhir6XvyCvVQPl3pgiSzACKIujvX9pq	WARGA	1	f	\N	2026-09-02 10:39:13	2026-11-16 21:07:12	\N	\N	\N	\N	f	menyalahgunakan sistem	2026-11-16 21:07:12	1
8	Budi Santoso	budi.petugas@kabupaten.go.id	081234567811	\N	$2y$12$3YiD9kcM6ZxpAH2l.fNCNO1VA2l.4.nFEg6dNZc2VTPg5Tq7VkH52	PETUGAS	\N	f	\N	2026-09-23 02:32:39	2026-09-23 02:32:39	\N	\N	\N	\N	t	\N	\N	\N
9	Agus Prasetyo	agus.petugas@kabupaten.go.id	081234567812	\N	$2y$12$BcaKrU7OSUkQH9RhQK0fau6VLomvt09/jlAryVSrd2xqEJT4oM5SK	PETUGAS	\N	f	\N	2026-09-23 02:32:39	2026-09-23 02:32:39	\N	\N	\N	\N	t	\N	\N	\N
10	Dedi Kurniawan	dedi.petugas@kabupaten.go.id	081234567813	\N	$2y$12$8EoInRMmZPIZesRKCC.g3Oue9e4uPfXGK9g7Nc63UCeMWfyIhFIAa	PETUGAS	\N	f	\N	2026-09-23 02:32:40	2026-09-23 02:32:40	\N	\N	\N	\N	t	\N	\N	\N
11	Eko Wahyudi	eko.petugas@kabupaten.go.id	081234567814	\N	$2y$12$EOsxV7gl8VpBVjJoUaIBF.DUilZ5Zkq9mJZkZYFEvVpqFhU.zLXEO	PETUGAS	\N	f	\N	2026-09-23 02:32:40	2026-09-23 02:32:40	\N	\N	\N	\N	t	\N	\N	\N
12	Fajar Hidayat	fajar.petugas@kabupaten.go.id	081234567815	\N	$2y$12$lHaGseHFEN90X3qqQd8xROzKYwgj9OS2lAUllJksbbWew9Q0.J.v2	PETUGAS	\N	f	\N	2026-09-23 02:32:40	2026-09-23 02:32:40	\N	\N	\N	\N	t	\N	\N	\N
13	Hendra Saputra	hendra.petugas@kabupaten.go.id	081234567816	\N	$2y$12$vnlwFutESxs45JK.An4G6.sm7JYWWL3SHB4DrsgtttahqftlPAWbK	PETUGAS	\N	f	\N	2026-09-23 02:32:40	2026-09-23 02:32:40	\N	\N	\N	\N	t	\N	\N	\N
14	Irfan Maulana	irfan.petugas@kabupaten.go.id	081234567817	\N	$2y$12$ySyYUz5qefxnLeGxHQBfTeghDwG7toRS1SLwhhTaVFr2fV51twuvC	PETUGAS	\N	f	\N	2026-09-23 02:32:40	2026-09-23 02:32:40	\N	\N	\N	\N	t	\N	\N	\N
15	Joko Susilo	joko.petugas@kabupaten.go.id	081234567818	\N	$2y$12$EXJ91DGKNhrCiRGgPliXU.D5miVKOYcv.pmUFv2BnYsncy4gqxrwW	PETUGAS	\N	f	\N	2026-09-23 02:32:41	2026-09-23 02:32:41	\N	\N	\N	\N	t	\N	\N	\N
16	Kurniawan Pratama	kurniawan.petugas@kabupaten.go.id	081234567819	\N	$2y$12$4mfLpj9535ujBQHy4IZhVelhO5pz5xupJpTVpRZLHo8cDsHM1ZaeG	PETUGAS	\N	f	\N	2026-09-23 02:32:41	2026-09-23 02:32:41	\N	\N	\N	\N	t	\N	\N	\N
25	Siti Rahmawati	siti.rahmawati@warga.kabupaten.id	081244556601	\N	$2y$12$BzsB7WW823kZurD7h9p64eVb4L0GFMBKX/QahvuGR0mkQIcWzIvgy	WARGA	\N	f	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N	\N	\N	\N	t	\N	\N	\N
26	Hendrik Saputra	hendrik.saputra@warga.kabupaten.id	081244556602	\N	$2y$12$ayKHQK77P/v/zaNmSJdPkusSC1Zpy7B6IQXTGYD.U4Pc6SQBWAv6C	WARGA	\N	f	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N	\N	\N	\N	t	\N	\N	\N
27	Nurhayati	nurhayati@warga.kabupaten.id	081244556603	\N	$2y$12$jSXwwl.0U2TZpB/SMbVAUOpZVeGQcO1jalF1DXxPE226NJ1JmJYpi	WARGA	\N	f	\N	2026-11-19 13:59:44	2026-11-19 13:59:44	\N	\N	\N	\N	t	\N	\N	\N
4	Tri Susilo Muin	tri@gmail.com	081100000004	\N	$2y$12$/OTYv6kJm5BoJDJT7LZvpulrzhCczDiOVsZ5GNqMwwqP8HUL75kMW	PETUGAS	1	f	\N	2026-09-02 10:39:13	2026-11-20 23:16:44	\N	114.10.135.181	Dart/3.13 (dart:io)	2026-11-20 23:16:44	t	\N	\N	\N
6	Dul Arafat Muin	dularafat@gmail.com	081344697178	\N	$2y$12$iJSgNPMv9Eij2PYNynmD8eb7p.03lf0.xk9YxHzvXfIhJc5nNWsJ.	WARGA	1	f	\N	2026-09-02 11:05:41	2026-11-20 23:18:59	\N	114.10.135.181	Dart/3.13 (dart:io)	2026-11-20 23:18:59	t	\N	\N	\N
1	Super Administrator	admin@kabupaten.go.id	081100000001	\N	$2y$12$N9K/Zg.YcIkQdKplG./IB.eeRo6K5dyBMyK9NCi0HAkMBQiEdRMfe	ADMIN	1	f	d2p8CHQWyt0PfWWPfnHRKXuCoOLOW2lSG1RqV1dmdEpb2J1ximLM8TDTQSRu	2026-09-02 10:39:12	2026-11-20 22:15:02	\N	180.245.204.39	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-20 22:15:02	t	\N	\N	\N
\.


--
-- Name: ai_assessments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ai_assessments_id_seq', 1, false);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 3, true);


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_id_seq', 6, true);


--
-- Name: chat_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.chat_messages_id_seq', 376, true);


--
-- Name: complaint_photos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaint_photos_id_seq', 16, true);


--
-- Name: complaint_ratings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaint_ratings_id_seq', 1, false);


--
-- Name: complaint_status_histories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaint_status_histories_id_seq', 25, true);


--
-- Name: complaints_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaints_id_seq', 13, true);


--
-- Name: departments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departments_id_seq', 3, true);


--
-- Name: dispositions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.dispositions_id_seq', 5, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 26, true);


--
-- Name: notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.notifications_id_seq', 24, true);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 28, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 95, true);


--
-- Name: regions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.regions_id_seq', 18, true);


--
-- Name: supports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.supports_id_seq', 2, true);


--
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 4, true);


--
-- Name: team_members_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.team_members_id_seq', 11, true);


--
-- Name: teams_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.teams_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 27, true);


--
-- Name: ai_assessments ai_assessments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_assessments
    ADD CONSTRAINT ai_assessments_pkey PRIMARY KEY (id);


--
-- Name: app_db_sync_marker app_db_sync_marker_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_db_sync_marker
    ADD CONSTRAINT app_db_sync_marker_pkey PRIMARY KEY (version);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: chat_messages chat_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_pkey PRIMARY KEY (id);


--
-- Name: chat_sessions chat_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_sessions
    ADD CONSTRAINT chat_sessions_pkey PRIMARY KEY (id);


--
-- Name: complaint_photos complaint_photos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_photos
    ADD CONSTRAINT complaint_photos_pkey PRIMARY KEY (id);


--
-- Name: complaint_ratings complaint_ratings_complaint_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_ratings
    ADD CONSTRAINT complaint_ratings_complaint_id_unique UNIQUE (complaint_id);


--
-- Name: complaint_ratings complaint_ratings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_ratings
    ADD CONSTRAINT complaint_ratings_pkey PRIMARY KEY (id);


--
-- Name: complaint_status_histories complaint_status_histories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_status_histories
    ADD CONSTRAINT complaint_status_histories_pkey PRIMARY KEY (id);


--
-- Name: complaints complaints_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaints
    ADD CONSTRAINT complaints_pkey PRIMARY KEY (id);


--
-- Name: complaints complaints_public_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaints
    ADD CONSTRAINT complaints_public_code_unique UNIQUE (public_code);


--
-- Name: departments departments_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_code_unique UNIQUE (code);


--
-- Name: departments departments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_pkey PRIMARY KEY (id);


--
-- Name: dispositions dispositions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dispositions
    ADD CONSTRAINT dispositions_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: otp_verifications otp_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications
    ADD CONSTRAINT otp_verifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: regions regions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regions
    ADD CONSTRAINT regions_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: supports supports_complaint_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supports
    ADD CONSTRAINT supports_complaint_id_user_id_unique UNIQUE (complaint_id, user_id);


--
-- Name: supports supports_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supports
    ADD CONSTRAINT supports_pkey PRIMARY KEY (id);


--
-- Name: system_settings system_settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_key_unique UNIQUE (key);


--
-- Name: system_settings system_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_pkey PRIMARY KEY (id);


--
-- Name: team_members team_members_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_pkey PRIMARY KEY (id);


--
-- Name: team_members team_members_team_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_team_id_user_id_unique UNIQUE (team_id, user_id);


--
-- Name: teams teams_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teams
    ADD CONSTRAINT teams_code_unique UNIQUE (code);


--
-- Name: teams teams_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teams
    ADD CONSTRAINT teams_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: audit_logs_actor_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_actor_id_created_at_index ON public.audit_logs USING btree (actor_id, created_at);


--
-- Name: audit_logs_entity_type_entity_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_entity_type_entity_id_index ON public.audit_logs USING btree (entity_type, entity_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: complaint_ratings_rating_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX complaint_ratings_rating_created_at_index ON public.complaint_ratings USING btree (rating, created_at);


--
-- Name: complaint_status_histories_complaint_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX complaint_status_histories_complaint_id_created_at_index ON public.complaint_status_histories USING btree (complaint_id, created_at);


--
-- Name: complaints_parent_complaint_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX complaints_parent_complaint_id_index ON public.complaints USING btree (parent_complaint_id);


--
-- Name: complaints_priority_level_priority_score_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX complaints_priority_level_priority_score_index ON public.complaints USING btree (priority_level, priority_score);


--
-- Name: complaints_status_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX complaints_status_created_at_index ON public.complaints USING btree (status, created_at);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: notifications_user_id_read_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_user_id_read_at_index ON public.notifications USING btree (user_id, read_at);


--
-- Name: otp_verifications_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_email_index ON public.otp_verifications USING btree (email);


--
-- Name: otp_verifications_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_expires_at_index ON public.otp_verifications USING btree (expires_at);


--
-- Name: otp_verifications_purpose_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_purpose_index ON public.otp_verifications USING btree (purpose);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: ai_assessments ai_assessments_complaint_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_assessments
    ADD CONSTRAINT ai_assessments_complaint_id_foreign FOREIGN KEY (complaint_id) REFERENCES public.complaints(id) ON DELETE CASCADE;


--
-- Name: audit_logs audit_logs_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: categories categories_department_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_department_id_foreign FOREIGN KEY (department_id) REFERENCES public.departments(id) ON DELETE CASCADE;


--
-- Name: chat_messages chat_messages_chat_session_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_chat_session_id_foreign FOREIGN KEY (chat_session_id) REFERENCES public.chat_sessions(id) ON DELETE CASCADE;


--
-- Name: chat_sessions chat_sessions_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_sessions
    ADD CONSTRAINT chat_sessions_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: complaint_photos complaint_photos_complaint_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_photos
    ADD CONSTRAINT complaint_photos_complaint_id_foreign FOREIGN KEY (complaint_id) REFERENCES public.complaints(id) ON DELETE CASCADE;


--
-- Name: complaint_ratings complaint_ratings_complaint_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_ratings
    ADD CONSTRAINT complaint_ratings_complaint_id_foreign FOREIGN KEY (complaint_id) REFERENCES public.complaints(id) ON DELETE CASCADE;


--
-- Name: complaint_ratings complaint_ratings_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_ratings
    ADD CONSTRAINT complaint_ratings_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: complaint_status_histories complaint_status_histories_changed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_status_histories
    ADD CONSTRAINT complaint_status_histories_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: complaint_status_histories complaint_status_histories_complaint_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaint_status_histories
    ADD CONSTRAINT complaint_status_histories_complaint_id_foreign FOREIGN KEY (complaint_id) REFERENCES public.complaints(id) ON DELETE CASCADE;


--
-- Name: complaints complaints_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaints
    ADD CONSTRAINT complaints_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE RESTRICT;


--
-- Name: complaints complaints_region_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaints
    ADD CONSTRAINT complaints_region_id_foreign FOREIGN KEY (region_id) REFERENCES public.regions(id) ON DELETE SET NULL;


--
-- Name: complaints complaints_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.complaints
    ADD CONSTRAINT complaints_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: dispositions dispositions_assigned_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dispositions
    ADD CONSTRAINT dispositions_assigned_by_foreign FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dispositions dispositions_assigned_to_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dispositions
    ADD CONSTRAINT dispositions_assigned_to_foreign FOREIGN KEY (assigned_to) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dispositions dispositions_complaint_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dispositions
    ADD CONSTRAINT dispositions_complaint_id_foreign FOREIGN KEY (complaint_id) REFERENCES public.complaints(id) ON DELETE CASCADE;


--
-- Name: dispositions dispositions_department_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dispositions
    ADD CONSTRAINT dispositions_department_id_foreign FOREIGN KEY (department_id) REFERENCES public.departments(id) ON DELETE CASCADE;


--
-- Name: dispositions dispositions_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dispositions
    ADD CONSTRAINT dispositions_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.teams(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: supports supports_complaint_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supports
    ADD CONSTRAINT supports_complaint_id_foreign FOREIGN KEY (complaint_id) REFERENCES public.complaints(id) ON DELETE CASCADE;


--
-- Name: supports supports_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supports
    ADD CONSTRAINT supports_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: team_members team_members_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.teams(id) ON DELETE CASCADE;


--
-- Name: team_members team_members_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: teams teams_department_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teams
    ADD CONSTRAINT teams_department_id_foreign FOREIGN KEY (department_id) REFERENCES public.departments(id) ON DELETE SET NULL;


--
-- Name: teams teams_leader_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teams
    ADD CONSTRAINT teams_leader_id_foreign FOREIGN KEY (leader_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--


