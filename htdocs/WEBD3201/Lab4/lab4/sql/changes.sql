--ADD LOGO COL TO CLIENTS TABLE
ALTER TABLE clients 
ADD COLUMN logo VARCHAR
(255);

--JOIN FOR SELECTING ALL CALLS FOR LOGGED IN SALESPERSON
SELECT calls.id, calls.client_id, calls.date, calls.reason, clients.salesperson_id
FROM calls
  INNER JOIN clients
  ON calls.client_id = clients.id
WHERE clients.salesperson_id = 1;

