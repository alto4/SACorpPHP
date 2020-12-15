<?php
$title = "Change Password";
$file = "change-password.php";
$description = "This page contains a password reset form that confirms a new user's password by performing basic validations.";
$date = "October 22, 2020";

include "./includes/header.php";

// Redirect to sign-in page if the user is not a salesperson
if (!$_SESSION) {
  $output .= "Sorry, you must be logged in  to access this page.";
  set_message($output, "success");

  redirect("sign-in.php");
}
$output = "";

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to collect info to insert into the calls table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $client_id = "";
  $reason = "";
}

// If the user has tried to insert an entry after the page first loads, store the data values in input fields
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_SESSION['email'];
  $new_password = trim($_POST["password"]);
  $password_confirm  = trim($_POST["confirm"]);

  // PASSWORD VALIDATIONS
  // Confirm that password is at least 3 characters in length
  if (strlen($new_password) < MIN_PASSWORD_LENGTH) 
  {
    $output .= "Your new password must be at least 3 characters in length.";
  }
  else if ($new_password !== $password_confirm) 
  {
    $output .= "The passwords entered in both fields must match for the change to be processed.";
  }

  if ($output == "") 
  {
    $result = user_update_password($email, $new_password);

    // If any issues arise with entering the record into the calls database, display a notice of the failure
    if ($result == false) {
      $output .= "Sorry, this entry failed to be updated in our records.";
    } 
    else 
    {
      
      // If the query produces a result, log password update event in activity logs
      update_logs("$email", "successfully updated their account password");
      
      // Redirect user to the dashboard and flash a message declaring the successful password update
      set_message("You password was successfully updated.", "success");
      header('Location: dashboard.php');
    }
  }
}
?>

<h1>Change Password</h1>

<p class="w-75 lead mx-auto">To change your password, please enter a new password below, and then verify it by retyping it in the second field.</p>
  <h5 class="text-danger"><?php echo $output ?></h5>
  <h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>
  <div>
    <?php
    display_form(
      array(
        array(
          "type" => "password",
          "name" => "password",
          "value" => "",
          "label" => "New Password",
          "is_dropdown" => false
        ),
        array(
          "type" => "password",
          "name" => "confirm",
          "value" => "",
          "label" => "Re-Type Password",
          "is_dropdown" => false
        )
      )
    );
    ?>

    <?php
    include "./includes/footer.php";
    ?>