<?php

$title = "Logout Page";
$file = "logout.php";
$description = "This page is used to destroy a current session to log a user out of their account. Once the session is 
destroyed, the page will redirect to the sign-in form page.";
$date = "October 22, 2020";

require("./includes/header.php");

// If a session already exists, it will be destroyed and captured in the logs
if ($_SESSION) {
  $email = $_SESSION['email'];

  session_destroy();

  // Log sign out event
  update_logs($email, "sign-out");
  set_message("Successfully logged out", "success");
}

// Redirect the signed out user to the sign-in page if they would like access to restricted pages
header("Location: sign-in.php");
