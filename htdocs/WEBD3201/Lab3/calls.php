<?php
$title = "Calls";
$file = "calls.php";
$description = "This page presents a call input form that allows salespeople to input calls from their clients, and creates 
a timestamped record of the interaction in the calls table.";
$date = "November 18, 2020";

include "./includes/header.php";

// Redirect to sign-in page if the user is not a salesperson
if ($_SESSION['type'] != "a") {
    $output .= "Sorry, you must be logged in as a salesperson to access this page.";
    set_message($output, "success");

    redirect("sign-in.php");
} else {
    $salesperson_id = $_SESSION['id'];
}

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to collect info to insert into the calls table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $client_id = "";
    $reason = "";
}

// If the user has tried to insert an entry after the page first loads, store the data values in input fields
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = trim($_POST["client"]);
    $reason = trim($_POST["reason"]);
    $time_stamp =  date("Y-m-d G:i:s");
    $output = "";

    $result = call_create($client_id, $time_stamp, $reason);

    // If any issues arise with entering the record into the calls database, display a notice of the failure
    if ($result == false) {
        $output .= "Sorry, this entry failed to be inserted into the records.";
    } else {
        // If the query produces a result, flash a message declaring the successful creation of the call record
        set_message("The client interaction was successfully registered into our records.", "success");
        $message = flash_message();

        // Log call creation event in activity logs
        update_logs("$client_id", "successfully added a call with client #$client_id to records");

        // Clear all fields once the call is successfully entered in the db
        $client_id = "";
        $reason = "";
    }
}
?>

<h1>New Calls</h1>

<p class="w-75 lead mx-auto">Please enter the details of any customer calls in the form below. Each salesperson assigned to a particular customer is responsible for handling their customer's inquiry.</p>

    <h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>

    
    <?php
    display_form(
        array(
            array(
                "type" => "select",
                "name" => "client",
                "value" => "",
                "label" => "Client",
                "is_dropdown" => true
            ),
            array(
                "type" => "reason",
                "name" => "reason",
                "value" => "",
                "label" => "Reason for Inquiry",
                "is_dropdown" => false
            )
        )
    );
    ?>

    <h1>Record of Calls</h1>

    <?php
    display_table(
        array(
            "id" => "ID",
            "client_id" => "Client Id",
            "first_name" => "First Name",
            "last_name" => "Last Name",
            "date" => "Date",
            "reason" => "Reason for Inquiry"
        ),
        calls_select_all($salesperson_id),
        calls_count($salesperson_id),
        1
    );
    ?>

    <?php
    include "./includes/footer.php";
    ?>