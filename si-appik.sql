--
-- PostgreSQL database dump
--


-- Dumped from database version 18.6
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
--



--
--



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
    user_id bigint NOT NULL,
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
249	01a1ed7f-4a77-703b-9096-683e3b40f050	BOT	Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?	\N	2026-11-18 15:11:44	2026-11-18 15:11:44
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
\.


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, user_id, type, title, body, data_json, read_at, created_at, updated_at) FROM stdin;
13	4	FIELD_TASK_ASSIGNED	Tugas Lapangan Baru	Laporan #ADU/0005/11/09/2026/RINGAN ("Laporan Perbaikan Susunan Paving Block Trotoar") telah ditugaskan. Silakan mengambil dan mengerjakan laporan ini dengan batas waktu penanganan hingga 16 September 2026, 02:02 (4 hari dari sekarang).	{"complaint_id":5,"public_code":"ADU\\/0005\\/11\\/09\\/2026\\/RINGAN","deadline":"2026-09-16T02:02:44+08:00"}	2026-09-11 17:19:35	2026-09-11 02:02:44	2026-09-11 17:19:35
10	4	FIELD_TASK_ASSIGNED	Tugas Lapangan Baru	Laporan #ADU-0003 ("Lubang Aspal") telah ditugaskan. Silakan mengambil dan mengerjakan laporan ini dengan batas waktu penanganan hingga 13 September 2026, 03:01 (2 hari dari sekarang).	{"complaint_id":3,"public_code":"ADU-0003","deadline":"2026-09-13T03:01:20+08:00"}	2026-09-11 17:19:41	2026-09-10 03:01:20	2026-09-11 17:19:41
17	27	COMPLAINT_REJECTED	Laporan Ditolak Operator	Laporan #0012/ringan/2026 tidak dapat diproses. Alasan: Laporan tidak memenuhi kriteria kelayakan atau bukti lapangan tidak sesuai.	{"complaint_id":12}	\N	2026-11-19 14:31:34	2026-11-19 14:31:34
16	6	COMPLAINT_IN_PROGRESS	Laporan Sedang Dikerjakan	Pekerja Lapangan telah mengambil laporan Anda (#ADU-0003: "Lubang Aspal") dan saat ini berstatus Dikerjakan untuk segera dilakukan perbaikan fisik di lokasi.	{"complaint_id":3,"public_code":"ADU-0003","complaint_title":"Lubang Aspal"}	2026-09-23 03:26:38	2026-09-23 02:56:39	2026-09-23 03:26:38
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
79	App\\Models\\User	4	auth-token	1ef457cb25aac72bf5bdbb1d954dc3e15aed1916f1b5c442a9b114dba19c4452	["*"]	2026-11-19 19:25:52	\N	2026-11-19 16:45:59	2026-11-19 19:25:52
72	App\\Models\\User	24	auth-token	b098f91ac3ddd809ac171bde250fbf80b596e5064b538d568dc3323d06a97c65	["*"]	2026-11-19 00:07:33	\N	2026-11-18 22:40:28	2026-11-19 00:07:33
85	App\\Models\\User	4	auth-token	de46cb977ecf758df6d696dd921a9d167c13fda86d5ec4b1fac5e1fd9e720592	["*"]	2026-11-19 19:27:44	\N	2026-11-19 18:02:10	2026-11-19 19:27:44
18	App\\Models\\User	13	auth-token	3c6d2b1fe9266f38cd7e89d390a5951e565f6d01e097590de9172dd4623f2409	["*"]	\N	\N	2026-09-23 03:28:45	2026-09-23 03:28:45
58	App\\Models\\User	6	auth-token	90783b629e6b234c94ae7ea99852e96387f6e07ab4c8cc6ef31174b0e0ffcbc4	["*"]	2026-10-13 13:39:29	\N	2026-10-13 13:39:21	2026-10-13 13:39:29
23	App\\Models\\User	13	auth-token	7f593b9f5680af37d4dea794b7fb058c777aca3940280a33c2d90cc0965514a1	["*"]	\N	\N	2026-09-24 02:34:10	2026-09-24 02:34:10
26	App\\Models\\User	13	auth-token	2fc41e0035d0110c40b9d154f46a706a5f866ab8aec2d76b01ad472ffdba3038	["*"]	\N	\N	2026-09-24 02:50:34	2026-09-24 02:50:34
30	App\\Models\\User	13	auth-token	1fd7b3799602f56ba9805c27b7aa3127b59882410b8734f873c2acec81397387	["*"]	\N	\N	2026-09-24 03:28:12	2026-09-24 03:28:12
62	App\\Models\\User	6	auth-token	e16e46922e19d47fa0ae91a296f21d3cde72b1954602b8e91af842e9e8d9a7c2	["*"]	2026-11-06 01:12:49	\N	2026-11-02 19:41:12	2026-11-06 01:12:49
47	App\\Models\\User	18	auth-token	03dcbb7057466bd17afab09ac607885d36f19490e91ef091852086e36e51c46c	["*"]	2026-09-30 00:29:17	\N	2026-09-30 00:29:10	2026-09-30 00:29:17
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
1	Super Administrator	admin@kabupaten.go.id	081100000001	\N	$2y$12$N9K/Zg.YcIkQdKplG./IB.eeRo6K5dyBMyK9NCi0HAkMBQiEdRMfe	ADMIN	1	f	bw0bAYztpZUSMAFpYiGw4nLoA45nFii35PP0CiNAiQ0d73QwcPk0cX7wGuvb	2026-09-02 10:39:12	2026-11-19 16:12:17	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-19 16:12:17	t	\N	\N	\N
7	Operator Verifikasi	operator@kabupaten.go.id	\N	\N	$2y$12$3LoNzvLZcUuEzx3KZPkrA.WQ.H8vivQ5mPSebWGxJ0xN5R15a.INi	OPERATOR	1	f	saD1rvf73tOpgZUqjA8mdHwyN5FR7Wb8RMsObpBLTDW24LxJiiBoc2s8NLA8	2026-09-02 16:10:17	2026-11-18 06:02:29	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-18 06:02:29	t	\N	\N	\N
2	Kepala Daerah Kabupaten	bupati@kabupaten.go.id	081100000002	\N	$2y$12$iTuACittsfeL6rGSfcRUL.wvQwuyxFrckm6xhTE58LF7ugbmDE0zu	KEPALA_DAERAH	1	f	mYN9yd6grpx0StxL14ZAoqCt0Ezowymwu1LLe27ZDX2reVaQTUjjIzYzCKjr	2026-09-02 10:39:12	2026-11-18 06:02:03	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-18 06:02:03	t	\N	\N	\N
24	Abdullah	letsgetreal61@gmail.com	081100001323	\N	$2y$12$gP4YdIM6jsMEgxiTM8KeQ.FmLZIcW2.ypGjGtafcGfl2IUkjCbTYC	PETUGAS	\N	f	2HzLFKq0yVO4UVR85mVga6K5EgoUQATI5IE8lHNpB3DqMTCtj88WJoX1UkZC	2026-09-30 15:42:57	2026-11-19 16:08:42	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	2026-11-19 16:08:42	t	\N	\N	\N
5	Budi	budi@gmail.com	081234567890	\N	$2y$12$Y47ezet7Ua1kq9zkvya.VeFhir6XvyCvVQPl3pgiSzACKIujvX9pq	WARGA	1	f	\N	2026-09-02 10:39:13	2026-11-16 21:07:12	\N	\N	\N	\N	f	menyalahgunakan sistem	2026-11-16 21:07:12	1
6	Dul Arafat Muin	dularafat@gmail.com	081344697178	\N	$2y$12$iJSgNPMv9Eij2PYNynmD8eb7p.03lf0.xk9YxHzvXfIhJc5nNWsJ.	WARGA	1	f	\N	2026-09-02 11:05:41	2026-11-19 18:01:34	\N	100.82.242.85	Dart/3.13 (dart:io)	2026-11-19 18:01:34	t	\N	\N	\N
4	Tri Susilo Muin	tri@gmail.com	081100000004	\N	$2y$12$/OTYv6kJm5BoJDJT7LZvpulrzhCczDiOVsZ5GNqMwwqP8HUL75kMW	PETUGAS	1	f	\N	2026-09-02 10:39:13	2026-11-19 18:12:42	\N	100.82.242.85	Dart/3.13 (dart:io)	2026-11-19 18:02:10	t	\N	\N	\N
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

SELECT pg_catalog.setval('public.chat_messages_id_seq', 351, true);


--
-- Name: complaint_photos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaint_photos_id_seq', 15, true);


--
-- Name: complaint_ratings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaint_ratings_id_seq', 1, false);


--
-- Name: complaint_status_histories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaint_status_histories_id_seq', 22, true);


--
-- Name: complaints_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.complaints_id_seq', 12, true);


--
-- Name: departments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departments_id_seq', 3, true);


--
-- Name: dispositions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.dispositions_id_seq', 4, true);


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

SELECT pg_catalog.setval('public.migrations_id_seq', 25, true);


--
-- Name: notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.notifications_id_seq', 17, true);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 27, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 85, true);


--
-- Name: regions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.regions_id_seq', 18, true);


--
-- Name: supports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.supports_id_seq', 1, true);


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
-- Name: complaints_geom_gist; Type: INDEX; Schema: public; Owner: postgres
--

-- CREATE INDEX complaints_geom_gist ON public.complaints USING gist (geom);


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
-- Name: regions_geom_gist; Type: INDEX; Schema: public; Owner: postgres
--

-- CREATE INDEX regions_geom_gist ON public.regions USING gist (geom);


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
-- PostgreSQL database dump complete
--


