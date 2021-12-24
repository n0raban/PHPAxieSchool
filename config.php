<?php
/***********************************************
** SCHOLARS INFO
***********************************************/
define('SCHOLARS', 
       array(
	       
	///// SCHOLAR 1   
	// ronin address ( replace ronin: by 0x)
	"0xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" => array(
		"name" => "[Sc1] Scholar 1", // internal name
		"share" => 50, // scholar share in percent
		"reporting" => array( /// set to false if no individual report
			"contact_name" => "Scholar 1", // contact name
			"contact_email" => "scholar1_email@email.com", // scholar's email address
			"currency" => "php", // scholar's prefered currency
		),
	),
	       
	///// SCHOLAR 2  
	"0xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" => array(
		"name" => "[Sc2] Scholar 2",
		"share" => 50,
		"reporting" => array(

			"contact_name" => "Scholar 2",
			"contact_email" => "scholar2_email@email.com",
			"currency" => "php",
		),
	),
	       
	///// SCHOLAR 3 
	"0xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" => array(
		"name" => "[Sc3] Scholar 3",
		"share" => 50,
		"reporting" => array(

			"contact_name" => "Scholar 3",
			"contact_email" => "scholar3_email@email.com",
			"currency" => "php",
		),
	),

	///// SCHOLAR 4 
	"0xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" => array(
		"name" => "[Sc4] Scholar 4",
		"share" => 50,
		"reporting" => false, // example for no indiviual report for this scholar
	),
	       
	///// Add as many scholar you want ...
));

/***********************************************
** EMAL REPORTS
***********************************************/
// email where the daily reports will be sent to and sent from
// note that emails at hotmail.com will not receive the emails because of their spam policies
// you can still tweak PHPMailer to make it functionnal 
// this will be email address will be visible by scholars
define('MANAGER_EMAIL', 'manager@email.com');
//// Manager default currency
define('DEFAULT_CURRENCY', 'usd');
// emails sent by the script will use this name as sender 
define('GUILD_NAME', 'Guild Name');
// emails titles of reports
define('EMAIL_REPORTS_TITLE', 'Daily slp report');
// display next claim date in scholar individual report
define('SHOLARS_NEXT_CLAIM_DISPLAY', true);
// will be marked in red if slp earned by scholar is inferior to this number
define('MIN_DAILY_SLP', 75);

/***********************************************
** AXIE API / Scholars
***********************************************/
// url to call for the API
// this should not be changed unless updated by the dev team
define('AX_API_URL', 'https://game-api.axie.technology/api/v1/');
/// Fetching scholar data from API
/// sometimes the API server does not respond at first try
/// define here the number of attempts the script should execute before aborting
define('AX_API_FETCH_SC_DATA_MAX_ATTEMPT', 15);
/// how long to wait between each new attempt (in seconds)
define('AX_API_FETCH_SC_DATA_SLEEP_BETWEEN', 60);
// store name for scholars, keep the default value
define('SCHOLARS_STORE_NAME', 'scholars');

/***********************************************
** COINS / PRICES / COINGECKO API
***********************************************/
// coins to fetch @ prices updates, with their Coingecko IDs
define('COINGECKO_COINS', array('slp' => 'smooth-love-potion'));
// Coingecko API url
define('COINGECKO_API_URL', 'https://api.coingecko.com/api/v3/');
/// sometimes the API server does not respond at first try
/// define here the number of attempts the script should execute before aborting
define('COINGECKO_API_FETCH_PRICES_MAX_ATTEMPT', 7);
/// how long to wait between each new attempt (in seconds)
define('COINGECKO_API_FETCH_PRICES_SLEEP_BETWEEN', 30);
/// store name for prices, keep the default value
define('PRICES_STORE_NAME', 'prices');

/***********************************************
** EMAIL / SMTP
***********************************************/
// smtp server
// set to false if your hosting provider has sendmail enabled
define('USE_SMTP', true);
// you can use gmail as a free SMTP server
// in Gmail, turn "allow less secure apps" to ON. Be aware that this will make your gmail account less secure, 
// that's why it is important to create a new and dedicated gmail account for this.
// to allow less secure apps, when connected to your new gmail account, go to https://myaccount.google.com/lesssecureapps
// if it does not work check http://mail.google.com/mail for verification request
// if still does not work visit http://www.google.com/accounts/DisplayUnlockCaptcha
define('SMTP_SERVER', 'smtp.gmail.com');
define('SMTP_USER', 'smtp_address@gmail.com');
define('SMTP_PASSWORD', 'password');
define('SMTP_SECURE', 'ssl'); // default gmail conf
define('SMTP_PORT', 465); // default gmail conf

/***********************************************
** PATHS
***********************************************/
/// as the script might be called by the server cron we need to precise the relative paths
define('PATH', __DIR__);
// make sure the script has creating/deleting files permissions on this folder
define('DATA_DIR', __DIR__ . '/data/'); 
// make sure the script has creating/deleting files permissions on this folder
define('DATA_LOG', __DIR__ . '/logs/'); 






