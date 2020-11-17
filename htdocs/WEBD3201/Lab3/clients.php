<?php
$title = "Clients";
$file = "clients.php";
$description = "This page presents a clients input form that allows salespeople or administrators to input clients into
a record that is stored in the clients table. If the user is logged in as an administrator, a dropdown menu will be used
to designate a salesperson as responsible for that client. Otherwise, if a salesperson is logged in, it is assumed that 
they are the one managing the new client's account.";
$date = "October 22, 2020";

include "./includes/header.php";

// Redirect to sign-in page if a session has not been authorized
if (!$_SESSION) {
    $output .= "Sorry, you must be logged in to access that page.";
    setMessage($output, "success");
    redirect("sign-in.php");
}

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to input data
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $firstName = "";
    $lastName = "";
    $salesperonId = "";
    $email  = "";
    $phone = "";
    $logo = "";
    // Validation output
    $output = " ";
}
// If an attempt has been made to enter client details after the page first loads, attempt to validate the provided information 
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    // If a salesperson has been assigned by an admin in the dropdown input, capture it's value (the selected salesperson's ID)
    if (isset($_POST["salespersonId"])) {
        $salespersonId = $_POST["salespersonId"];
    }

    // If the user is logged in as a salesperson, capture their id for assigning them to the new client
    if ($_SESSION['type'] == "a") {
        $salespersonId = $_SESSION['id'];
    }

    $email  = trim($_POST["email"]);
    $phone = trim($_POST['phone']);
    $output = "";

    // FILE UPLOAD VALIDATIONS
    // Check for file upload errors
    if (count($_FILES) > 0) {
        $logo = $_FILES['logo']['name'];

        if ($_FILES['logo']['error'] != 0) {
            $output .= "There was an issue uploading your file. Please try again.<br/>";
        } else if ($_FILES['logo']['size'] > MAX_FILE_SIZE) {
            $output .= "The selected file is too large. Please upload a file no larger than " . (MAX_FILE_SIZE / 1000) . " KB.<br/>";
        } else if ($_FILES['logo']['type'] != "image/jpeg" && $_FILES['logo']['type'] != "image/pjpeg" && $_FILES['logo']['type'] != "image/jpg") {
            $output .= "Only upload JPG, JPEG, or PJPEG file types may be used for the logo.";
        } else {
            // MOVE UPLOADED FILE
            $logoUrl = "./logos/logo-client-$phone.jpg";
            move_uploaded_file($_FILES['logo']['tmp_name'], $logoUrl);
        }
    }
    // FIRST NAME VALIDATIONS
    // Verify that the client's first name was entered, and if not, display an error message
    if (!isset($firstName) || $firstName == "") {
        $output .= "You must enter the client's first name.</br>";
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
    // Verify that client's last name was entered, and if not, display an error message
    if (!isset($lastName) || $lastName == "") {
        $output .= "You must enter the client's last name.</br>";
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
        $output .= "You must enter an email for the new client.</br>";
    }
    // Use filter_var to validate that email contains required characters and format
    else if (!(filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $output .= $email . " is not a valid email address. Please try again.<br/>";
        $email = "";
    }
    // If the email is already registered for another user account, display an error message requiring a unique email for proceeding
    else if (client_select($email) == true) {
        $output .= "This email already exists in the records. Please enter a unique email for the new client.<br />.";
    }

    // PHONE 
    // Verify that salesperson phone was entered, and if not, display an error message
    if (!isset($phone) || $phone == "") {
        $output .= "You must enter the client's phone number.</br>";
    }
    // Validate that the phone number input contains only numeric characters, and is at least 10 characters in length
    else if (!(is_numeric($phone)) || strlen("$phone") < MIN_PHONE_NUM_LENGTH) {
        $output .= $phone . " is not a valid phone number.<br/>";
        $phone = "";
    }

    // END OF VALIDATIONS        
}

// If there are no validation or errors and all input has been validated, proceed to add client information to the database to complete the registration process
if ($output == "") {
    $result = client_create($firstName, $lastName, $salespersonId, $email, $phone, 'c', $logoUrl);

    if ($result == false) {
        $output .= "Sorry, this entry failed to be inserted into the records.";
    } else {
        // Display success message that client was created without error
        setMessage("$firstName $lastName was successfully registered into our records as a client.", "success");
        $message = flashMessage();

        // Log client creation event
        updateLogs("$firstName $lastName", "successfully created as a client");

        // Clear all fields after new client is successfully inputted to db
        $firstName = "";
        $lastName = "";
        $email  = "";
        $phone = "";
        $logo = "";
    }
}
?>
<h1>New Clients</h1>
<h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>
<p class="lead">Please enter the details of all new clients in the form below.</h6>
    <h5 class="text-danger"><?php echo $output; ?></h5>

    <?php
    if ($_SESSION['type'] == "s") {
        $clientsDomain = "all";
        // Client Input Form
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
                    "type" => "select",
                    "name" => "salesperson",
                    "value" => "12",
                    "label" => "Salesperson",
                    "isDropdown" => true
                ),
                array(
                    "type" => "email",
                    "name" => "email",
                    "value" => $email,
                    "label" => "Email Address",
                    "isDropdown" => false
                ),
                array(
                    "type" => "number",
                    "name" => "phone",
                    "value" => $phone,
                    "label" => "Phone Number",
                    "isDropdown" => false
                ),
                array(
                    "type" => "file",
                    "name" => "logo",
                    "value" => $logo,
                    "label" => "Client Logo",
                    "isDropdown" => false
                )
            )
        );
    } else {
        // Set salesperson to logged in user
        $clientsDomain = $_SESSION['id'];

        // Client Input Form if salesperson logged in
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
                    "type" => "number",
                    "name" => "phone",
                    "value" => $phone,
                    "label" => "Phone Number",
                    "isDropdown" => false
                ),
                array(
                    "type" => "file",
                    "name" => "logo",
                    "value" => $logo,
                    "label" => "Client Logo",
                    "isDropdown" => false
                )
            )
        );
    }

    echo '<h1>Active Clients</h1>';

    display_table(
        array(
            "id" => "ID",
            "emailaddress" => "Email Address",
            "firstname" => "First Name",
            "lastname" => "Last Name",
            "phonenumber" => "Phone Number",
            "logo_path" => "Logo"
        ),
        client_select_all($clientsDomain),
        client_count($clientsDomain),
        1
    );
    ?>

    <?php
    include "./includes/footer.php";
    ?>