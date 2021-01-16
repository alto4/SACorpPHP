<?php
$title = "Clients";
$file = "clients.php";
$description = "This page presents a clients input form that allows salespeople or administrators to input clients into
a record that is stored in the clients table. If the user is logged in as an administrator, a dropdown menu will be used
to designate a salesperson as responsible for that client. Otherwise, if a salesperson is logged in, it is assumed that 
they are the one managing the new client's account.";
$date = "November 18, 2020";

include "./includes/header.php";

// Redirect to sign-in page if a session has not been authorized
if (!$_SESSION) {
    $output .= "Sorry, you must be logged in to access that page.";
    set_message($output, "success");
    redirect("sign-in.php");
}

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to input data
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $first_name = "";
    $last_name = "";
    $salesperson_id = "";
    $email  = "";
    $phone = "";
    $logo = "";
    // Validation output
    $output = " ";
}
// If an attempt has been made to enter client details after the page first loads, attempt to validate the provided information 
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    // If a salesperson has been assigned by an admin in the dropdown input, capture it's value (the selected salesperson's ID)
    if (isset($_POST["salesperson_id"])) {
        $salesperson_id = $_POST["salesperson_id"];
    }

    // If the user is logged in as a salesperson, capture their id for assigning them to the new client
    if ($_SESSION['type'] == "a") {
        $salesperson_id = $_SESSION['id'];
    }

    $email  = trim($_POST['email']);
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
            $logo_url = "./logos/logo-client-$phone.jpg";
            move_uploaded_file($_FILES['logo']['tmp_name'], $logo_url);
        }
    }
    // FIRST NAME VALIDATIONS
    // Verify that the client's first name was entered, and if not, display an error message
    if (!isset($first_name) || $first_name == "") {
        $output .= "You must enter the client's first name.</br>";
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
    // Verify that client's last name was entered, and if not, display an error message
    if (!isset($last_name) || $last_name == "") {
        $output .= "You must enter the client's last name.</br>";
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
    $result = client_create($first_name, $last_name, $salesperson_id, $email, $phone, 'c', $logo_url);

    if ($result == false) {
        $output .= "Sorry, this entry failed to be inserted into the records.";
    } else {
        // Display success message that client was created without error
        set_message("$first_name $last_name was successfully registered into our records as a client.", "success");
        $message = flash_message();

        // Log client creation event
        update_logs("$first_name $last_name", "successfully created as a client");

        // Clear all fields after new client is successfully inputted to db
        $first_name = "";
        $last_name = "";
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
        $clients_domain = "all";
        // Client Input Form
        display_form(
            array(
                array(
                    "type" => "text",
                    "name" => "first_name",
                    "value" => $first_name,
                    "label" => "First Name",
                    "is_dropdown" => false
                ),
                array(
                    "type" => "text",
                    "name" => "last_name",
                    "value" => $last_name,
                    "label" => "Last Name",
                    "is_dropdown" => false
                ),
                array(
                    "type" => "select",
                    "name" => "salesperson_id",
                    "value" => $salesperson_id,
                    "label" => "Salesperson",
                    "is_dropdown" => true
                ),
                array(
                    "type" => "email",
                    "name" => "email",
                    "value" => $email,
                    "label" => "Email Address",
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
                    "type" => "file",
                    "name" => "logo",
                    "value" => $logo,
                    "label" => "Client Logo",
                    "is_dropdown" => false
                )
            )
        );
    } else {
        // Set salesperson to logged in user
        $clients_domain = $_SESSION['id'];

        // Client Input Form if salesperson logged in
        display_form(
            array(
                array(
                    "type" => "text",
                    "name" => "first_name",
                    "value" => $first_name,
                    "label" => "First Name",
                    "is_dropdown" => false
                ),
                array(
                    "type" => "text",
                    "name" => "last_name",
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
                    "type" => "number",
                    "name" => "phone",
                    "value" => $phone,
                    "label" => "Phone Number",
                    "is_dropdown" => false
                ),
                array(
                    "type" => "file",
                    "name" => "logo",
                    "value" => $logo,
                    "label" => "Client Logo",
                    "is_dropdown" => false
                )
            )
        );
    }

    echo '<h1>Active Clients</h1>';

    display_table(
        array(
            "id" => "ID",
            "email_address" => "Email Address",
            "first_name" => "First Name",
            "last_name" => "Last Name",
            "salesperson_id" => "Salesperson",
            "phone_number" => "Phone Number",
            "logo" => "Logo"
        ),
        client_select_all($clients_domain),
        client_count($clients_domain),
        1
    );
    ?>

    <?php
    include "./includes/footer.php";
    ?>