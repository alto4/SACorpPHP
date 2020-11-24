<?php
$title = "Home";
$file = "index.php";
$description = "Lab #1 is captured in this page and demonstrates several skills using PHP, including: database set-up, user login functionality, and...";
$date = "October 2, 2020";

include "./includes/header.php";

?>

<h2 class="text-success"><?php echo $message; ?></h2>
<div class="jumbotron">
  <h1 class="display-4">Welcome to S/A Corp.</h1>
  <p class="lead w-75 mx-auto">Specializing in custom online marketing solutions, S/A Corp. focuses on helping small and medium size businesses establish an online presence.</p>
  <hr class="my-4">
  <p class="lead">
    <a class="btn btn-success btn-lg mx-auto" href="#" role="button">Learn more</a>
  </p>
  <img class="img-fluid mt-5" src="https://images.pexels.com/photos/905163/pexels-photo-905163.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=225&w=300" alt="A desk holding various office items." />
</div>

<?php
include "./includes/footer.php";
?>