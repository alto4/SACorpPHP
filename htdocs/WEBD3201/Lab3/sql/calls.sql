/*
  Name:         Scott Alton
  File:         calls.sql
  Description:  Creates the calls table for tracking customer interactions that are logged by the agencies salespeople.
  Date:         November 18, 2020
  Course:       WEBD3201 - Web Development - Intermediate
*/

DROP SEQUENCE IF EXISTS call_id_seq
CASCADE;
CREATE SEQUENCE call_id_seq
START 1;

DROP TABLE IF EXISTS calls;

/* Calls table - stores customer calls to track interactions and keep track of client inquiries that are assigned to each salesperson. */
CREATE TABLE calls
(
  id INT PRIMARY KEY DEFAULT nextval('call_id_seq'),
  client_id INT,
  date TIMESTAMP,
  reason VARCHAR(500)
);

GRANT ALL ON calls TO faculty;

/* Sample of call creation that will use sequence for unique ID*/
INSERT INTO calls
  (client_id, date, reason)
VALUES
  (
    1, '2020-10-11', 'To inquire about a new order estimate.'
);

INSERT INTO calls
  (client_id, date, reason)
VALUES
  (
    1, '2020-10-11', 'To inquire about an open orders status.'
);

INSERT INTO calls
  (client_id, date, reason)
VALUES
  (
    2, '2020-11-01', 'To speak with a manager.'
);

SELECT *
FROM calls;




