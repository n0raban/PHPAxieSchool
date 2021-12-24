<?php
/**
*
* PHPAxieSchool
*
* @see 			https://github.com/n0raban/PHPAxieSchool
* @version 		Check Github page
* @author 		n0raban
* @license   	https://unlicense.org The Unlicense
* @note      	This program is distributed in the hope that it will be useful - WITHOUT
* @note			ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* @note 		The script and its developer are NOT affiliated with Axie Infinity
* @donation		ronin:066149e4e914e33b76d612c93eae03df6d9db91a
*
*/

class AxieSchoolLogs
{
	/**
	* Write to logs
	*/
	static public function write($txt) :void
	{
		// get an UTC date object
		$dateUTC = new DateTime(null, new DateTimeZone('UTC'));
		$time = $dateUTC->getTimestamp();

		// set global date variable
		$date = $dateUTC->format('d-m-Y');

		file_put_contents( DATA_LOG . '/' . $date . '.txt',  $dateUTC->format('H:i:s') . ' | ' . $txt . PHP_EOL, FILE_APPEND);
	}
}























