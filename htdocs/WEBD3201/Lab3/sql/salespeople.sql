/*
  Name:         Scott Alton
  File:         salespeople.sql
  Description:  Creates the salespeople table for all salespeople within the organization, and includes their login credentials and contact info.
  Date:         November 18, 2020
  Course:       WEBD3201 - Web Development - Intermediate
*/

DROP SEQUENCE IF EXISTS salespeople_id_seq
CASCADE;
CREATE SEQUENCE salespeople_id_seq
START 1;

DROP TABLE IF EXISTS salespeople;

/* Users table - stores user account registration registration with Id as the primary key (auto-generated from users_id_sequence if no key is provided)*/
CREATE TABLE salespeople
(
  id INT PRIMARY KEY DEFAULT nextval('salespeople_id_seq'),
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  email_address VARCHAR(255) UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone_number VARCHAR(14),
  phone_ext VARCHAR(4),
  type VARCHAR(2)
);

GRANT ALL ON salespeople TO faculty;

/* Sample of salespeople creation that will use sequence for unique ID*/
INSERT INTO salespeople
  (first_name, last_name, email_address, password, phone_number, phone_ext, type
  )
VALUES
  (
    'Amanda', 'Kingston', 'sal@gmail.com', 'password', 9052543948, 12, 'a' 
);

INSERT INTO salespeople
  (first_name, last_name, email_address, password, phone_number, phone_ext, type
  )
VALUES
  (
    'Rita', 'Tosh', 'kiwi@gmail.com', 'password', 9052543948, 13, 'a' 
);

INSERT INTO salespeople
  (first_name, last_name, email_address, password, phone_number, phone_ext, type
  )
VALUES
  (
    'Henry', 'Eighth', 'fresh@gmail.com', 'password', 9052543948, 14, 'a' 
);


SELECT *
FROM salespeople;


