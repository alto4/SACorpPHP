<?php
/*
    Name:         Scott Alton
    Date:         December 15, 2020
    File:         db.php
    Description:  This file contains the functions used to connect to and interact with the site's PostgreSQL database, 
                  and makes use of constants imported from constants.php and several prepared statements. 
*/

/**
* db_connect function - connects to the PostGreSQL database based on set constant values
* @return array of database resources up successful connection, false on failure
*/
function db_connect()
{
  return pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DATABASE . " user=" . DB_ADMIN . " password=" . DB_PASSWORD);
}

/**
* user_select function - queries the database for email provided
* @param string $email  the email provided for database query to select user info 
* @return array containing user details if user is found
*/
function user_select($email)
{
  // Assume user does not exist
  $user = false;

  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $user_select_stmt = pg_prepare($conn, "user_select_stmt", "SELECT * FROM users WHERE email_address = $1");
  $result = pg_execute($conn, "user_select_stmt", array($email));

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if (pg_num_rows($result) >= 1) {
    $user = pg_fetch_assoc($result, 0);
    return $user;
  }

  // Log invalid attempt 
  update_logs("unknown", "attemped sign-in without a valid email");

  return false;
}

/**
* user_authenticate function - verifies that the user's password entry matches what is stored in the database
* @param string $email  the email provided for database query to select user info 
* @param string $password  the password provided by the user that will be compared to the encrypted password store in the database
* @return boolean - true if the password provided matches database records for email, or false if the password provided is incorrect
*/
function user_authenticate($email, $password)
{
  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $user_authenticate_stmt = pg_prepare($conn, "user_authenticate_stmt", "SELECT * FROM users WHERE email_address = $1");
  $result = pg_execute($conn, "user_authenticate_stmt", array($email));
  $records = pg_num_rows($result);

  // Match entered id against ids that exist in the database
  if ($records > 0) {
    if ($password == pg_fetch_result($result, 0, "password") || password_verify($password, pg_fetch_result($result, 0, "password"))) {
      // Start a new session upon authentication
      session_start();

      // Log valid login event 
      update_logs($email, "successful sign-in");

      // If email and password are authenticated, output a welcome message to the user with a brief summary of their account activity
      $output = "Welcome back! Your account is associated with the email address " . pg_fetch_result($result, 0, "email_address") . " and you were last logged in on " . pg_fetch_result($result, 0, "last_access") . ".";
  
      set_message($output, "success");
      header('Location: dashboard.php');

      $_SESSION['email'] = $email;
      $_SESSION['password'] = $password;

      // Update the session credential/user type to match what's stored in the database
      $_SESSION['type'] = pg_fetch_result($result, 0, "type");

      // If a salesperson is logged in, grab their id from the salesperson table for use in client interactions
      if ($_SESSION['type'] == "a") {
        $_SESSION['id'] = salesperson_select_id($email);
      }

      // Upon successful login, redirect user back to the dashboard page                   
      update_last_login($email);
      return true;
    }
    // If password does not match the corresponding id, output an error message
    else {
      update_logs($email, "unsuccessful login due to bad password");
      return false;
    }
  }
  return false;
}

/**
* salesperson_select_id function - accepts a salesperson's email and retrieves their corresponding salesperson id
* @param string $email  the email provided for database query to select salesperson id 
* @return integer - id that corresponds to the salesperson's email
*/
function salesperson_select_id($email)
{
  $conn = db_connect();

  // Prepared statement for selecting a salesperson's information from the database
  $salesperson_select_id_stmt = pg_prepare($conn, "salesperson_select_id_stmt", "SELECT * FROM salespeople WHERE email_address = $1");
  $id = pg_fetch_result(pg_execute($conn, "salesperson_select_id_stmt", array($email)), 0, "id");

  return $id;
}

/**
* update_last_login function - accepts a logged in users id and updates the database record of their most recent sign in
* @param integer $id the id for the user logged in 
*/
function update_last_login($id)
{
  $conn = db_connect();

  // Generate a time stamp
  $timestamp =  date("Y-m-d G:i:s");

  // Update last login time
  $user_update_login_time_stmt = pg_prepare($conn, "user_update_login_time_stmt", "UPDATE users SET last_access = $1 WHERE email_address = $2");

  $result = pg_execute($conn, "user_update_login_time_stmt", array($timestamp, $id));
}

// LAB #3 DATABASE FUNCTIONS
/**
* client_select_all - selects the data for all client associated with the passed in salesperson id
* @param integer $id  the id for salesperson to filter only their client records
* @return Array of client info for client's belonging to the specified salesperson 
*/
function client_select_all($salesperson_id)
{
  $conn = db_connect();

  // Prepared statement for selecting all clients from the database if user has admin privileges
  if ($salesperson_id == "all") {
    $client_select_all_stmt = pg_prepare($conn, "client_select_all_stmt", "SELECT * FROM clients");
    $result = pg_execute($conn, "client_select_all_stmt", array());

    // Prepared statement for selecting clients associated with the logged in salespersons account
  } else {
    $client_select_all_stmt = pg_prepare($conn, "client_select_all_stmt", "SELECT * FROM clients WHERE salesperson_id = $1");
    $result = pg_execute($conn, "client_select_all_stmt", array($salesperson_id));
  }

  // Fetch all rows from query results
  $rows = pg_fetch_all($result);

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if ($rows) {
    return $rows;
  }

  return false;
}

/**
* client_select function - queries the database for id provided, returns an array containing user details if user found
* @param string $email  the email for the client to be selected
* @return boolean - true if the client exists in records, false if no client record found with provided email
*/
function client_select($email)
{
  // Assume user does not exist
  $user = false;

  $conn = db_connect();

  // Prepared statement for selecting an individal client from the database
  $client_select_stmt = pg_prepare($conn, "client_select_stmt", "SELECT * FROM clients WHERE email_address = $1");
  $result = pg_execute($conn, "client_select_stmt", array($email));

  // Check for a result after querying database and if one exists, return true
  if (pg_num_rows($result) >= 1) {
    return true;
  }

  // Log invalid attempt if client email already exists in records
  update_logs("User", "attemped new client input with the email $email that already exists in our records.");

  return false;
}

/**
* salespeople_select_all prepared statement - selects all salesperson data from the database
* @return Array of all salepeople data 
*/
function salespeople_select_all()
{
  $conn = db_connect();

  // Prepared statement for selecting all salespeople from the database
  $salespeople_select_stmt = pg_prepare($conn, "salespeople_select_stmt", "SELECT salespeople.*, users.id AS user_id, users.enabled FROM salespeople JOIN users ON salespeople.email_address = users.email_address");
  $result = pg_execute($conn, "salespeople_select_stmt", array());

  $rows = pg_fetch_all($result);

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if ($rows) {
    return $rows;
  }

  return false;
}

/**
* calls_select_all prepared statement - selects all call data from the database associated with a salesperson's id
* @param integer $salesperson_id The salesperon's id to filter out records of calls made by their clients only
* @return Array of all call data 
*/
function calls_select_all($salesperson_id)
{
  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $calls_select_stmt = pg_prepare($conn, "calls_select_stmt", "
    SELECT calls.id, calls.client_id, clients.first_name, clients.last_name, calls.date, calls.reason, clients.salesperson_id 
    FROM calls 
    INNER JOIN clients 
    ON calls.client_id = clients.id
    WHERE clients.salesperson_id = $salesperson_id");
  $result = pg_execute($conn, "calls_select_stmt", array());
  $rows = pg_fetch_all($result);

  // Check for a result after querying database and if one exists, return call data
  if ($rows) {
    return $rows;
  }

  return false;
}

/**
* client_count function
* @param integer $salesperson_id The salesperon's id to filter out records of their clients only
* @return integer - number of clients associated with a given salesperson
*/
function client_count($salesperson_id)
{
  $conn = db_connect();

  // Determine how to count client total based on user being an admin who can see all clients, 
  // or a salesperson who can only see their own
  if ($salesperson_id == "all") {

    // Prepared statement for selecting count of all clients overall
    $clients_select_stmt = pg_prepare($conn, "client_count_stmt", "SELECT * FROM clients");
    $result = pg_execute($conn, "client_count_stmt", array());
  } else {

    // Prepared statement for selecting count of clients for a specific salesperson
    $clients_select_stmt = pg_prepare($conn, "client_count_stmt", "SELECT * FROM clients WHERE salesperson_id = $1");
    $result = pg_execute($conn, "client_count_stmt", array($salesperson_id));
  }

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if (pg_num_rows($result) >= 1) {
    return pg_num_rows($result);
  }

  return false;
}

/**
* salespeople_count prepared statement
* @return integer - number of salespeople
*/
function salespeople_count()
{
  $conn = db_connect();

  // Prepared statement for selecting the total number of salespeople 
  $salespeople_count_stmt = pg_prepare($conn, "salespeople_count_stmt", "SELECT * FROM salespeople");
  $result = pg_execute($conn, "salespeople_count_stmt", array());

  // Check for a result after querying database and if one exists, save it as an array to return user data
  if (pg_num_rows($result) >= 1) {
    return pg_num_rows($result);
  }

  return false;
}

/**
* calls_count prepared statement - returns the number of entries in the calls table made by a logged in 
* @param Salesperson Id $salesperson_id The Id of the salesperson calls are being counted for 
* @return integer - number of entries in the calls table filtered by salesperson Id
*/
function calls_count($salesperson_id)
{
  $conn = db_connect();

  // Prepared statement for selecting all client calls from the database
  $calls_select_stmt = pg_prepare($conn, "calls_count_stmt", "
    SELECT *
    FROM calls 
    INNER JOIN clients 
    ON calls.client_id = clients.id
    WHERE clients.salesperson_id = $salesperson_id");
  $result = pg_execute($conn, "calls_count_stmt", array());

  // Check for a result, and if query yields result, return the number of rows
  if (pg_num_rows($result) >= 1) {
    return pg_num_rows($result);
  }

  return false;
}

/**
* call_create prepared statement - creates a new interaction in the calls table
* @param integer $client The client's id
* @param string $time The time the call was made
* @param string $reason The reason the client made the inquiry
* @return boolean - true if call is successfully created, false if call fails to be created in database 
*/
function call_create($client, $time, $reason)
{
  $conn = db_connect();

  // Prepared statement for creating a new call record
  $calls_select_stmt = pg_prepare($conn, "call_create_stmt", "
    INSERT INTO calls(client_id, date, reason) VALUES (
      '$client',
      '$time',
      '$reason'
    )
  ");

  $result = pg_execute($conn, "call_create_stmt", array());

  // If the new call is successfully entered
  if ($result) {
    return true;
  }

  // If the new record fails to be inserted
  return false;
}

/**
* client_create prepared statement - creates a new client record in the clients table
* @param string $first_name The client's first name
* @param string $last_name The client's last name
* @param integer $salesperson_id The id of the salesperson the client is assigned to 
* @param string $email The client's email address
* @param integer $phone The client's phone number
* @param string $type The client's type
* @param string $logo_url The client's logo url
* @return boolean - true if client is successfully created, false if client fails to be created in database 
*/
function client_create($first_name, $last_name, $salesperson_id, $email, $phone, $type, $logo_url)
{
  $conn = db_connect();

  // Prepared statement for creating a new client record
  $client_create_stmt = pg_prepare($conn, "client_create_stmt", "
    INSERT INTO clients(first_name, last_name, salesperson_id, email_address, phone_number, type, logo) VALUES (
      '$first_name',
      '$last_name',
      '$salesperson_id',
      '$email',
      '$phone',
      '$type',
      '$logo_url'
    )
  ");

  $result = pg_execute($conn, "client_create_stmt", array());

  // If the new client is successfully created
  if ($result) {
    return true;
  }

  // If the new record fails to be inserted
  return false;
}

/**
* salesperson_create prepared statement - creates a new salesperson record in the salesperson table
* @param string $first_name The salesperson's first name
* @param string $last_name The salesperson's last name
* @param string $email The salesperson's email address
* @param string $password The salesperson's password to log in
* @param integer $phone The salesperson's phone number
* @param integer $extension The salesperson's phone extension
* @param string $type The salesperson's account type
* @return boolean - true if the salesperson is successfully created, false if client fails to be created in database 
*/
function salesperson_create($first_name, $last_name, $email, $password, $phone, $extension, $type)
{
  $conn = db_connect();
  $timeStamp =  date("Y-m-d G:i:s");

  // Prepared statement for creating a new salesperson record
  $salesperson_create_stmt = pg_prepare($conn, "salesperson_create_stmt", "
    INSERT INTO salespeople(first_name, last_name, email_address, password, phone_number, phone_ext, type) VALUES (
      '$first_name',
      '$last_name',
      '$email',
      '$password',
      '$phone',
      '$extension',
      '$type'
    )
  ");

  // Store the results of the creation of the new salesperson entry in the both the salespeople and the users tables
  $result_salesperson_entry = pg_execute($conn, "salesperson_create_stmt", array());
  $result_user_entry = user_create($first_name, $last_name, $email, $password, $phone, $extension, $type);

  // Ensure that the record is successful in both tables
  if ($result_salesperson_entry == true && $result_user_entry == true) {
    return true;
  }

  // If record is not successfully inserted into both tables return false
  return false;
}

/**
* user_create prepared statement - creates a new user record in the users table
* @param string $first_name The users's first name
* @param string $last_name The users's last name
* @param string $email The user's email address
* @param string $password The user's password to log in
* @param integer $phone The user's phone number
* @param integer $extension The user's phone extension
* @param string $type The users's account type
* @return boolean - true if the user is successfully created, false if client fails to be created in database 
*/
function user_create($first_name, $last_name, $email, $password, $phone, $extension, $type)
{
  $conn = db_connect();
  $timestamp =  date("Y-m-d G:i:s");

  // Prepared statement for creating a new user record
  $user_create_stmt = pg_prepare($conn, "user_create_stmt", "      
    INSERT INTO users (first_name, last_name, email_address, password,  enrol_date, enabled, type) VALUES (
      '$first_name', 
      '$last_name',
      '$email',
      crypt('$password' , gen_salt('bf')),
      '$timestamp',
      true, 
      '$type' 
    )
  ");

  $result = pg_execute($conn, "user_create_stmt", array());

  // If the user record is successfully created 
  if ($result) {
    return true;
  }

  // If the user record is unsuccessful
  return false;
}

/**
* user_update_password prepared statement - updates an existing users password in the users table
* @param string $email The user's email address
* @param string $new_password The user's new password to log in
* @return boolean - true if the password is successfully updated, false if not 
*/
function user_update_password($email, $new_password)
{
  $conn = db_connect();

  // Encrypt new password before updating in database
  $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

  // Prepared statement for creating updated password in database after encyption
  $user_update_password_stmt = pg_prepare($conn, "user_update_password_stmt", "      
    UPDATE users
    SET password = '$hashed_password' 
    WHERE email_address = '$email';
  ");

  $result = pg_execute($conn, "user_update_password_stmt", array());

  // If the password is successfully updated in the users table
  if ($result) {
    return true;
  }

  // If the password update is unsuccessful
  return false;
}
 
/**
* select_dropdown_options prepared statement - selects dropdown menu items from the specified table
* @param string $name The name of the table that select menu options are generated from
* @return array of table data to be converted to dropdown options
*/
function select_dropdown_options($name)
{
  $conn = db_connect();

  // Generate the salespeople from the database as select options if that is the name passed in to the element
  if ($name == "salesperson") {

    // Prepared statement for selecting salesperson dropdown options
    $salesperson_dropdown_select_stmt = pg_prepare(
      $conn,
      "salesperson_dropdown_select_stmt",
      "SELECT id, first_name, last_name FROM salespeople;"
    );

    $result = pg_execute($conn, "salesperson_dropdown_select_stmt", array());

    // Generate the clients from the database as select options if salesperson is logged in 
    // by filtering only their clients
  } else if ($name == "client" && $_SESSION['type'] == "a") {

    // Prepared statement for selecting client dropdown options
    $salesperson_id = $_SESSION['id'];
    $clients_dropdown_select_stmt = pg_prepare(
      $conn,
      "clients_dropdown_select_stmt",
      "SELECT id, first_name, last_name FROM clients WHERE salesperson_id = $salesperson_id;"
    );

    $result = pg_execute($conn, "clients_dropdown_select_stmt", array());
  }

  // Return the data required to populate dropdown menu options
  return $result;
}

// LAB #4 Functions
/**
* disable_salesperson function - disables an active salesperson account without destroying records of their membership
* @param integer $user_id The Id of the salesperson whose account is to be disabled
*/
function disable_salesperson($user_id) {

  $conn = db_connect();

  // Prepared statement for selecting salesperson dropdown options
  $disable_salesperson_stmt = pg_prepare(
    $conn,
    "disable_salesperson_stmt",
    "UPDATE users SET enabled = false, type = 'd' WHERE id = $user_id;"
  );

  $result = pg_execute($conn, "disable_salesperson_stmt", array());
}

/**
* enable_salesperson function - enables a disabled salesperson account 
* @param integer $user_id The Id of the salesperson whose account is to be disabled
*/
function enable_salesperson($user_id) {
  
  $conn = db_connect();

  // Prepared statement for selecting salesperson dropdown options
  $enable_salesperson_stmt = pg_prepare(
    $conn,
    "enable_salesperson_stmt",
    "UPDATE users SET enabled = true, type = 'a' WHERE id = $user_id;"
  );

  $result = pg_execute($conn, "enable_salesperson_stmt", array());
}

/**
* user_check_status function - checks if a salesperson account is active or inactive
* @param string $email The email of the salesperson whose account status is being determined
* @return boolean - true if account is active, or false is account is inactive
*/
function user_check_status($email){
  $conn = db_connect();

  // Prepared statement for selecting a user from the database
  $user_check_status_stmt = pg_prepare($conn, "user_check_status_stmt", "SELECT * FROM users WHERE email_address = $1");
  $result = pg_execute($conn, "user_check_status_stmt", array($email));

  if (pg_fetch_result($result, 0, "type") == "d") {
    // Log valid login event 
    update_logs($email, "attempted sign-in with disabled account");
 
    return false;
  }

  return true;
}