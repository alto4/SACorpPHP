<?php
/*
    Name: Scott Alton
    Date: October 2, 2020
    File: db.php
    Description: This file contains the functions used to connect to the site's postgres database, and makes use of constants imported from constants.php to do
  */

// db_connect function - connects to the PostGreSQL database based on set constant values
function db_connect()
{
  return pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DATABASE . " user=" . DB_ADMIN . " password=" . DB_PASSWORD);
}

// user_select function - queries the database for id provided, returns an array containing user details if user found, or otherwise returns false
function user_select($id)
{
  // Assume user does not exist
  $user = false;

  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $user_select_stmt = pg_prepare($conn, "user_select_stmt", "SELECT * FROM users WHERE EmailAddress = $1");
  $result = pg_execute($conn, "user_select_stmt", array($id));

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if (pg_num_rows($result) == 1) {
    $user = pg_fetch_assoc($result, 0);
  }

  return $user;
}

// user_authenticate function - verifies that the user's password entry matches what is stored in the database before granting access
function user_authenticate($id, $password)
{
  // Ensure user credentials check out and store data in an array
  $userInfo = user_select($id);
  $password = $userInfo['password'];
  // Verify the users password using password decryption function
  $password = password_hash($password, PASSWORD_BCRYPT);

  if (password_verify($password, $userInfo['password'])) {

    $_SESSION['user'] = $id;

    update_last_login($id);
    return true;
  }

  return false;
}

// update_last_login function - accepts a logged in users id/email and updates the database record of their most recent sign in
function update_last_login($id)
{
  $conn = db_connect();

  // Generate a time stamp
  $timeStamp =  date("Y-m-d G:i:s");

  // Update last login time
  $user_update_login_time_stmt = pg_prepare($conn, "user_update_login_time_stmt", "UPDATE users SET LastAccess = $1 WHERE EmailAddress = $2");

  $result = pg_execute($conn, "user_update_login_time_stmt", array($timeStamp, $id));
}

// LAB #3 DATABASE FUNCTION
function client_select_all($salespersonId)
{
  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  if ($salespersonId == "all") {
    $clients_select_stmt = pg_prepare($conn, "client_select_stmt", "SELECT * FROM clients");
    $result = pg_execute($conn, "client_select_stmt", array());
  } else {
    $clients_select_stmt = pg_prepare($conn, "client_select_stmt", "SELECT * FROM clients WHERE salespersonId = " . $salespersonId);
    $result = pg_execute($conn, "client_select_stmt", array());
  }
  $rows = pg_fetch_all($result);
  // Check for a result after querying database and if one exists, save it as an array to return user data
  if ((count($rows)) >= 1) {
    return $rows;
  }

  return false;
}

// salespeople_select_all prepared statement 
function salespeople_select_all()
{
  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $salespeople_select_stmt = pg_prepare($conn, "salespeople_select_stmt", "SELECT * FROM salespeople");
  $result = pg_execute($conn, "salespeople_select_stmt", array());

  $rows = pg_fetch_all($result);
  // Check for a result after querying database and if one exists, save it as an array to return user data
  if ((count($rows)) >= 1) {
    return $rows;
  }

  return false;
}

// calls_select_all prepared statement 
function calls_select_all($salespersonId)
{
  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $calls_select_stmt = pg_prepare($conn, "calls_select_stmt", "
    SELECT calls.Id, calls.ClientId, calls.Date, calls.Reason, clients.SalespersonID 
    FROM calls 
    INNER JOIN clients 
    ON calls.ClientId = clients.Id
    WHERE clients.SalespersonId = $salespersonId");
  $result = pg_execute($conn, "calls_select_stmt", array());
  $rows = pg_fetch_all($result);
  // Check for a result after querying database and if one exists, save it as an array to return user data
  if ((count($rows)) >= 1) {
    return $rows;
  }

  return false;
}

// client_count prepared - returns the number of entries in the clients table
function client_count($salespersonId)
{
  $conn = db_connect();

  if ($salespersonId == "all") {
    // Prepared statement for selecting a user from the database filtered by salesperson ID
    $clients_select_stmt = pg_prepare($conn, "client_count_stmt", "SELECT * FROM clients");
    $result = pg_execute($conn, "client_count_stmt", array());
  } else {
    // Prepared statement for selecting a user from the database filtered by salesperson ID
    $clients_select_stmt = pg_prepare($conn, "client_count_stmt", "SELECT * FROM clients WHERE salespersonId = $salespersonId");
    $result = pg_execute($conn, "client_count_stmt", array());
  }
  // Check for a result after querying database and if one exists, save it as an array to return user data
  if (pg_num_rows($result) >= 1) {
    return pg_num_rows($result);
  }

  return false;
}

// salespeople_count prepared statement - returns the number of entries in the salespeople table
function salespeople_count()
{
  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $salespeople_select_stmt = pg_prepare($conn, "salespeople_count_stmt", "SELECT * FROM salespeople");
  $result = pg_execute($conn, "salespeople_count_stmt", array());

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if (pg_num_rows($result) >= 1) {
    return pg_num_rows($result);
  }

  return false;
}

// calls_count prepared statement - returns the number of entries in the calls table
function calls_count($salespersonId)
{
  $conn = db_connect();
  // Prepared statement for selecting a user from the database
  $calls_select_stmt = pg_prepare($conn, "calls_count_stmt", "
    SELECT *
    FROM calls 
    INNER JOIN clients 
    ON calls.ClientId = clients.Id
    WHERE clients.SalespersonId = $salespersonId");
  $result = pg_execute($conn, "calls_count_stmt", array());

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if (pg_num_rows($result) >= 1) {
    return pg_num_rows($result);
  }

  return false;
}

// call_create prepared statement
function call_create($client, $time, $reason)
{
  $conn = db_connect();
  // Prepared statement for creating a new call record
  $calls_select_stmt = pg_prepare($conn, "call_create_stmt", "
    INSERT INTO calls(ClientId, Date, Reason) VALUES (
      '$client',
      '$time',
      '$reason'
    )
  ");

  $result = pg_execute($conn, "call_create_stmt", array());
  if ($result) {
    return true;
  }

  return false;
}

// client_create prepared statement
function client_create($firstName, $lastName, $salespersonId, $email, $phone, $type, $logoUrl)
{
  $conn = db_connect();
  // Prepared statement for creating a new client record
  $client_create_stmt = pg_prepare($conn, "client_create_stmt", "
    INSERT INTO clients(FirstName, LastName, SalespersonId, EmailAddress, PhoneNumber, Type, logo_path) VALUES (
      '$firstName',
      '$lastName',
      '$salespersonId',
      '$email',
      '$phone',
      '$type',
      '$logoUrl'
    )
  ");

  $result = pg_execute($conn, "client_create_stmt", array());
  if ($result) {
    return true;
  }

  return false;
}

// client_create prepared statement
function salesperson_create($firstName, $lastName, $email, $password, $phone, $extension, $type)
{
  $conn = db_connect();
  $timeStamp =  date("Y-m-d G:i:s");
  // Prepared statement for creating a new client record
  $salesperson_create_stmt = pg_prepare($conn, "salesperson_create_stmt", $sql = "
    INSERT INTO salespeople(FirstName, LastName, EmailAddress, Password, PhoneNumber, PhoneExt, Type) VALUES (
      '$firstName',
      '$lastName',
      '$email',
      '$password',
      '$phone',
      '$extension',
      '$type'
    )
  ");

  $resultSalespersonEntry = pg_execute($conn, "salesperson_create_stmt", array());
  $resultUserEntry = user_create($firstName, $lastName, $email, $password, $phone, $extension, $type);

  if ($resultSalespersonEntry == true && $resultUserEntry == true) {
    return true;
  }

  return false;
}

// user_create prepared statement
function user_create($firstName, $lastName, $email, $password, $phone, $extension, $type)
{
  $conn = db_connect();
  $timeStamp =  date("Y-m-d G:i:s");
  // Prepared statement for creating a new client record
  $user_create_stmt = pg_prepare($conn, "user_create_stmt", $sql = "      
    INSERT INTO users (FirstName, LastName, EmailAddress, Password,  EnrolDate, Enabled, Type) VALUES (
      '$firstName', 
      '$lastName',
      '$email',
      '$password',
      '$timeStamp',
      true, 
      '$type' 
    )
  ");

  $result = pg_execute($conn, "user_create_stmt", array());

  if ($result) {
    return true;
  }

  return false;
}

// user_update_password prepared statement
function user_update_password($email, $newPassword)
{
  $conn = db_connect();
  // DEBUG for password encryption
  $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
  // echo "<h1>Hashed password for database:" . $hashedPassword . "</h1>";
  // if (password_verify($newPassword, $hashedPassword)) {
  //   echo "<h1>Houston, we have a match!</h1>";
  // }
  // Prepared statement for creating updated password in database after encyption
  $user_update_password_stmt = pg_prepare($conn, "user_update_password_stmt", $sql = "      
    UPDATE users
    SET password = '$hashedPassword' 
    WHERE emailAddress = '$email';
  ");

  $result = pg_execute($conn, "user_update_password_stmt", array());

  if ($result) {
    return true;
  }

  return false;
}
