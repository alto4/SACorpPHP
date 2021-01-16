/*
  Name:         Scott Alton
  File:         users.sql
  Description:  Creates the users table for login authentication
  Date:         November 18, 2020
  Course:       WEBD3201 - Web Development - Intermediate
*/

CREATE EXTENSION
IF NOT EXISTS pgcrypto;

DROP SEQUENCE IF EXISTS user_id_seq
CASCADE;
CREATE SEQUENCE user_id_seq
START 1000;

DROP TABLE IF EXISTS users;

/* Users table - stores user account registration registration with Id as the primary key (auto-generated from users_id_sequence if no key is provided)*/
CREATE TABLE users
(
  id INT PRIMARY KEY DEFAULT nextval('user_id_seq'),
  email_address VARCHAR(255) UNIQUE,
  password VARCHAR(255) NOT NULL,
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  last_access TIMESTAMP,
  enrol_date TIMESTAMP,
  enabled BOOLEAN,
  phone_ext VARCHAR(4),
  type VARCHAR(2)
);

GRANT ALL ON users TO faculty;

/* Sample of user creation that will use sequence for unique ID */
INSERT INTO users
  (email_address, password, first_name, last_name, last_access, enrol_date, enabled, phone_ext, type)
VALUES
  (
    'scottalton@gmail.com', crypt('password' , gen_salt('bf')), 'Scott', 'Alton', '2020-09-15 09:02:31', '2020-09-15 09:02:31', true, '266',
    's' 
  );
INSERT INTO users
  (Id, email_address, password, first_name, last_name, last_access, enrol_date, enabled, phone_ext, type)
VALUES
  (
    1, 'test1@gmail.com', crypt('password', gen_salt('bf')), 'John', 'Jacob', '2020-09-15 09:02:31', '2020-09-15 09:02:31', true, '266',
    's' 
  );
INSERT INTO users
  (id, email_address, password, first_name, last_name, last_access, enrol_date, enabled, phone_ext, type)
VALUES
  (
    2, 'jjjhs@gmail.com', crypt('password', gen_salt('bf')), 'Jingle', 'Heimer-Schmitd', '2020-09-15 09:02:31', '2020-09-15 09:02:31', true, '266',
    's' 
  );


SELECT *
FROM users;




