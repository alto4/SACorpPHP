--ADD LOGO COL TO CLIENTS TABLE
ALTER TABLE clients 
ADD COLUMN logo VARCHAR
(255);

--JOIN FOR SELECTING ALL CALLS FOR LOGGED IN SALESPERSON
SELECT calls.Id, calls.ClientId, calls.Date, calls.Reason, clients.SalespersonID
FROM calls
  INNER JOIN clients
  ON calls.ClientId = clients.Id
WHERE clients.SalespersonId = 1;

