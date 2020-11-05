<?php
$title = "Calls";
$file = "calls.php";
$description = "This page presents a call input form that allows salespeople to input calls from their clients, and creates 
a timestamped record of the interaction in the calls table.";
$date = "October 22, 2020";

include "./includes/header.php";

// Redirect to sign-in page if the user is not a salesperson
if ($_SESSION['type'] != "a") {
    $output .= "Sorry, you must be logged in as a salesperson to access this page.";
    setMessage($output, "success");

    redirect("sign-in.php");
}

// Form submission logic
// When the page first loads or is reset, create empty variables that will attempt to collect info to insert into the calls table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $clientId = "";
    $reason = "";
}

// If the user has tried to insert an entry after the page first loads, store the data values in input fields
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clientId = trim($_POST["client"]);
    $reason  = trim($_POST["reason"]);
    $timeStamp =  date("Y-m-d G:i:s");
    $output = "";

    // Insert call info into call table
    $sql = "INSERT INTO calls(ClientId, Date, Reason) VALUES (
        '$clientId',
        '$timeStamp',
        '$reason'
        )
    ";

    $conn = db_connect();
    $result = pg_query($conn, $sql);

    // If any issues arise with entering the record into the calls database, display a notice of the failure
    if (!$result) {
        $output .= "Sorry, this entry failed to be inserted into the records.";
    } else {
        // If the query produces a result, flash a message declaring the successful creation of the call record
        setMessage("The client interaction was successfully registered into our records.", "success");
        $message = flashMessage();

        // Log call creation event in activity logs
        updateLogs("$clientId", "successfully added a call with client #$clientId to records");

        // Clear all fields once the call is successfully entered in the db
        $clientId = "";
        $reason = "";
    }
}
?>

<h1 class="h2">Calls</h1>

<h6 class="w-75 mx-auto">Please enter the details of any customer calls in the form below. Each salesperson assigned to a particular customer is responsible for handling their customer's inquiry.</h6>

<h5 class="text-success w-50-lg px-5 py-2"><?php echo $message; ?></h5>
<?php
display_form(
    array(
        array(
            "type" => "select",
            "name" => "client",
            "value" => "",
            "label" => "Client",
            "isDropdown" => true
        ),
        array(
            "type" => "reason",
            "name" => "reason",
            "value" => "",
            "label" => "Reason for Inquiry",
            "isDropdown" => false
        )
    )
);

display_table(
    array(
        "id" => "ID",
        "clientid" => "Client Id",
        "date" => "Date",
        "reason" => "Reason for Inquiry"
    ),
    calls_select_all(),
    calls_count()
);
?>

<?php
include "./includes/footer.php";
?>