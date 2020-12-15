<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Import site constants, db, and function PHP files -->
  <?php
  // Start session and output buffer for redirecting
  session_start();
  ob_start();

  // Required Files
  require("./includes/constants.php");
  require("./includes/db.php");
  require("./includes/functions.php");

  $message = flash_message();

  // Set time zone for logging user activity in text files and database
  date_default_timezone_set("America/New_York");
  ?>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">

  <title>&lt;S/A&gt; | <?php echo $title; ?></title>

  <!-- 
        Author: Scott Alton
        Filename: <?php echo $file . "\n"; ?>
        Date: <?php echo $date . "\n"; ?>
        Description: <?php echo $description . "\n"; ?>

    -->

  <!-- Font Awesome CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet" />

  <!-- Bootstrap core CSS -->
  <link href="./css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="./css/styles.css" rel="stylesheet">

</head>

<body>
  <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
    <a class="navbar-brand text-center col-3 col-lg-2 mr-0 text-success bg-dark" href="./index.php"><i class="fa fa-laptop"></i>&lt; S / A &gt; Corp.</a>
    <ul class="navbar-nav px-3">
      <?php

      // If a session exists after a user has logged in, provide the option to sign out and destroy the session
      if ($_SESSION) {
        echo '
          <li class="nav-item text-nowrap">
            <a class="nav-link" href="./logout.php">Sign Out</a>
          </li>
        ';
        // If no session is active, provide the option to navigate to the sign-in page
      } else {
        echo '
          <li class="nav-item text-nowrap">
            <a class="nav-link" href="./sign-in.php">Sign In</a>
          </li>
        ';
      }
      ?>

    </ul>
  </nav>
  <div class="container-fluid">
    <div class="row">

      <nav class="col-md-3 col-lg-2 d-none d-md-block bg-light sidebar">
        <div class="sidebar-sticky">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link btn btn-success mx-3 mb-2 text-center round" href="./index.php">Home</a>
            </li>

            <?php
            // If the user is signed, give them the option to navigate to the dashboard page
            if (!$_SESSION) {
              echo '
              <li class="nav-item my-2">
                <a class="nav-link btn btn-success mx-3 text-center round" href="sign-in.php">Sign In</a>
              </li>
            ';
            }
            // If the user is signed, give them the option to navigate to the dashboard page
            if ($_SESSION) {
              echo '
                <li class="nav-item my-2">
                  <a class="nav-link btn btn-success mx-3 text-center round" href="dashboard.php">Dashboard</a>
                </li>
              ';
            }

            // If the user is signed in as an administrator, give them the option to navigate to the salespeople page
            if ($_SESSION && $_SESSION['type'] == "s") {
              echo '
                <li class="nav-item my-2">
                  <a class="nav-link btn btn-success mx-3 text-center round" href="./salespeople.php">Salespeople</a>
                </li>
              ';
            }

            // If the user is signed in as a salesperson, give them the option to navigate to the calls page
            if ($_SESSION && $_SESSION['type'] == "a") {
              echo '
                <li class="nav-item my-2">
                  <a class="nav-link btn btn-success mx-3 text-center round" href="./calls.php">Calls</a>
                </li>
              ';
            }

            // If the user is signed in as a salesperson or an admin, give them the option to navigate to the clients page
            if ($_SESSION && (($_SESSION['type'] == "s") || ($_SESSION['type'] == "a"))) {
              echo '
                <li class="nav-item my-2">
                  <a class="nav-link btn btn-success mx-3 text-center round" href="./clients.php">Clients</a>
                </li>
              ';
            }

            // If the user is signed in, allow them access to the change password page
            if ($_SESSION) {
              echo '
                <li class="nav-item my-2">
                  <a class="nav-link btn btn-success mx-3 text-center round" href="./change-password.php">Change Password</a>
                </li>
              ';
            }

            // Give user the option to navigate to the reset page
            echo '
              <li class="nav-item my-2">
                <a class="nav-link btn btn-success mx-3 text-center round" href="reset.php">Reset</a>
              </li>
            ';
            
            ?>
          </ul>
        </div>
      </nav>

      <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 text-center">
        <div class="d-block flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">