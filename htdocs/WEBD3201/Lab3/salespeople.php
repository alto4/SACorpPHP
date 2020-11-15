<?php
$title = "Salespeople";
$file = "salespeople.php";
$description = "This page presents a salesperson input form that allows administrators to input news salespeople, and creates 
a record of their active employment in the salesperson table. Upon creation, the salesperson will also be assigned login
credentials and entered as a registered user in the users table.";
$date = "October 22, 2020";

include "./includes/header.php";

// Redirect to sign-in page if the user is not authorized as a administrator able to create a new salesperson/site user
if ($_SESSION['type'] != "s") {
    $output .= "Sorry, you must be logged in as an administrator to access this page.";
    setMessage($output, "success");
    redirect("sign-in.php");
}

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to collect user login and password that wil match with an entry in the database
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = "";
    $firstName = "";
    $lastName = "";
    $email  = "";
    $password = "";
    $phone = "";
    $extension = "";

    // Validation output
    $output = " ";
}
// If the user has tried to register a new salesperson after the page first loads, attempt to validate the provided information 
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = trim($_POST["password"]);
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $email  = trim($_POST["email"]);
    $phone = trim($_POST['phone']);
    $extension = trim($_POST['extension']);
    $output = "";

    // PASSWORD VALIDATIONS
    // Verify that user password was entered, and if not, display an error message
    if (!isset($password) || $password == "") {
        $output .= "You must enter a password to register this salesperson.</br>";
    }
    // Check that password meets minimum length requirements
    else if (strlen("$password") < MIN_PASSWORD_LENGTH) {
        $output .= "The password must be greater than " . MIN_PASSWORD_LENGTH . " characters in length.<br/>";
        $password = "";
    }

    // FIRST NAME VALIDATIONS
    // Verify that salesperson's first name was entered, and if not, display an error message
    if (!isset($firstName) || $firstName == "") {
        $output .= "You must enter salesperson's first name.</br>";
    }
    // Check that the first name does not exceed the maximum field length requirements
    else if (strlen("$firstName") > MAX_FIRST_NAME_LENGTH) {
        $output .= "The first name entered cannot exceed " . MAX_FIRST_NAME_LENGTH . " characters in length.<br/>";
        $firstname = "";
    }
    // Check that the first name does not contain any numeric entries
    else if (is_numeric($firstName)) {
        $output .= "The first name entered may not contain any numeric characters. Please only enter letters from the alphabet.<br/>";
        $firstname = "";
    };

    // LAST NAME VALIDATIONS
    // Verify that salesperson's last name was entered, and if not, display an error message
    if (!isset($lastName) || $lastName == "") {
        $output .= "You must enter the salesperon's last name.</br>";
    }
    // Check that the last name does not exceed the maximum file length requirements
    else if (strlen("$lastName") > MAX_LAST_NAME_LENGTH) {
        $output .= "The last name entered cannot exceed " . MAX_LAST_NAME_LENGTH . " characters in length.<br/>";
        $lastName = "";
    }
    // Check that the last name does not contain any numeric entries
    else if (is_numeric($lastName)) {
        $output .= "The last name entered may not contain any numeric characters. Please only enter letters from the alphabet.<br/>";
        $lastName = "";
    };

    // EMAIL VALIDATIONS
    // Verify that user email was entered, and if not, display an error message
    if (!isset($email) || $email == "") {
        $output .= "You must enter an email to for the salesperson.</br>";
    }
    // Use filter_var to validate that email contains required characters
    else if (!(filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $output .= $email . " is not a valid email address. Please try again.<br/>";
        $email = "";
    }

    // SQL Query to check if ID exists in the database records 
    $result = user_select($email);

    // If the email is already registered for another user account, display an error message requiring a unique email for proceeding
    if ($result != false) {
        $output .= "This email already exists in the records. Please enter a unique email for the new salesperson.<br />.";
    }

    // PHONE 
    // Verify that salesperson phone was entered, and if not, display an error message
    if (!isset($phone) || $phone == "") {
        $output .= "You must enter a phone number for the salesperson.</br>";
    }
    // Use filter_var to validate that email contains required characters
    else if (!(is_numeric($phone)) || strlen("$phone") < MIN_PHONE_NUM_LENGTH) {
        $output .= $phone . " is not a valid phone number.<br/>";
        $phone = "";
    }
    // END OF VALIDATIONS        

    // If there are no validation or errors and all input has been validated, proceed to add user information to the database to complete the registration process
    if ($output == "") {
        // Insert salesperson info into salespeople table
        $result = salesperson_create($firstName, $lastName, $email, $password, $phone, $extension, 'a');

        // If the query is unsuccessful, inform the user of this failure
        if ($result == false) {
            $output .= "Sorry, this entry failed to be inserted into the records.";
        } else {
            // Display success message that salesperson was created without error
            setMessage("$firstName $lastName was successfully registered into our records as a salesperson.", "success");
            $message = flashMessage();

            // Log salesperson creation event
            updateLogs("$firstName $lastName", "successfully created as a salesperson");

            // Clear all fields once salesperson is successfully entered in the db
            $id = "";
            $password = "";
            $firstName = "";
            $lastName = "";
            $email  = "";
            $password = "";
            $phone = "";
            $extension = "";
        }
    }
}
?>
<h1 class="py-3">New Salespeople</h1>

<p class="w-75 lead mx-auto">When a new salesperson is hired, please ensure that they are promptly entered as a user in the system. Salespeople cannot enter other salespeople,
    however they are granted permission to create new clients and calls in the our records.</p>




<h5 class="text-danger"><?php echo $output; ?></h5>
<h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>

<!-- Salesperson registration form -->
<?php // Generate salesperson data input form using display_form function
display_form(
    array(
        array(
            "type" => "text",
            "name" => "firstName",
            "value" => $firstName,
            "label" => "First Name",
            "isDropdown" => false
        ),
        array(
            "type" => "text",
            "name" => "lastName",
            "value" => $lastName,
            "label" => "Last Name",
            "isDropdown" => false
        ),
        array(
            "type" => "email",
            "name" => "email",
            "value" => $email,
            "label" => "Email Address",
            "isDropdown" => false
        ),
        array(
            "type" => "password",
            "name" => "password",
            "value" => "",
            "label" => "Password",
            "isDropdown" => false
        ),
        array(
            "type" => "phone",
            "name" => "phone",
            "value" => $phone,
            "label" => "Phone Number",
            "isDropdown" => false
        ),
        array(
            "type" => "number",
            "name" => "extension",
            "value" => $extension,
            "label" => "Extension",
            "isDropdown" => false
        )
    )
);
?>

<h1>Active Salespeople</h1>

<?php
display_table(
    array(
        "id" => "ID",
        "firstname" => "First Name",
        "lastname" => "Last Name",
        "emailaddress" => "Email Address",
        "phonenumber" => "Phone Number",
        "phoneext" => "Phone Ext."
    ),
    salespeople_select_all(),
    salespeople_count(),
    1
);
?>
<?php
include "./includes/footer.php";
?>