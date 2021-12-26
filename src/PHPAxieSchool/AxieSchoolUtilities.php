<?php
/**
*
* PHPAxieSchool
*
* @see 			https://github.com/n0raban/PHPAxieSchool
* @version 		Check Github page
* @author 		n0raban
* @license   		https://unlicense.org The Unlicense
* @note      		This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY
* @note 		The script/developer are NOT affiliated with Axie Infinity
* @donation		ronin:066149e4e914e33b76d612c93eae03df6d9db91a
*
*/

class AxieSchoolUtilities{

	/**
	* check if a string is json
	*/
	static public function isJson($string) :bool
	{
	   json_decode($string);
	   return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	* return UTC timestamp
	*/
	static public function timestamp_utc() :int
	{
		$date = new DateTime(null, new DateTimeZone('UTC'));
		return $date->getTimestamp();
	}

	/**
	* return UTC timestamp of today at 0:00
	*/
	static public function timestamp_0h_utc() :int
	{
		$date = new DateTime(null, new DateTimeZone('UTC'));
		$time = $date->getTimestamp();
		return mktime(0,0,0, date('m', $time), date('d', $time), date('Y', $time));
	}

	/**
	* return UTC date
	*/
	static public function date_utc($offset_day = false) :string
	{
		$date = new DateTime(null, new DateTimeZone('UTC'));

		// if an offset is given
		if($offset_day)
		{
			$date->modify($offset_day . ' day');
		}

		return $date->format('d-m-Y');
	}

	/**
	* convert slp to currency
	*/
	static public function convert_slp($amount, $to) :int
	{
		$last_prices = AxieSchoolDb::fetchLastPrices();
		$slp_price_in_currency = $last_prices['smooth-love-potion'][$to];
		return round($slp_price_in_currency*$amount);
	}

	/**
	* get the last slp price from db
	*/
	static public function get_slp_price($currency) :string
	{
		$last_prices = AxieSchoolDb::fetchLastPrices();
		$slp_price_in_currency = $last_prices['smooth-love-potion'][$currency];

		return round($slp_price_in_currency, 4);
	}

	/**
	* send an email
	*/
	static public function send_mail($recipient_email, $email_title, $email_body) 
	{
		try 
		{
			AxieSchoolLogs::write("Sending report to " . $recipient_email);

			$mail = new PHPMailer\PHPMailer\PHPMailer(true);
			$mail->CharSet="UTF-8";
			$mail->setFrom(MANAGER_EMAIL, GUILD_NAME);
			$mail->isHTML(true);
			$mail->addAddress($recipient_email, substr($recipient_email, 0, strpos($recipient_email, "@")));
			$mail->Subject = $email_title;
			$mail->Body = $email_body;
			  
			if(USE_SMTP)
			{
				//$mail->SMTPDebug = 2;    
				$mail->isSMTP();
				$mail->Host = SMTP_SERVER;
				$mail->SMTPAuth = TRUE;
				$mail->Username = SMTP_USER;
				$mail->Password = SMTP_PASSWORD;
				$mail->SMTPSecure = SMTP_SECURE; 
				$mail->Port =  SMTP_PORT;
			}

			$mail->send();

			// in case we call this function inside a loop
			sleep(3);
		}
		catch (Exception $e)
		{
			// if we have a problem sending email
			$error_msg = $e->errorMessage();
			AxieSchoolLogs::write("Error while sending email : " . $error_msg);
		}
	}
}
