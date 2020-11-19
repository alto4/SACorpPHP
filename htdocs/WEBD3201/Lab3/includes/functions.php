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

      if ($name == "salesperson") {
        $result = select_dropdown_options("salesperson");
        echo '<select name="client" class="d-block w-100 p-2 form-control my-3">';
      } else if ($name == "client" && $_SESSION['type'] == "a") {
        $result = select_dropdown_options("client");
        echo '<select name="client" class="d-block w-100 p-2 form-control my-3">';
      }

      // Populate the option elements with the first and last name of targetted table, and set each elements value to their 
      //  unique id
      while ($menuOption = pg_fetch_assoc($result)) {
        $id = $menuOption['id'];
        echo '<option value=' . $id . '>' . $menuOption['firstname'] . ' ' . $menuOption['lastname'] . '</option>';
      }

      echo '</select>';

      // If the element is not flagged to be a dropdown, generate a standard input element 
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
// show_table function - queries a table from the database using a prepared statement and displays are specified cells in a table
function display_table($dataFields, $data, $numOfRows, $page)
{
  // Pagination variable declarations
  if (isset($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    // If no page has been set, 1 is the default
    $page = 1;
  }

  // Pagination calculations
  $startingRecord = ($page - 1) * RECORDS_PER_PAGE;
  $numOfPages = ceil($numOfRows / RECORDS_PER_PAGE);
  $rowsOnLastPage = $numOfRows % RECORDS_PER_PAGE;

  // Begin table output
  echo '<div class="table-responsive w-75 mx-auto py-3">
          <table class="table table-dark table-bordered table-sm">
              <thead>
              <tr>';

  // Generate table headings by looping through the keys of the passed in associative array of data
  foreach ($dataFields as $key) {
    echo '<th class="py-2">' . $key . '</th>';
  }
  echo '      </tr>
              </thead>
              <tbody>';

  // Populate each new row with corresponding data from table by looping through the array
  // Store indexed array of keys 
  $keys = array_keys($dataFields);

  // Check if the final page of table data has been selected to avoid overflow beyond the databases final row
  if ($page == $numOfPages) {
    for ($i = $startingRecord; $i < $startingRecord + $rowsOnLastPage; $i++) {
      // Set the current row index
      $row = $data[$i];
      echo "<tr>";

      // Loop through each of the current rows keys to fill row columns with corresponding data
      for ($j = 0; $j < count($keys); $j++) {
        $col = $keys[$j];

        // Check for instance where the current column is a logo, and display a logo image rather than textual data if so
        if ($keys[$j] == "logo_path" && $row[$col] != "") {
          echo '<td class="py-2"><img src="' . $row[$col] . '" alt="Client Logo" class="logo-thumbnail" /></td>';
        } else {
          // Display regular textual data
          echo '
            <td class="py-2">' . $row[$col] . "</td>";
        }
      }
      echo "</tr>";
    }
  } else {
    // Loop through the set number of rows specified to be displayed on each page 
    for ($i = $startingRecord; $i < $startingRecord + RECORDS_PER_PAGE; $i++) {
      $row = $data[$i];
      echo "<tr>";

      // Loop through each of the current rows keys to fill row columns with corresponding data
      for ($j = 0; $j < count($keys); $j++) {
        $col = $keys[$j];

        // Check for instance where the current column is a logo, and display a logo image rather than textual data if so
        if ($keys[$j] == "logo_path" && $row[$col] != "") {
          echo '<td class="py-2"><img src="' . $row[$col] . '" alt="Client Logo" class="logo-thumbnail" /></td>';
        } else {
          echo '
          <td class="py-2">' . $row[$col] . "</td>";
        }
      }
      echo "</tr>";
    }
  }

  // Output the closing tags for table of data
  echo '</tbody>
          </table>
          ';

  // Create pagination navigation buttons
  for ($i = 1; $i <= $numOfPages; $i++) {
    echo '<a class="btn btn-dark mx-1" href="?page=' . $i . '" >' . $i . '</a>';
  }
}
