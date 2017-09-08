<?php
/***
TR46:2016 TestKit
Author: Hazrul Azhar Jamari
Website: https://www.hazrulazhar.com
Github: https://github.com/abanghazrul
Description: A command line testkit that helps the auditing of Last Mile Delivery Provider APIs.
Version: 1.0
Date: 2017-03-24
License: MIT License.
***/

// this is the base class
include_once("tr46_base.php");

// load your own classes below if you need to extend the base class
include_once("tr46_yoursystem.php");

// Fix the timezone to Singapore
date_default_timezone_set('Asia/Singapore');

// ok lah. let's crank this up!
//$tr46_test = new TR46_Base();
$tr46_test = new TR46_YourSystem();
$tr46_test->main();

?>
