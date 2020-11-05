<?php

/*
    Name: Scott Alton
    Date: October 2, 2020
    File: constants.php
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

// Registration Validation Constants
define("MIN_PASSWORD_LENGTH", 8);
define("MAX_PASSWORD_LENGTH", 75);
define("MAX_FIRST_NAME_LENGTH", 40);
define("MAX_LAST_NAME_LENGTH", 50);
define("MIN_PHONE_NUM_LENGTH", 10);
