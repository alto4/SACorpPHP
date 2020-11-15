<?php
$title = "Login Page";
$file = "sign-in.php";
$description = "This page contains the sign-in form for the entire site, and a user's form submission is validated by several conditional statements to guide
                their input, and once input is valid, and attempt to authenticate the user's email and password is attempted. If the details pass,
                the user will be redirected to the user dashboard";
$date = "October 22, 2020";

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

    // Save the function that connects to the database as a variable
    $conn = db_connect();

    // Verify that user id was entered, and if not, display an error message
    if (!isset($email) || $email == "") {
        $output .= "Please enter an email address.</br>";
    };

    // Verify that user password was entered, and if not, display an error message
    if (!isset($password) || $password == "") {
        $output .= "You must enter your password to login</br>";
    };

    // If both a login and a password have been set by the user, proceed to compare them to the database entries of user info
    if ($output == "") {
        // Query the database
        $sql = "SELECT * FROM users WHERE EmailAddress ='$email'";
        $result = pg_query($conn, $sql);
        $records = pg_num_rows($result);

        // Match entered id against ids that exist in the database
        if ($records > 0) {
            // Check entered password against the password associated with the entered id that exists in the database
            if ($password == pg_fetch_result($result, 0, "password") || password_verify($password, pg_fetch_result($result, 0, "password"))) {
                // Start a new session upon authentication
                session_start();

                // Log valid login 
                updateLogs($email, "successful sign-in");

                // If email and password are authenticated, output a welcome message to the user with a brief summary of their account activity
                $output .= "Welcome back! Your account is associated with the email address " . pg_fetch_result($result, 0, "emailaddress") . " and you were last logged in on " . pg_fetch_result($result, 0, "lastaccess") . ".";

                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;

                // Update the session credential/user type to match what's stored in the database
                $_SESSION['type'] = pg_fetch_result($result, 0, "type");

                // If a salesperson is logged in, grab their id from the salesperson table for use in client interactions
                if ($_SESSION['type'] == "a") {
                    $sql = "SELECT id FROM salespeople WHERE EmailAddress ='$email'";
                    $result = pg_query($conn, $sql);
                    $_SESSION['id'] = pg_fetch_result($result, 0, "id");
                }

                // Upon successful login, redirect user back to the dashboard page                   
                update_last_login($email);
                user_authenticate($email, $password);

                setMessage($output, "success");

                header('Location: dashboard.php');
            }
            // If password does not match the corresponding id, output an error message
            else {
                $output .= "The password you have entered is incorrect.<br />Please try again.";
                $password = "";

                updateLogs($email, "unsuccessful login due to bad password");
            }
        }
        // If the user id is not found in the database records, display an error message and clear form fields
        else {
            $output .= "The email address " . $email . "<br/> has not been registered.";
            $email = "";
            $password = "";

            // Log invalid attempt 
            updateLogs("unknown", "attemped sign-in without a valid email");
        }
    }
}
?>

<div class="text-align-center">
    <h1 class="h3 mb-3 font-weight-normal">Sign In</h1>
    <h5 class='text-danger'><?php echo $message; ?></h5>
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