<?php
$title = "Reset";
$file = "reset.php";
$description = "This page contains a reset request form that will send an email to the user with their account information.";
$date = "November 23, 2020";

include "./includes/header.php";

$output = "";

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to collect info to insert into the calls table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $client_id = "";
  $reason = "";
}

// If the user has tried to insert an entry after the page first loads, store the data values in input fields
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST["email"]);
  
  // PASSWORD VALIDATIONS
  // EMAIL VALIDATIONS
    // Verify that user email was entered, and if not, display an error message
    if (!isset($email) || $email == "") {
      $output .= "You must enter an email to recieve an account reset email.</br>";
  }
  // Use filter_var to validate that email contains required characters
  else if (!(filter_var($email, FILTER_VALIDATE_EMAIL))) {
      $output .= $email . " is not a valid email address. Please try again.<br/>";
      $email = "";
  }

  if ($output == "") 
  {   
      // Check if email is registered in the users database
      if(user_select($email)) {      
        // The message
        $emailContent = "You have successfully requested a reset for your account at S/A Corp registered under the email $email. Please follow this link to finish the account reset: <a href='#'>www.sacorp/reset/gtergdgdgfr</a>.
        Thank you, and please contact a site administrator if any further issues arise.
        Management\r\n";

        // In case any of our lines are larger than 70 characters, we should use wordwrap()
        //$emailContent = wordwrap($message, 70, "\r\n");

        // Set email headers
        $headers = 'From: accountinfo@sacorp.com' . "\r\n" .
        'Reply-To: accountinfo@sacorp.com' . "\r\n";
       
        // Log email in archives
        // Open current day's log file, or if non-existent, create a new one
        $handle = fopen('./emails/sent.txt', 'a');

        // Write event to log file
        fwrite($handle, "SENT EMAIL DETAILS\n" . $headers . "\nMESSAGE\n" . $emailContent . "\n"); 
       
        // Send
        $successfulEmail = mail($emailContent, 'Account resent', $message, $headers);
      
        if (!$successfulEmail) {
          $output = error_get_last()['message'];
        }  

       // If the query produces a result, log password update event in activity logs
       update_logs("$email", "successfully sent reset email to account with the email");
        
      set_message("A message was sent.", "success");
       
      header('Location: sign-in.php');
      } else {
      // If the query produces a result, log password update event in activity logs
      update_logs("$email", "SECURITY ALERT! unsuccessful request made to reset account with the email");

      set_message("A message was sent.", "success");

      header('Location: sign-in.php');
      }  
    }
  }
?>

<h1>Reset Account</h1>

<p class="w-75 lead mx-auto">To change your password, please enter a new password below, and then verify it by retyping it in the second field.</h6>
  <h5 class="text-danger"><?php echo $output ?></h5>
  <h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>
  <div>
    <?php
    display_form(
      array(
        array(
          "type" => "email",
          "name" => "email",
          "value" => "",
          "label" => "Email Address",
          "is_dropdown" => false
        )
      )
    );
    ?>

    <?php
    include "./includes/footer.php";
    ?>