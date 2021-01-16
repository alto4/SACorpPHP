/*
  Name:         Scott Alton
  File:         clients.sql
  Description:  Creates the clients table used to keep track of all clients of the organization and their contact information
  Date:         November 18, 2020
  Course:       WEBD3201 - Web Development - Intermediate
*/

DROP SEQUENCE IF EXISTS client_id_seq
CASCADE;
CREATE SEQUENCE client_id_seq
START 1;

DROP TABLE IF EXISTS clients;

/* Clients table - stores client contact information */
CREATE TABLE clients
(
  id INT PRIMARY KEY DEFAULT nextval('client_id_seq'),
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  salesperson_id INT REFERENCES salespeople (id),
  email_address VARCHAR(255) UNIQUE,
  phone_number VARCHAR(14),
  phone_ext VARCHAR(4),
  type VARCHAR(2)
);

GRANT ALL ON clients TO faculty;

/* Sample of client creation that will use sequence for unique ID*/
INSERT INTO clients
  (first_name, last_name, salesperson_id, email_address, phone_number, phone_ext, Type
  )
VALUES
  (
    'Scott', 'Alcock', 1, 'sal@gmail.com', 9052543948, 1, 'c'
);

INSERT INTO clients
  (first_name, last_name, salesperson_id, email_address, phone_number, phone_ext, Type
  )
VALUES
  (
    'Shia', 'Mal', 1, 'kiwi@gmail.com', 9052543948, 2, 'c' 
);

INSERT INTO clients
  (first_name, last_name, salesperson_id, email_address, phone_number, phone_ext, Type
  )
VALUES
  (
    'Shannon', 'Fresh', 2, 'fresh@gmail.com', 9052543948, 3, 'c' 
);

--ADD LOGO COL TO CLIENTS TABLE
ALTER TABLE clients 
ADD COLUMN logo VARCHAR
(255);

SELECT *
FROM clients;




