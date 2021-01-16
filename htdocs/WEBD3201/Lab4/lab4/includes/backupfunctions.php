<?php
/*
    Name: Scott Alton
    Date: December 15, 2020
    File: functions.php
    Description: This file contains functions that are used accross the site for flash messaging, redirecting, message logging, 
    as well as functions that are used to generate components in the UI.
*/

// LAB #1 FUNCTIONS
/**
* redirect function - sends the user to desired location and sends the contents of output buffer
* @param string $url The address to redirect the user to
*/ 
function redirect($url)
{
  header("Location:" . $url);
  ob_flush();
}

/**
* set_message function
* @param string $message The contents of the flash message
* @param string $type The class to alter message's text colour
*/
function set_message($message, $type)
{
  $_SESSION['message'] = "<div class=\"text-$type\">$message</div>";
}

/**
* get_message
* @return string - the flash message contents
*/
function get_message()
{
  return $_SESSION['message'];
}

/**
* is_message function
* @return boolean - true message has been set, false if not
*/
function is_message()
{
  return isset($_SESSION['message']) ? true : false;
}

/**
* remove_message function - removes flash message contents stored as a session variable
*/
function remove_message()
{
  unset($_SESSION['message']);
}

/**
* user_check_status function - checks if a salesperson account is active or inactive
* @return string The contents of the flash message
*/
function flash_message()
{
  $message = "";

  // Check if a session message has been sent
  if (is_message()) {
    $message = get_message();
    remove_message();
  }

  return $message;
}

/**
* dump function - displays array contents in readable format
* @param array $arg The array to be displayed
*/
function dump($arg)
{
  echo "<pre>";
  print_r($arg);
  echo "</pre>";
}

/**
* update_logs function - updates user event logs detailing an event type and along with timing details
* @param string $user The email of the salesperson whose account status is being determined
* @param string $event The event that is being logged
*/
function update_logs($user, $event)
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
/**
* display_form function - generates a dynamically rendered form based on an array of associative arrays that is 
* passed in and specifies various attributes of each form element.
* @param array $elements A nested array containing arrays populated with a database row's contents for display
*/
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
    $dropdown = $elements[$i]['is_dropdown'];

    // Form output
    // Generate label for form input element
    echo '
      <div class="form-group text-left">
          <label class="h5">' . $label . ':</label>
        ';

    // Check for flag that determines if a form element needs to be processed as a dropdown 
    if ($dropdown == true) {

      if ($name == "salesperson_id") {
        $result = select_dropdown_options("salesperson");
        echo '<select name="salesperson_id" class="d-block w-100 p-2 form-control my-3">';
      } else if ($name == "client" && $_SESSION['type'] == AGENT) {
        $result = select_dropdown_options("client");
        echo '<select name="client" class="d-block w-100 p-2 form-control my-3">';
      }

      // Populate the option elements with the first and last name of targetted table, and set each elements value to their 
      //  unique id
      while ($menu_option = pg_fetch_assoc($result)) {
        $id = $menu_option['id'];
        echo '<option value="' . $id . '">' . $menu_option['first_name'] . ' ' . $menu_option['last_name'] . '</option>';
      }

      echo '</select>
	    </div>';

      // If the element is not flagged to be a dropdown, generate a standard input element 
    } else if ($type == "file") {
	echo '  <input type="' . $type . '" class="form-control" name="' . $name . '" />
         </div>';
    } else {
      echo '  <input type="' . $type . '" class="form-control" name="' . $name . '" value="' . $value . '" />
      </div>
      ';
    }
  }

  // Generate a submit button 
  echo '<hr />
      <button type="submit" name="submitType" value="Create" class="btn btn-block btn-dark">Create</button>
    </form>
    </div>';
}

// LAB #3 FUNCTIONS
/**
* show_table function - queries a table from the database using a prepared statement and displays a data table
* @param array $data_fields An array containing column headings for the table to be generated for display
* @param array $data A nested array containing the data to populate columns in each table row
* @param integer $num_of_rows The number of rows to be displayed in the table
* @param integer $page The page number to be displayed
*/
function display_table($data_fields, $data, $num_of_rows, $page)
{
  // Pagination variable declarations
  if (isset($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    // If not set, page 1 is the default
    $page = 1;
  }

  // Pagination calculations
  $starting_record = ($page - 1) * RECORDS_PER_PAGE;
  $num_of_pages = ceil($num_of_rows / RECORDS_PER_PAGE);
  $rows_on_last_page = $num_of_rows % RECORDS_PER_PAGE;

  // Begin table output
  echo '<div id="data-table" class="table-responsive w-75 mx-auto py-3">
          <table class="table table-dark table-bordered table-sm table-striped">
              <thead>
              <tr>';

  // Generate table headings by looping through the keys of the passed in associative array of data
  foreach ($data_fields as $key) {
    echo '<th class="py-2">' . $key . '</th>';
  }

  echo '      </tr>
              </thead>
              <tbody>';

  // Populate each new row with corresponding data from table by looping through the array
  // Store indexed array of keys 
  $keys = array_keys($data_fields);

  // Check if the final page of table data has been selected to avoid overflow beyond the databases final row
  if ($page == $num_of_pages && $rows_on_last_page % RECORDS_PER_PAGE !== 0) {

    // Loop through each record to display contents as a table row
    for ($i = $starting_record; $i < $starting_record + $rows_on_last_page; $i++) {
      // Set the current row index
      $row = $data[$i];
      echo "<tr>";

      // Loop through each of the current rows keys to fill row columns with corresponding data
      for ($j = 0; $j < count($keys); $j++) {
        $col = $keys[$j];

        // Check for instance where the current column is a logo, and display a logo image rather than textual data if so
        if ($keys[$j] == "logo" && $row[$col] != "") {
          echo '<td class="py-2"><img src="' . $row[$col] . '" alt="Client Logo" class="logo-thumbnail" /></td>';
        
        // Check for instance where current column contains active/inactive form
        } else if ($keys[$j] == "enabled") {
          $user_id = $data[$i]['user_id'];
          $user_enabled = $data[$i]['enabled'];
                    
          echo '
            <td class="py-2">'. 
              '<form method="POST" action="./salespeople.php">
                <div>
                  <input type="radio" name="active[' . $user_id . ']" value="t" ';
                  if($user_enabled == "t") { echo 'checked'; };
                echo '/>
                  <label>Active</label>
                </div> 
                <div>
                  <input type="radio" name="active[' . $user_id . ']" value="f" ';
                  if($user_enabled == "f") { echo 'checked'; };
                  
                echo '/>
                  <label>Inactive</label>
                </div> 
                <input type="submit" name="submitType" value="Update" />
              </form> 
            </td>';
        } else {
          // Display regular textual data
          echo '
            <td class="py-2">' . $row[$col] . "</td>";
        }
      }
      echo "</tr>";
    }
  } else {
    // For all other data pages, loop through the number of rows specified in the associated constant
    for ($i = $starting_record; $i < $starting_record + RECORDS_PER_PAGE; $i++) {
      $row = $data[$i];
      echo "<tr>";

      // Loop through each of the current rows keys to fill row columns with corresponding data
      for ($j = 0; $j < count($keys); $j++) {
        $col = $keys[$j];

        // Check for instance where the current column is a logo, and display a logo image rather than textual data if so
        if ($keys[$j] == "logo" && $row[$col] != "") {
          echo '<td class="py-2"><img src="' . $row[$col] . '" alt="Client Logo" class="logo-thumbnail" /></td>';
        // Check for instance where current column contains active/inactive form
      } else if ($keys[$j] == "enabled") {
        $user_id = $data[$i]['user_id'];
        $user_enabled = $data[$i]['enabled'];
        
        
        echo '
          <td class="py-2">'. 
            '<form method="POST" action="./salespeople.php">
              <div>
                <input type="radio" name="active[' . $user_id . ']" value="t" ';
                if($user_enabled == "t") { echo 'checked'; };
              echo '/>
                <label>Active</label>
              </div> 
              <div>
                <input type="radio" name="active[' . $user_id . ']" value="f" ';
                if($user_enabled == "f") { echo 'checked'; };
                
              echo '/>
                <label>Inactive</label>
              </div> 
              <input type="submit" name="submitType" value="Update" />
            </form> 
          </td>';
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

  // Create pagination nav buttons
  // Show previous button if not on first page of records 
  if ($page > 1) {
    echo '<a class="btn btn-dark mx-1" href="?page=' . ($page - 1) . '#data-table"><i class="fa fa-arrow-left"></i></a>';
  }

  // Create a link button for each page of records
  for ($i = 1; $i <= $num_of_pages; $i++) {
    echo '<a class="btn btn-dark mx-1" href="?page=' . $i . '#data-table" >' . $i . '</a>';
  }

  // Show next button if not on the last page of records
  if ($page < $num_of_pages) {
    echo '<a class="btn btn-dark mx-1" href="?page=' . ($page + 1) . '#data-table"><i class="fa fa-arrow-right"></i></a>';
  }
}

