<?php
    $title = "Home";
    $file = "index.php";
    $description = "Lab #1 is captured in this page and demonstrates several skills using PHP, including: database set-up, user login functionality, and...";
    $date = "October 2, 2020";

    include "./includes/header.php";

?>

<h2 class="text-success"><?php echo $message; ?></h2>
<div class="jumbotron">
  <h1 class="display-4">Welcome to the S/A Corp. Website</h1>
  <p class="lead">This site will showcase various PHP/PostgreSQL skills learned throughout the semester in WEB3201.</p>
  <hr class="my-4">
  <p class="lead">
    <a class="btn btn-success btn-lg mx-auto" href="#" role="button">Learn more</a>
  </p>
  <img class="img-fluid" src="https://images.unsplash.com/photo-1482015527294-7c8203fc9828?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=600&q=80" alt="A fall forest with leaves of various changing colours." />
</div>

<?php
    include "./includes/footer.php";
?>    