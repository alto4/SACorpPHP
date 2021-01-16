<?php
$title = "Login Page";
$file = "sign-in.php";
$description = "This page contains the sign-in form for the entire site, and a user's form submission is validated by several conditional statements to guide
                their input, and once input is valid, and attempt to authenticate the user's email and password is attempted. If the details pass,
                the user will be redirected to the user dashboard";
$date = "November 18, 2020";

include "./includes/header.php";

// Check for user input in required fields and process login transaction
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $email = "";
    $password = "";
    $output = "";
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $output = "";

    // Verify that user id was entered, and if not, display an error message
    if (!isset($email) || $email == "") {
        $output .= "Please enter an email address.</br>";
    };

    // Verify that user password was entered, and if not, display an error message
    if (!isset($password) || $password == "") {
        $output .= "You must enter your password to login</br>";
    };

    // If both a login and a password have been set by the user, proceed to compare them to the database entries of user info
    if ($output == "" && user_select($email) == false) {
        $output .= "The email address " . $email . "<br/> has not been registered.";
        $email = "";
        $password = "";
    
    }

    if ($output == "" && user_check_status($email) == false) {
        $output = "Sorry, the account associated with the email address $email is currently disabled.<br/>Please contact a site administrator to regain access.";
        $password = "";
    }

    if ($output == "" && user_authenticate($email, $password) != true) {

        $output .= "The password you have entered is incorrect.<br />Please try again.";
        $email = "";
        $password = "";
    }

    
}

?>

<div class="text-align-center">
    <h1>Sign In</h1>
    <div class="container d-flex justify-content-center w-100">
        <h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>
    </div>
    <h5 class="text-danger"><?php echo $output; ?></h5>

    <div>
        <!-- Sign-in form -->
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-signin">
            <label for="inputEmail" class="sr-only">Email address</label>
            <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
            <button class="btn btn-lg btn-success btn-block" type="submit">Sign in</button>
        </form>
    </div>
</div>

<?php
include "./includes/footer.php";
?>