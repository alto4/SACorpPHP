<?php
$title = "Salespeople";
$file = "salespeople.php";
$description = "This page presents a salesperson input form that allows administrators to input news salespeople, and creates 
a record of their active employment in the salesperson table. Upon creation, the salesperson will also be assigned login
credentials and entered as a registered user in the users table.";
$date = "November 18, 2020";

include "./includes/header.php";

// Redirect to sign-in page if the user is not authorized as a administrator able to create a new salesperson/site user
if ($_SESSION['type'] != "s") {
    $output .= "Sorry, you must be logged in as an administrator to access this page.";
    set_message($output, "success");
    redirect("sign-in.php");
}

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to collect user login and password that wil match with an entry in the database
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = "";
    $first_name = "";
    $last_name = "";
    $email  = "";
    $password = "";
    $phone = "";
    $extension = "";
    

    // Validation output
    $output = " ";
}
// TODO : WORK ON NESTED FORM SUBMISSION LOGIC - CURRENTLY FLAGGING - NEED TO INCORPORATE
//    salesperson_enable/salesperson_disable prepared statements based on values for fields in debug string
// Nested form submssion logic - catches POST request with name of ''
else if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submitType"] == "Update") {
    $output = "";
    $id = "";
    $first_name = "";
    $last_name = "";
    $email  = "";
    $password = "";
    $phone = "";
    $extension = "";
        
    $id = (array_keys($_POST['active'])[0]); 
    $status = $_POST['active'][$id];

    // Filter status update to trigger corresponding prepared statement 
    if($status == "t") {
        enable_salesperson($id);
    } else {
        disable_salesperson($id);
    }

    // echo "Salesperson ID: $id";
    // echo "<br/>Updated Active to: $status";
}

// If the user has tried to register a new salesperson after the page first loads, attempt to validate the provided information 
else if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submitType"] == "Create") {
    $password = trim($_POST["password"]);
    $first_name = trim($_POST["firstName"]);
    $last_name = trim($_POST["lastName"]);
    $email  = trim($_POST["email"]);
    $phone = trim($_POST['phone']);
    $extension = trim($_POST['extension']);
    $output = "";

    // PASSWORD VALIDATIONS
    // Verify that user password was entered, and if not, display an error message
    if (!isset($password) || $password == "") {
        $output .= "You must enter a password to register this salesperson.<br/>";
    }
    // Check that password meets minimum length requirements
    else if (strlen("$password") < MIN_PASSWORD_LENGTH) {
        $output .= "The password must be greater than " . MIN_PASSWORD_LENGTH . " characters in length.<br/>";
        $password = "";
    }

    // FIRST NAME VALIDATIONS
    // Verify that salesperson's first name was entered, and if not, display an error message
    if (!isset($first_name) || $first_name == "") {
        $output .= "You must enter salesperson's first name.<br/>";
    }
    // Check that the first name does not exceed the maximum field length requirements
    else if (strlen("$first_name") > MAX_FIRST_NAME_LENGTH) {
        $output .= "The first name entered cannot exceed " . MAX_FIRST_NAME_LENGTH . " characters in length.<br/>";
        $first_name = "";
    }
    // Check that the first name does not contain any numeric entries
    else if (is_numeric($first_name)) {
        $output .= "The first name entered may not contain any numeric characters. Please only enter letters from the alphabet.<br/>";
        $first_name = "";
    };

    // LAST NAME VALIDATIONS
    // Verify that salesperson's last name was entered, and if not, display an error message
    if (!isset($last_name) || $last_name == "") {
        $output .= "You must enter the salesperon's last name.<br/>";
    }
    // Check that the last name does not exceed the maximum file length requirements
    else if (strlen("$last_name") > MAX_LAST_NAME_LENGTH) {
        $output .= "The last name entered cannot exceed " . MAX_LAST_NAME_LENGTH . " characters in length.<br/>";
        $last_name = "";
    }
    // Check that the last name does not contain any numeric entries
    else if (is_numeric($last_name)) {
        $output .= "The last name entered may not contain any numeric characters. Please only enter letters from the alphabet.<br/>";
        $last_name = "";
    };

    // EMAIL VALIDATIONS
    // Verify that user email was entered, and if not, display an error message
    if (!isset($email) || $email == "") {
        $output .= "You must enter an email to for the salesperson.<br/>";
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
        $output .= "You must enter a phone number for the salesperson.<br/>";
    }
    // Use filter_var to validate that email contains required characters
    else if (!(is_numeric($phone)) || strlen("$phone") < MIN_PHONE_NUM_LENGTH) {
        $output .= $phone . " is not a valid phone number.<br/>";
        $phone = "";
    }
    // END OF VALIDATIONS        

    // If there are no validation errors and all input has been validated, proceed to add user information to the database to complete the registration process
    if ($output == "") {
        // Insert salesperson info into salespeople table
        $result = salesperson_create($first_name, $last_name, $email, $password, $phone, $extension, 'a');

        // If the query is unsuccessful, inform the user of this failure
        if ($result == false) {
            $output .= "Sorry, this entry failed to be inserted into the records.";
        } else {
            // Display success message that salesperson was created without error
            set_message("$first_name $last_name was successfully registered into our records as a salesperson.", "success");
            $message = flash_message();

            // Log salesperson creation event
            update_logs("$first_name $last_name", "successfully created as a salesperson");

            // Clear all fields once salesperson is successfully entered in the db
            $id = "";
            $password = "";
            $first_name = "";
            $last_name = "";
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
            "value" => $first_name,
            "label" => "First Name",
            "is_dropdown" => false
        ),
        array(
            "type" => "text",
            "name" => "lastName",
            "value" => $last_name,
            "label" => "Last Name",
            "is_dropdown" => false
        ),
        array(
            "type" => "email",
            "name" => "email",
            "value" => $email,
            "label" => "Email Address",
            "is_dropdown" => false
        ),
        array(
            "type" => "password",
            "name" => "password",
            "value" => "",
            "label" => "Password",
            "is_dropdown" => false
        ),
        array(
            "type" => "number",
            "name" => "phone",
            "value" => $phone,
            "label" => "Phone Number",
            "is_dropdown" => false
        ),
        array(
            "type" => "number",
            "name" => "extension",
            "value" => $extension,
            "label" => "Extension",
            "is_dropdown" => false
        )
    )
);
?>

<h1>Active Salespeople</h1>

<?php
display_table(
    array(
        "id" => "ID",
        "first_name" => "First Name",
        "last_name" => "Last Name",
        "email_address" => "Email Address",
        "enabled" => "Is Active?",
        "phone_number" => "Phone Number",
        "phone_ext" => "Phone Ext."
    ),
    salespeople_select_all(),
    salespeople_count(),
    1
);
?>
<?php
include "./includes/footer.php";
?>