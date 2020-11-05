<?php 

  require("./includes/constants.php");
  require("./includes/db.php");

  // Dump function - shows formatted array data with whitespace/in human-readable format
  function dump($arg) {
    echo "<pre>";
    print_r($arg);
    echo "</pre>";
  }

  $conn = db_connect();

  $user_select = pg_prepare($conn, "user_select" ,"SELECT * FROM users WHERE EmailAddress = $1");
  $user_update_login_time_stmt = pg_prepare($conn, "user_update_login_time_stmt", "UPDATE users SET LastAccess = $1 WHERE EmailAddress = $2");

  $result = pg_execute($conn, "user_select", array("scottalton@gmail.com"));

  // Loop through array of results if present and dump all user account information
  if(pg_num_rows($result) == 1) {
      $user = pg_fetch_assoc($result, 0);
      dump($user); 

     // Authenticate user based on password comparison
      $is_valid_user = password_verify("password", $user["password"]) ? "AUTHENTICATED" : "NOT LOGGED IN!";

      echo "User authenticated status? : " . $is_valid_user;
  }

?>