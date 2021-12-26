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

/**
*
* https://github.com/leemunroe/responsive-html-email-template
* https://htmlemail.io/inline/
*
*/

abstract class AxieSchoolReport extends AxieSchool
{

	/**
	* preset variables
	*/
	protected string $dist_or_src = 'dist';
	protected int $min_daily_slp = MIN_DAILY_SLP;
	protected string $default_currency = DEFAULT_CURRENCY;

	/**
	* variables set at construction
	*/
	protected object $template;
	protected array $all_scholars_last_data;

    	public function __construct()
    	{
       		$this->set_vars();
        	$this->build_report();
    	}

	/**
	* init template class and file
	*/
	protected function init_template() :void
	{
		$this->template = new Template('template');
		$this->template_email_path = $this->path . '/templates/' . $this->dist_or_src . '/email_report.tpl';
	}

	/**
	* built the html report
	*/
	protected function build_report() :void
	{
		$this->init_template();
		$this->parse_report_texts();
		$this->parse_body_main_content();
		$this->parse_in_game();
		$this->parse_expected_for_30_days();
	}

	/**
	* get html report
	*/
	public function get_html_report() :string
	{
		$this->template->set_filenames(array('template' => $this->template_email_path));
		return $this->template->get_html('template');
	}

	/**
	* next claim span for one scholar
	* return :
	* claim is today 
	* or claim is ready (if day of claim is past of more than 1 day)
	* or claim in ... day(s)
	*/
	protected function get_next_claim_one_scholar($ronin) :string
	{
		$next_claim_time = false;
		$next_claim_string = '';

		// if we have data for this scholar
		if(isset($this->all_scholars_last_data[$ronin]['next_claim']))
		{
			/// claim date
			$claim_date = new DateTime(null, new DateTimeZone('UTC'));
			$claim_date->setTimestamp($this->all_scholars_last_data[$ronin]['next_claim']);

			// today's date
			$now_date  = new DateTime(null, new DateTimeZone('UTC'));

			// number of days left until claim
			$diff = $claim_date->diff($now_date);

			/// we need to check if diff is in the past or the future
			/// in the future
			if($diff->invert || $diff->days == 0)
			{
				// claim is ready today
				if($diff->days == 0)
				{
					$next_claim_string = '<span style="color:#038403">Claim is today</span>';
				}
				else
				{ // claim will be ready in ... day(s)
					$s = ($diff->days>1) ? 's' : '';
					$next_claim_string = '<span style="color:#4f4f4f">Claim in ' . $diff->days . ' day' . $s . '</span>';
				}
			}
			else
			{ // claim is ready since more than a day
				$next_claim_string = '<span style="color:#038403">Claim is ready</span>';
			}
		}

		return $next_claim_string;
	}

	/**
	* filter the slp color in green or red 
	* depending on the minimum SLP defined in config
	*/
	protected function color_slp_filter($slp) :string
	{
		if($slp < $this->min_daily_slp)
		{
			return '<span style="color:#ff0909;">' . $slp . '</span>';
		}
		else
		{
			return '<span style="color:#038403;">' . $slp . '</span>';
		}
	}

	/**
	* TR line of one scholar
	* mmr and slp for the day
	* + avg 7 for slp and mmr
	*/
	protected function scholar_data_tr($ronin) :void
	{
		// if we display next_claim
		if($this->show_next_claim)
		{
			$next_claim = $this->get_next_claim_one_scholar($ronin);
		}
		else
		{
			$next_claim = '';
		}

		/// parse the scholars table
		$this->template->assign_block_vars('list_scholars', 
			array
			( 
				'NAME' => (isset($this->name)) ? $this->name : $this->scholars[$ronin]['name'],
				'SLP' => $this->color_slp_filter($this->all_scholars_last_data[$ronin]['gained_today_slp']),
				'MMR' => $this->all_scholars_last_data[$ronin]['mmr'],
				'AVG_SLP_7' => $this->color_slp_filter($this->all_scholars_last_data[$ronin]['averages'][7]['gained_today_slp']),
				'AVG_MMR_7' => $this->all_scholars_last_data[$ronin]['averages'][7]['mmr'],
				'CLAIM' => $next_claim,
			)
		);
	}

	/**
	* amount of slp in game for one scholar
	* return array from scholar_share()
	*/
	protected function scholar_in_game_slp($ronin) :array
	{
		$in_game_slp = 0;
		if(isset($this->all_scholars_last_data[$ronin]['in_game_slp'])){

			$in_game_slp = $this->scholar_share($ronin, $this->all_scholars_last_data[$ronin]['in_game_slp']);
		}

		return $in_game_slp;
	}

	/**
	* take amount and ronin address
	* return shared amount of slp between shcolar and manager
	*/
	protected function scholar_share($ronin, $value) :array
	{
		$total = $value;
		$result = array('total' => $total, 'manager' => '', 'scholar' => '', 'scholar_percent' => '');
		if(isset($this->scholars[$ronin]['share'])){

			//compute scholar cut
			$scholar_share = $this->scholars[$ronin]['share'];
			$scholar_cut = round((($scholar_share * $total) / 100));
			$manager_cut = $total-$scholar_cut;

			$result['scholar_percent'] = $scholar_share;
			$result['scholar'] = $scholar_cut;
			$result['manager'] = $manager_cut;

		}

		return $result;
	}
}

/**
* Methods to build manager report
*/
class AxieSchoolReportToManager extends AxieSchoolReport
{

	protected bool $show_next_claim = true;

	/**
	* set vars
	*/
   	 protected function set_vars() :void
    	{
    		$this->all_scholars_last_data = AxieSchoolDb::fetchAllScholarsLastData();
    	}

	/**
	* parse manager only texts in report
	*/
	protected function parse_report_texts() :void
	{
		$this->template->assign_vars(array( 
			'TITLE' => 'Daily report',
			'SUB_TITLE' => 'Here is your daily report.',
			'NAME' => 'manager',
			'TXT_TOP' => 'Here is your daily report.',
			'TXT_GOOD_BYE' => 'Have a nice day!',
			'CURRENCY'  => $this->default_currency,
			'SLP_PRICE' => AxieSchoolUtilities::get_slp_price($this->default_currency),
			'FOOTER_TXT' => 'If you use this script, please consider making a donation<br/>ronin:066149e4e914e33b76d612c93eae03df6d9db91a<br/>Even a few SLP would mean the world to me! Thanks!',
			// 'FOOTER_TXT' => '',

		));
	}

	/**
	* parse all scholars mmr, slp, and avg 7
	* parse also the total for today, before and after cut
	*/
	protected function parse_body_main_content() :void
	{
		/// foreach scholars
		$all_scholars_total_today = 0;
		$all_scholars_after_cut_today = 0;

		foreach ($this->scholars as $k => $v) 
		{
			// parse tr
			$this->scholar_data_tr($k);

			$scholar_total_today = $this->all_scholars_last_data[$k]['gained_today_slp'];
			$all_scholars_total_today += $scholar_total_today;
			$share_scholar_today = $this->scholar_share($k, $scholar_total_today);
			$all_scholars_after_cut_today += $share_scholar_today['manager'];
		}

		//we  parse total for today
		$this->template->assign_vars(array(
			'TODAY_BEFORE_CUT_SLP' => $all_scholars_total_today,
			'TODAY_AFTER_CUT_SLP' => $all_scholars_after_cut_today,
			'TODAY_BEFORE_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($all_scholars_total_today, $this->default_currency),
			'TODAY_AFTER_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($all_scholars_after_cut_today, $this->default_currency),
		));
	}

	/**
	* parse total of in-game slp, before and after cut
	*/
	protected function parse_in_game() :void
	{
		//calculate total in_game for all scholars
		$all = array();
		$total = 0;
		$manager = 0;
		$scholar = 0;

		foreach ($this->scholars as $k => $v)
		{
			$all[$k] = $this->scholar_in_game_slp($k);
			$total+=$all[$k]['total'];
			$manager+=$all[$k]['manager'];
			$scholar+=$all[$k]['scholar'];
		}

		$before_cut_slp = $total;
		$after_cut_slp = $manager;
		$before_cut_in_currency = AxieSchoolUtilities::convert_slp($before_cut_slp, $this->default_currency);
		$after_cut_in_currency = AxieSchoolUtilities::convert_slp($after_cut_slp, $this->default_currency);

		$this->template->assign_vars(array( 
			'INGAME_BEFORE_CUT_SLP' => $before_cut_slp,
			'INGAME_AFTER_CUT_SLP' => $after_cut_slp,
			'INGAME_BEFORE_CUT_CURRENCY' => $before_cut_in_currency,
			'INGAME_AFTER_CUT_CURRENCY' => $after_cut_in_currency,
		));
	}

	/**
	* parse expected for 30 days for all scholars, before and after cut
	*/
	protected function parse_expected_for_30_days() :void
	{
		$total = 0;
		$after_cut = 0;

		foreach ($this->scholars as $k => $v)
		{
			if(isset($this->all_scholars_last_data[$k]['averages']))
			{
				$expected_scholar_30_days_total = round($this->all_scholars_last_data[$k]['averages']['7']['gained_today_slp']*30);
				$scholar_share_30_days = $this->scholar_share($k, $expected_scholar_30_days_total);
				$expected_scholar_30_days_scholar_cut = $scholar_share_30_days['scholar'];
				$expected_scholar_30_days_manager_cut = $scholar_share_30_days['manager'];
				$total += $expected_scholar_30_days_total;
				$after_cut += $expected_scholar_30_days_manager_cut;
			}
		}

		$this->template->assign_vars(array( 
			'EXPECTED_30_BEFORE_CUT_SLP' => $total,
			'EXPECTED_30_AFTER_CUT_SLP' => $after_cut,
			'EXPECTED_30_BEFORE_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($total, $this->default_currency),
			'EXPECTED_30_AFTER_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($after_cut, $this->default_currency),
		));
	}
}


/**
* Methods to build scholars report
*/
class AxieSchoolReportToScholar extends AxieSchoolReport
{

	/**
	* preset variables by config 
	*/
	protected bool $show_next_claim = SHOLARS_NEXT_CLAIM_DISPLAY;

	/**
	* variables set at construction
	*/
	protected string $scholar_ronin;
	protected string $currency;

	public function __construct($ronin)
	{

		$this->scholar_ronin = $ronin;
		parent::__construct();
	}

	/**
	* set vars
	*/
    	protected function set_vars() :void
    	{
    		$this->all_scholars_last_data = AxieSchoolDb::fetchAllScholarsLastData();
    		$this->scholar_last_data = AxieSchoolDb::fetchOneScholarLastData($this->scholar_ronin);

    		// if report is not set to false for this scholar
    		if($this->scholars[$this->scholar_ronin]['reporting'])
    		{
    			$this->currency = $this->scholars[$this->scholar_ronin]['reporting']['currency'];
    			$this->name = $this->scholars[$this->scholar_ronin]['reporting']['contact_name'];
    		}
    		else
    		{
    			$this->currency = $this->default_currency;
    			$this->name = $this->scholar_last_data['name'];
    		}
    	}

	/**
	* parse scholars only texts in report
	*/
	protected function parse_report_texts() :void
	{
		$this->template->assign_vars(array( 
			'TITLE' => 'Daily report',
			'SUB_TITLE' => 'Here is your daily report.',
			'NAME' => $this->name,
			'TXT_TOP' => 'Here is your daily report.',
			'TXT_GOOD_BYE' => 'Have a nice day!',
			'CURRENCY'  => $this->currency,
			'SLP_PRICE' => AxieSchoolUtilities::get_slp_price($this->currency),
			'FOOTER_TXT' => 'This is a report on your earnings from yesterday, snapshot taken at energy reset time.<br/> 
			The results on this report are based on third party data. <br/>Please contact your manager if you notice any errors.<br/>
			The 30 days expected is based on your 7 days average.',
		));
	}

	/**
	* parse scholar mmr, slp, and avg 7
	* parse also the total for today, before and after cut
	*/
	protected function parse_body_main_content() :void
	{

		// parse tr
		$this->scholar_data_tr($this->scholar_ronin);

		$share_scholar_today = $this->scholar_share($this->scholar_ronin, $this->scholar_last_data['gained_today_slp']);

		//we  parse total for today
		$this->template->assign_vars(array(
			'TODAY_BEFORE_CUT_SLP' => $share_scholar_today['total'],
			'TODAY_AFTER_CUT_SLP' => $share_scholar_today['scholar'],
			'TODAY_BEFORE_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($share_scholar_today['total'], $this->currency),
			'TODAY_AFTER_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($share_scholar_today['scholar'], $this->currency),
		));
	}

	/**
	* parse scholar in-game slp, before and after cut
	*/
	protected function parse_in_game() :void
	{
		
		$in_game = $this->scholar_in_game_slp($this->scholar_ronin);
		$before_cut_slp = $in_game['total'];
		$after_cut_slp = $in_game['scholar'];
		$before_cut_in_currency = AxieSchoolUtilities::convert_slp($before_cut_slp, $this->currency);
		$after_cut_in_currency = AxieSchoolUtilities::convert_slp($after_cut_slp, $this->currency);

		$this->template->assign_vars(array( 
			'INGAME_BEFORE_CUT_SLP' => $before_cut_slp,
			'INGAME_AFTER_CUT_SLP' => $after_cut_slp,
			'INGAME_BEFORE_CUT_CURRENCY' => $before_cut_in_currency,
			'INGAME_AFTER_CUT_CURRENCY' => $after_cut_in_currency,
		));
	}

	/**
	* parse scholar expected for 30 days, before and after cut
	*/
	protected function parse_expected_for_30_days() :void
	{

		// get the 7 days average
		$avg7 = $this->scholar_last_data['averages']['7']['gained_today_slp'];
		$expected_scholar_30_days_total = round($avg7*30);

		$scholar_share_30_days = $this->scholar_share($this->scholar_ronin, $expected_scholar_30_days_total);

		$this->template->assign_vars(array( 
			'EXPECTED_30_BEFORE_CUT_SLP' => $scholar_share_30_days['total'],
			'EXPECTED_30_AFTER_CUT_SLP' => $scholar_share_30_days['scholar'],
			'EXPECTED_30_BEFORE_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($scholar_share_30_days['total'], $this->currency),
			'EXPECTED_30_AFTER_CUT_CURRENCY' => AxieSchoolUtilities::convert_slp($scholar_share_30_days['scholar'], $this->currency),
		));
	}
}
