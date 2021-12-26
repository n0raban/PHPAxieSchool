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

class AxieSchool
{

	/**
	* preset variables
	*/
	protected array $scholars = SCHOLARS;
	protected string $path = PATH;
	protected string $manager_email = MANAGER_EMAIL;
	protected int $hour_reset_energy_utc = 0;
	protected string $email_report_title = EMAIL_REPORTS_TITLE;
	protected string $scholars_store_name = SCHOLARS_STORE_NAME;
	protected string $prices_store_name = PRICES_STORE_NAME;

	public function __construct()
	{
		$this->load_AxieSchool();
		$this->first_time_run();
	}

	/**
	* load needed files
	*/
	protected function load_AxieSchool() :void
	{
		require_once __DIR__ . "/AxieSchoolDb.php";
		require_once __DIR__ . "/AxieSchoolLogs.php";
		require_once __DIR__ . "/AxieSchoolUtilities.php";
		require_once __DIR__ . "/AxieSchoolFetchData.php";
		require_once __DIR__ . "/AxieSchoolReport.php";
	}

	/**
	* First time run, populate scholars and prices data
	*/ 
	public function first_time_run() :void
	{
		// if there is no data scholars storage yet, update
		if(!AxieSchoolDb::last_id($this->scholars_store_name)) $this->update_scholars();
		// if there is no data scholars storage yet, update
		if(!AxieSchoolDb::last_id($this->prices_store_name)) $this->update_prices();
	}

	/**
	* update the prices
	*/
	public function update_prices() :array
	{
		$AxieSchoolFetchDataPrices = new AxieSchoolFetchDataPrices();
		return $update_result = $AxieSchoolFetchDataPrices->update();
	}

	/**
	* update scholars
	*/
	public function update_scholars() :array
	{
		$AxieSchoolFetchDataScholars = new AxieSchoolFetchDataScholars();
		return $update_result = $AxieSchoolFetchDataScholars->update();
	}

	/**
	* return html string of one scholar report 
	*/
	public function html_scholar_report($ronin_address) :string
	{
		$AxieSchoolReportToScholar = new AxieSchoolReportToScholar($ronin_address);
		return $AxieSchoolReportToScholar->get_html_report();
	}

	/**
	* return html string of manager report
	*/
	public function html_manager_report() :string
	{
		$AxieSchoolReportToManager = new AxieSchoolReportToManager();
		return $AxieSchoolReportToManager->get_html_report();
	}

	/**
	* send report to manager
	*/
	public function send_report_to_manager() :void
	{
		$manager_html_report = $this->html_manager_report();
		AxieSchoolUtilities::send_mail($this->manager_email, $this->email_report_title, $manager_html_report);
	}

	/**
	* send reports to all scholars if they have e-mail address in config
	*/
	public function send_reports_to_scholars() :void
	{
		// for each scholar
		foreach ($this->scholars as $k => $v) 
		{
			// check if we have an email address
			if(isset($this->scholars[$k]['reporting']['contact_email']))
			{
				// set the scholar email
				$recipient_email = $this->scholars[$k]['reporting']['contact_email'];

				// build html report
				$scholar_html_report = $this->html_scholar_report($k);

				// send the report
				AxieSchoolUtilities::send_mail($recipient_email, $this->email_report_title, $scholar_html_report);
			}
		}
	}

	/**
	* method to call from the cron at energy reset time
	* update scholars
	* update prices
	* send reports
	*/
	public function cron() :void
	{
		// check if it is reset energy time
		$date = new DateTime(null, new DateTimeZone('UTC'));
		$hour = $date->format('H');

		// if it is not energy reset time, write a warning inside the logs
		if(!$hour == $this->hour_reset_energy_utc)
		{
			AxieSchoolLogs::write('warning : cron started outside energy reset hour.');
		}

		// carry on

		// update prices
		$this->update_prices();

		// update scholars
		$this->update_scholars();

		// send manager report
		$this->send_report_to_manager();

		// send scolars reports
		$this->send_reports_to_scholars();
	}
}
