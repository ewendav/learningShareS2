DROP TABLE IF EXISTS review;
DROP TABLE IF EXISTS report;
DROP TABLE IF EXISTS attend;
DROP TABLE IF EXISTS lesson;
DROP TABLE IF EXISTS exchange;
DROP TABLE IF EXISTS location;
DROP TABLE IF EXISTS user_role;
DROP TABLE IF EXISTS role;
DROP TABLE IF EXISTS app_user;
DROP TABLE IF EXISTS session;
DROP TABLE IF EXISTS rate;
DROP TABLE IF EXISTS skill;
DROP TABLE IF EXISTS category;
DROP TABLE IF EXISTS blacklist;

CREATE TABLE CATEGORY(
	category_id SERIAL PRIMARY KEY,
	category_name VARCHAR(50) NOT NULL
);

CREATE TABLE SKILL(
	skill_id SERIAL PRIMARY KEY,
	skill_name VARCHAR(50) NOT NULL,
	search_counter INTEGER DEFAULT 0,
	category_id INTEGER REFERENCES CATEGORY (category_id)
);


CREATE TABLE RATE(
	rate_id SERIAL PRIMARY KEY,
	rate_name VARCHAR(50) NOT NULL,
	rate_amount INTEGER NOT NULL CHECK(rate_amount >= 0)
);

CREATE TABLE SESSION (
	session_id SERIAL PRIMARY KEY,
	start_time TIME NOT NULL CHECK(start_time < end_time), 
	end_time TIME NOT NULL CHECK(end_time > start_time),
  date_session DATE NOT NULL,
  description VARCHAR(255),
	rate_id INTEGER REFERENCES RATE(rate_id),
	skill_taught_id INTEGER REFERENCES SKILL(skill_id)
);

CREATE TABLE APP_USER(
	user_id SERIAL PRIMARY KEY,
	mail VARCHAR(50) NOT NULL,
	user_first_name VARCHAR(50) NOT NULL,
	user_last_name VARCHAR(50) NOT NULL,
	biography VARCHAR(250),
	avatar_path VARCHAR(100),
	phone VARCHAR(50) NOT NULL,
	password BYTEA NOT NULL,
	balance INTEGER DEFAULT 100 CHECK(balance >= 0)
);

CREATE TABLE ROLE(
	role_id SERIAL PRIMARY KEY,
	role_name VARCHAR(50) NOT NULL
);

CREATE TABLE USER_ROLE(
	role_id INTEGER REFERENCES ROLE (role_id),
	user_id INTEGER REFERENCES APP_USER (user_id)
);

CREATE TABLE LOCATION (
	location_id SERIAL PRIMARY KEY,
	address VARCHAR(255) NOT NULL,
	zip_code VARCHAR(50) NOT NULL,
	city VARCHAR(100) NOT NULL
);

CREATE TABLE LESSON (
	lesson_session_id INTEGER PRIMARY KEY REFERENCES SESSION(session_id),
	location_id INTEGER REFERENCES LOCATION(location_id),
	lesson_host_id INTEGER REFERENCES APP_USER(user_id),
	max_attendees INTEGER NOT NULL CHECK(max_attendees > 0)
);

CREATE TABLE EXCHANGE (
	exchange_session_id INTEGER PRIMARY KEY REFERENCES SESSION(session_id),
  skill_requested_id INTEGER REFERENCES SKILL(skill_id),
	exchange_requester_id INTEGER REFERENCES APP_USER(user_id),
	exchange_accepter_id INTEGER REFERENCES APP_USER(user_id)
);

CREATE TABLE ATTEND (
	attend_id INTEGER PRIMARY KEY,
	attend_lesson_id INTEGER REFERENCES LESSON(lesson_session_id),
	attend_user_id INTEGER REFERENCES APP_USER(user_id)
);

CREATE TABLE REPORT (
	report_id SERIAL PRIMARY KEY,
	report_content VARCHAR(500) NOT NULL,
	report_giver_id INTEGER REFERENCES APP_USER(user_id),
	report_receiver_id INTEGER REFERENCES APP_USER(user_id)
);

CREATE TABLE REVIEW (
	review_id SERIAL PRIMARY KEY,
	review_content VARCHAR(500),
	review_giver_id INTEGER REFERENCES APP_USER(user_id),
	review_receiver_id INTEGER REFERENCES APP_USER(user_id),
	review_session_id INTEGER REFERENCES SESSION(session_id),
	rating INTEGER CHECK (rating > 0 AND rating < 6) NOT NULL
);

CREATE TABLE BLACKLIST(
  blacklist_id SERIAL PRIMARY KEY,
	badword_array JSON 
);

