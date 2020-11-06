<?php

/*
    Name: Scott Alton
    Date: October 22, 2020
    File: functions.php
    Description: This file contains functions that are used accross the site for flash messaging, redirecting, message logging, 
    as well as functions that are used to generate components in the UI.
  */

// LAB #1 FUNCTIONS
// redirect function - sends the user to desired location and sends the contents of output buffer
function redirect($url)
{
  header("Location:" . $url);
  ob_flush();
}

// setMessage function
function setMessage($message, $type)
{
  $_SESSION['message'] = "<div class=\"text-$type\">$message</div>";
}

// getMessage function
function getMessage()
{
  return $_SESSION['message'];
}

// isMessage function
function isMessage()
{
  return isset($_SESSION['message']) ? true : false;
}

// removeMessage function
function removeMessage()
{
  unset($_SESSION['message']);
}

// flashMessage function 
function flashMessage()
{
  $message = "";

  // Check if a session message has been sent
  if (isMessage()) {
    $message = getMessage();
    removeMessage();
  }

  return $message;
}

// dump function - shows formatted array data with whitespace/in human-readable format
function dump($arg)
{
  echo "<pre>";
  print_r($arg);
  echo "</pre>";
}

// updateLogs function - updates user event logs detailing an event type and along with timing details
function updateLogs($user, $event)
{
  // Get current date/time details at time of the event
  $today = date("Ymd");
  $now = date("H:i:s");

  // Open current day's log file, or if non-existent, create a new one
  $handle = fopen("./activity_logs/" . $today . ".txt", 'a');

  // Write event to log file
  fwrite($handle, "$event event at $now $today. User $user $event." . "\n");
}

// LAB #2 FUNCTIONS

// display_form function - generates a dynamically rendered form based on an array of associative arrays that is 
//      passed in and specifies various attributes of each form element. 
function display_form($elements)
{
  // Start of the generated form
  echo '
    <form enctype="multipart/form-data" class="input-form rounded bg-success p-4 mb-5" style="width:400px; align-self: center; margin: auto;"' . ' method="POST" >
  ';

  // Start loop for each element (nested array) to generate individual form elements
  for ($i = 0; $i < count($elements); $i++) {

    $type = $elements[$i]['type'];
    $name = $elements[$i]['name'];
    $value = $elements[$i]['value'];
    $label = $elements[$i]['label'];
    $dropdown = $elements[$i]['isDropdown'];

    // Form output
    // Generate label for form input element
    echo '
      <div class="form-group text-left">
          <label class="h5">' . $label . ':</label>
        ';

    // Check for flag that determines if a form element needs to be processed as a dropdown 
    if ($dropdown == true) {

      // Generate the salespeople from the database as select options if that is the name passed in to the element
      if ($name == "salesperson") {
        $table = "salespeople";
        echo '<select name="salespersonId" class="d-block w-100 p-2 form-control my-3">';
        // Generate the clients from the database as select options if that is the name passed in to the element
      } else if ($name == "client") {
        $table = "clients";
        echo '<select name="client" class="d-block w-100 p-2 form-control my-3">';
      }

      // Query the database for all salespeople or clients to populate dropdown select options
      $conn = db_connect();
      $sql = "SELECT Id, FirstName, LastName FROM $table";
      $result = pg_query($conn, $sql);

      // Populate the option elements with the first and last name of targetted table, and set each elements value to their 
      //  unique id
      while ($menuOption = pg_fetch_assoc($result)) {
        $id = $menuOption['id'];
        echo '<option value=' . $id . '>' . $menuOption['firstname'] . ' ' . $menuOption['lastname'] . '</option>';
      }

      echo '</select>';

      // If the element is not flagged to be a dropdown, generate a standard input element 
    } else if ($type == "file") {
      echo '<input type=' . $type . ' class="form-control" id="uploadLogoId "name=' . $name . ' value=' . $value . '>';
    } else {
      echo '  <input type=' . $type . ' class="form-control" name=' . $name . ' value=' . $value . '>
      </div>
      ';
    }
  }

  // Generate a submit button 
  echo '<hr />
      <button type="submit" class="btn btn-block btn-dark">Create</button>
    </form>
    </div>';
}

// LAB #3 FUNCTIONS

// NEED TO GET PAGINATION BUTTONS WORKING!!!!!
// show_table function - queries a table from the database using a prepared statement and displays are specified cells in a table
function display_table($dataFields, $data, $numOfRows, $page)
{
  // Pagination variable declarations
  if (isset($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    // set proper default value if it was not set
    $page = 1;
  }

  $startingRecord = ($page - 1) * ROWS_PER_PAGE;
  $numOfPages = ceil($numOfRows / ROWS_PER_PAGE);

  echo '<div class="table-responsive w-75 mx-auto py-3">
          <table class="table table-dark table-bordered table-sm">
              <thead>
              <tr>';
  foreach ($dataFields as $key) {
    echo '<th class="py-2">' . $key . '</th>';
  }
  echo '      </tr>
              </thead>
              <tbody>';

  // Populate each new row with corresponding data from table
  $keys = array_keys($dataFields);

  for ($i = $startingRecord; $i < $startingRecord + ROWS_PER_PAGE; $i++) {
    $row = $data[$i];
    echo "<tr>";

    for ($j = 0; $j < count($keys); $j++) {
      $col = $keys[$j];
      echo '
          <td class="py-2">' . $row[$col] . "</td>";
    }

    echo "</tr>";
  }


  echo      '</tbody>
          </table>';

  for ($i = 1; $i <= $numOfPages; $i++) {
    echo '<a class="btn btn-dark mx-1" href="?page=' . $i . '" >' . $i . '</a>';
  }
}
