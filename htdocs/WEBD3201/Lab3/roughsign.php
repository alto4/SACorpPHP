<?php
    $title = "Login Page";
    $file = "sign-in.php";
    $description = "This page contains the sign-in form for the entire site, and a user's form submission is validated by several conditional statements to guide
                     their input, and once input is valid, and attempt to authenticate the user's email and password is attempted. If the details pass,
                     the user will be redirected to the user dashboard";
    $date = "October 2, 2020";

    include "./includes/header.php";
    
    // If a session already exists, it will be destroyed and captured in the logs
    if($_SESSION) {

       session_destroy(); 

       // Log sign out event
       updateLogs($email, "sign-out");    
    }  
 
    // Check for user input in required fields and process login transaction
    if($_SERVER["REQUEST_METHOD"]=="GET")
    {
        $email = "";
        $password = "";
        $output = "";
    } 
    else if($_SERVER["REQUEST_METHOD"]=="POST")
    {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $output = "";
        
        // Save the function that connects to the database as a variable
        $conn = db_connect();
        $user = user_select($email);

        // Verify that user id was entered, and if not, display an error message
        if(!isset($email) || $email == "")
        {
            $output .= "Please enter an email address.</br>";
        };

        // Verify that user password was entered, and if not, display an error message
        if(!isset($password) || $password == "")
        {
            $output .= "You must enter your password to login</br>";
        };

        
              // Check entered password against the password associated with the entered id that exists in the database
                if(user_select($email) == true)
                {
                    $userInfo = user_select($email);

                    if(user_authenticate($userInfo['EmailAddress'], $password) == true)
                    {
                        // Start a new session upon authentication
                        //session_start();             

                        // Log valid login 
                        updateLogs($email, "successful sign-in");   

                        // If email and password are authenticated, output a welcome message to the user with a brief summary of their account activity
                        $output .= "Welcome back! Your account is associated with the email address " . pg_fetch_result($result, 0, "emailaddress") . " and you were last logged in on " . pg_fetch_result($result, 0, "lastaccess") . ".";
                
                        $_SESSION['email'] = htmlentities($email);
                        $_SESSION['password'] = htmlentities($password);

                        // Upon successful login, redirect user back to the dashboard page                   

                        setMessage($output);
                        
                        header('Location: dashboard.php');
                    }    
                }
                // If password does not match the corresponding id, output an error message
                else                 
                {
                    $output .= "The password you have entered is incorrect.<br />Please try again.";
                    $password = "";

                    updateLogs($email, "unsuccessful login due to bad password");   
                }
            
            
            //If the user id is not found in the database records, display an error message and clear form fields
            // else
            // {
            //     $output .= "The email address <br/>" . $email . "<br/> has not been registered.";
            //     $email = "";
            //     $password = "";

            //     // Log invalid attempt 
            //     updateLogs("unknown", "attemped sign-in without a valid email");    
            // }
        
    }

    // Display the results of the above authentication and valdiation tests
    echo "<h5 class='text-danger'>" . $output . "</h5>";
?>   
    
<div class="text-align-center">
    <p class="text-danger"><?php echo $message; ?></p>
<div>   
<!-- Sign-in form -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-signin">
    <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1> 
    <label for="inputEmail" class="sr-only">Email address</label>
    <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
    <button class="btn btn-lg btn-success btn-block" type="submit">Sign in</button>
</form>

<?php
    include "./includes/footer.php";
?>    