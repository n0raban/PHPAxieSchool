<?php

/*
// show errors
ini_set('display_errors', 1);
error_reporting(E_ALL); 
*/

// includes
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/src/PHPAxieSchool/AxieSchool.php";
require_once __DIR__ . "/src/SleekDB/Store.php";
require_once __DIR__ . "/src/Template.php";
require_once __DIR__ . "/src/PHPMailer/PHPMailer.php";

// initiate AxieSchool
$AxieSchool = new AxieSchool();

// build and display the manager report
echo $AxieSchool->html_manager_report();



/*

///// Other possible usages :

// update prices in db
$AxieSchool->update_prices();

// update scholars in db
// note : It is best to call the API at energy reset time, 
// as the result of the API keep the data cached for 3 hours
$AxieSchool->update_scholars();

// show the report the scholar will get (the parameter ronin_address must be one of your scholar defined in config)
echo $AxieSchool->html_scholar_report($ronin_address);

// send the email report to the manager
$AxieSchool->send_report_to_manager();

// send the email reports to the scholars
$AxieSchool->send_reports_to_scholars();


*/



