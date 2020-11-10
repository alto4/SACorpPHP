<?php
$title = "Change Password";
$file = "change-password.php";
$description = "This page presents a call input form that allows salespeople to input calls from their clients, and creates 
a timestamped record of the interaction in the calls table.";
$date = "October 22, 2020";

include "./includes/header.php";

// Redirect to sign-in page if the user is not a salesperson
if (!$_SESSION) {
  $output .= "Sorry, you must be logged in  to access this page.";
  setMessage($output, "success");

  redirect("sign-in.php");
}
$output = "";
// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to collect info to insert into the calls table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $clientId = "";
  $reason = "";
}

// If the user has tried to insert an entry after the page first loads, store the data values in input fields
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_SESSION['email'];
  $newPassword = trim($_POST["password"]);
  $passwordConfirm  = trim($_POST["confirm"]);

  // Update password in the users table
  $sql = "UPDATE users
          SET password = '$newPassword' 
          WHERE emailAddress = '$email'
    ";

  $conn = db_connect();
  $result = pg_query($conn, $sql);

  // If any issues arise with entering the record into the calls database, display a notice of the failure
  if (!$result) {
    $output .= "Sorry, this entry failed to be updated in our records.";
  } else {
    // If the query produces a result, flash a message declaring the successful creation of the call record
    setMessage("You password was successfully updated.", "success");
    $message = flashMessage();

    // Log call creation event in activity logs
    updateLogs("$email", "successfully updated their account password");

    // Clear all fields once the call is successfully entered in the db
    $newPassword = "";
    $passwordConfirm = "";
  }
}
?>

<h1>Change Password</h1>

<p class="w-75 lead mx-auto">To change your password, please enter a new password below, and then verify it by retyping it in the second field.</h6>

  <h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>
  <?php
  display_form(
    array(
      array(
        "type" => "password",
        "name" => "password",
        "value" => "",
        "label" => "New Password",
        "isDropdown" => false
      ),
      array(
        "type" => "password",
        "name" => "confirm",
        "value" => "",
        "label" => "Re-Type Password",
        "isDropdown" => false
      )
    )
  );
  ?>

  <?php
  include "./includes/footer.php";
  ?>