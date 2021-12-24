<?php

// includes
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/src/PHPAxieSchool/AxieSchool.php";
require_once __DIR__ . "/src/SleekDB/Store.php";
require_once __DIR__ . "/src/Template.php";
require_once __DIR__ . "/src/PHPMailer/PHPMailer.php";


// initiate AxieSchool
$AxieSchool = new AxieSchool();

// start the cron
// - update prices
// - update scholars
// - send email report to manager
// - send emails reports to scholars
$AxieSchool->cron();
