-- Création de la table

CREATE DATABASE IF NOT EXISTS php;
USE php;

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

CREATE TABLE category (
                         category_id INT AUTO_INCREMENT PRIMARY KEY,
                         category_name VARCHAR(50) NOT NULL
);

CREATE TABLE skill (
                       skill_id INT AUTO_INCREMENT PRIMARY KEY,
                       skill_name VARCHAR(50) NOT NULL,
                       search_counter INT DEFAULT 0,
                       category_id INT,
                       CONSTRAINT fk_skill_category FOREIGN KEY (category_id) REFERENCES category(category_id)
);



CREATE TABLE rate (
                     rate_id INT AUTO_INCREMENT PRIMARY KEY,
                     rate_name VARCHAR(50) NOT NULL,
                     rate_amount INTEGER NOT NULL
);

CREATE TABLE session (
                         session_id INT AUTO_INCREMENT PRIMARY KEY,
                         start_time TIME NOT NULL ,
                         end_time TIME NOT NULL ,
                         date_session DATE NOT NULL,
                         description VARCHAR(255),
                         rate_id INTEGER REFERENCES rate(rate_id),
                         skill_taught_id INTEGER REFERENCES skill(skill_id)
);

CREATE TABLE app_user (
                          user_id INT AUTO_INCREMENT PRIMARY KEY,
                          mail VARCHAR(50) NOT NULL,
                          user_first_name VARCHAR(50) NOT NULL,
                          user_last_name VARCHAR(50) NOT NULL,
                          biography VARCHAR(250),
                          avatar_path VARCHAR(100),
                          phone VARCHAR(50) NOT NULL,
                          password VARCHAR(255) NOT NULL,
                          balance INT DEFAULT 100
);

CREATE TABLE role (
                     role_id INT AUTO_INCREMENT PRIMARY KEY,
                     role_name VARCHAR(50) NOT NULL
);

CREATE TABLE user_role (
                           role_id INT,
                           user_id INT,
                           PRIMARY KEY (role_id, user_id),
                           CONSTRAINT fk_userrole_role FOREIGN KEY (role_id) REFERENCES role(role_id),
                           CONSTRAINT fk_userrole_user FOREIGN KEY (user_id) REFERENCES app_user(user_id)
);


CREATE TABLE location (
                          location_id INT AUTO_INCREMENT PRIMARY KEY,
                          address VARCHAR(255) NOT NULL,
                          zip_code VARCHAR(50) NOT NULL,
                          city VARCHAR(100) NOT NULL
);

CREATE TABLE lesson (
                        lesson_session_id INTEGER PRIMARY KEY REFERENCES session(session_id),
                        location_id INTEGER REFERENCES location(location_id),
                        lesson_host_id INTEGER REFERENCES app_user(user_id),
                        max_attendees INTEGER NOT NULL
);

CREATE TABLE exchange (
                          exchange_session_id INTEGER PRIMARY KEY REFERENCES session(session_id),
                          skill_requested_id INTEGER REFERENCES skill(skill_id),
                          exchange_requester_id INTEGER REFERENCES app_user(user_id),
                          exchange_accepter_id INTEGER REFERENCES app_user(user_id)
);

CREATE TABLE attend (
                        attend_id INTEGER PRIMARY KEY,
                        attend_lesson_id INTEGER REFERENCES lesson(lesson_session_id),
                        attend_user_id INTEGER REFERENCES app_user(user_id)
);

CREATE TABLE report (
                        report_id INT AUTO_INCREMENT PRIMARY KEY,
                        report_content VARCHAR(500) NOT NULL,
                        report_giver_id INTEGER REFERENCES app_user(user_id),
                        report_receiver_id INTEGER REFERENCES app_user(user_id)
);

CREATE TABLE review (
                        review_id INT AUTO_INCREMENT PRIMARY KEY,
                        review_content VARCHAR(500),
                        review_giver_id INTEGER REFERENCES app_user(user_id),
                        review_receiver_id INTEGER REFERENCES app_user(user_id),
                        review_session_id INTEGER REFERENCES session(session_id),
                        rating INTEGER  NOT NULL
);

CREATE TABLE blacklist (
                          blacklist_id INT AUTO_INCREMENT PRIMARY KEY,
                          badword_array JSON
);