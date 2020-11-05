<?php 

  /*
    Name: Scott Alton
    Date: October 2, 2020
    File: _serverconstants.php
    Description: This file contains constant declarations for various user types, as well as database details 
  */

  // User Types
  define("ADMIN", "s");
  define("AGENT", "a");
  define("CLIENT", "c");
  define("PENDING", "p");
  define("DISABLED", "d");

  // Database Constants
  define("DB_HOST", "localhost");
  define("DATABASE", "altons_db");
  define("DB_ADMIN", "altons");
  define("DB_PORT", "5432");
  define("DB_PASSWORD", "password");

?>