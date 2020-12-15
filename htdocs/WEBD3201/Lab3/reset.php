<?php
$title = "Reset";
$file = "reset.php";
$description = "This page contains a reset request form that will send an email to the user with their account information.";
$date = "December 15, 2020";

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

  // Once all validations pass, check if email provided has a registered account
  if ($output == "") 
  {   
      // Check if email is registered in the users database
      if(user_select($email)) {      
        $time_stamp = date('D, d/m/y H:i:s');
        
        // Set email headers
        $headers = array(
          "To: $email",
          "From: accountinfo@sacorp.com",
          "Reply-To: accountinfo@sacorp.com",
          "Time: $time_stamp"
        );

        // Email subject line
        $subject = "SA Corp Account Password Reset";
        
        // Email body
        $email_body = "Dear User,\n\nYou have successfully requested a reset for your account at S/A Corp registered under the email $email. This request was made on " . date('D, d/m/y') . " at " . date('H:i:s') . ". Please follow this link to finish the account reset: www.sacorp/reset/g444532gft745g4. \n\nThank you, and please contact a site administrator if any further issues arise.\n\nSincerely,\nSA Corportation\n\n";
        // In case any of our lines are larger than 100 characters, we should use wordwrap()
        $email_content = wordwrap($email_body, 100, "\n");

        // Log email to txt file storing all archived mail
        $handle = fopen('./emails/sent.txt', 'a');

        // Write event to log file
        fwrite($handle, "SENT EMAIL DETAILS\n" . implode("\r\n", $headers) . "\nMESSAGE\n" . $email_content . "\n"); 
       
        // Send email and log success or failure of event
        //$successful_email = mail($email, $subject, $email_content, implode("\r\n", $headers));
      
        // if (!$successful_email) {
        //   $output = error_get_last()['message'];
        // }  

       // If the query produces a result, log password update event in activity logs
       update_logs("$email", "successfully sent reset email to account");
        
      set_message("A message was sent.", "success");
       
      header('Location: sign-in.php');
      } else {

      // Log attempt to reset a password for non-existent account, log event 
      update_logs("$email", "SECURITY ALERT! unsuccessful request made to reset account with the email");

      set_message("A message was sent.", "success");

      header('Location: sign-in.php');
      }  
    }
  }
?>

<h1>Reset Account</h1>

<p class="w-75 lead mx-auto">To reset your password, please enter an email below. If you have a valid account, a password reset link will be sent to your email.</p>
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