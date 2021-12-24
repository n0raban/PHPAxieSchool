<?php
/**
*
* PHPAxieSchool
*
* @see 			https://github.com/n0raban/PHPAxieSchool
* @version 		Check Github page
* @author 		n0raban
* @license   		https://unlicense.org The Unlicense
* @note      		This program is distributed in the hope that it will be useful - WITHOUT
* @note			ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* @note 		The script and its developer are NOT affiliated with Axie Infinity
* @donation		ronin:066149e4e914e33b76d612c93eae03df6d9db91a
*
*/

abstract class AxieSchoolFetchData extends AxieSchool
{
	/**
	* Add/update data to the db
	*/
	protected function update() :?array
	{
		$data = $this->fetch_data();

		if($data!=null)
		{
			// process data before inserting in db
			$data = $this->process_data($data);

			$date = AxieSchoolUtilities::date_utc();
			$time = AxieSchoolUtilities::timestamp_utc();
			$result = AxieSchoolDb::fetchDataByDate($this->store_name, $date);

			// if we already have data for today
			// update
			if($result)
			{
				AxieSchoolLogs::write('"' . $this->store_name . '" : we already have data for today');

				$id = $result['_id'];
				$data = $result;
				// remove _id
				unset($data['_id']);
				// update timestamp
				$data['last_update'] = $time;

				$result = AxieSchoolDb::updateById($this->store_name, $id, $data);
				AxieSchoolLogs::write('"' . $this->store_name . '" ' . $date . ' updated');
				return $result;

			// if we don't have yet data for today
			// insert
			}
			else
			{
				$data['last_update'] = $time;
				$data['date'] = $date; 
				$result = AxieSchoolDb::insert($this->store_name, $data);
				AxieSchoolLogs::write('"' . $this->store_name . '" ' . $date . ' added');
				return $result;
			}
		}
		else
		{
			AxieSchoolLogs::write('error : could not update "' . $this->store_name . '" (data is null)');
			return null;
		}
	}

	/**
	* Fetch data 
	* Get json content from a url 
	* Will make several attempts until the result correspond to what is expected or until max_attempts is reached
	* return an array of the fetched data
	* or return null if fails
	*/
	protected function fetch_data() :?array
	{
		// init vars
		$attempt = 1;
		$check_result = false;
		$json_result = false;

		// until we did not get the expected result
		// or max_attempt is reached
		do { 

			// write the attempt to logs
		  	AxieSchoolLogs::write('fetching ' . $this->query_url . ' : attempt ' . $attempt . '/' . $this->fetch_data_max_attempt);

			// api call
			$json_result = @file_get_contents($this->query_url);
			$check_result = $this->check_result($json_result);

			// if result is wrong
			if(!$check_result)
			{
				$attempt ++;
				sleep($this->fetch_data_sleep_between);
			}

			// if max_attempt is reached
			if($attempt > $this->fetch_data_max_attempt)
			{
				AxieSchoolLogs::write('error : failed to fetch data from ' . $this->query_url . ' after ' . $this->fetch_data_max_attempt . ' attempts');
				return null;
				break;
			}

		} while(!$check_result);

		AxieSchoolLogs::write('response : ' . $json_result);

		return json_decode($json_result, true);
	}

	/**
	* check if the result from the API is what we expect to be
	*/
	private function check_result($result) :bool
	{
		// result is empty
		if(!$result)
		{
			AxieSchoolLogs::write('error : empty result');
			return false;
		}
		else
		{
			// check if result is json
			if(AxieSchoolUtilities::isJson($result))
			{
				//convert json to array
				$result_array = json_decode($result, true);

				// deeper check depending on which API we are dealing with
				if($this->check_result_array($result_array))
				{
					return true;
				}
				else
				{
					AxieSchoolLogs::write('error : unexpected result');
					return false;
				}

			// result is not Json 
			}
			else
			{
				AxieSchoolLogs::write('error : result is not Json');
				return false;
			}
		}
	}
}

/**
* fetch scholar history data from API
* NOTE : API server is caching the data for 3 hours
*/
class AxieSchoolFetchDataScholars extends AxieSchoolFetchData
{
	/**
	* preset variables by config 
	*/
	private string $api_url = AX_API_URL;
	protected int $fetch_data_sleep_between = AX_API_FETCH_SC_DATA_SLEEP_BETWEEN;
	protected int $fetch_data_max_attempt = AX_API_FETCH_SC_DATA_MAX_ATTEMPT;
	protected string $store_name = SCHOLARS_STORE_NAME;

	/**
	* variables set at construction
	*/
	protected string $query_url;

	public function __construct()
	{
		$this->set_query_url();
	}

	/**
	* Set query url
	*/
	private function set_query_url() :void
	{
		$this->query_url = $this->build_query();
	}

	/**
	* Check on array keys and values if we can consider the result to be good or not
	*/
	protected function check_result_array($result_array) :bool
	{
		$expected_result_count = 0;
		foreach ($result_array as $k => $v)
		{
			if($result_array[$k]['success'] == 1)
			{
				$expected_result_count++;
			}
		}

		if($expected_result_count == count(SCHOLARS))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Process data before inserting in db
	*/
	protected function process_data($data)
	{
		//format cache_last_updated from microtime to humain readeable date and time
		$data = $this->format_cache_last_updated($data);

		// compute gained_today_slp (gained since yesterday)
		$data = $this->compute_scholars_gained_today_slp($data);

		// calculate avg 7 on slp, mmr, rank
		// calculate avg 15 on slp, mmr, rank
		// calculate avg 30 on slp, mmr, rank
		$on_what_array = ['gained_today_slp', 'mmr', 'rank'];
		$days_offset_array = [7, 15, 30];
		
		foreach ($on_what_array as $k => $v) 
		{
			foreach ($days_offset_array as $k2 => $v2) 
			{
				$data = $this->compute_scholars_averages($data, $v, $v2);
			}
		}

		return $data;
	}

	/**
	* caclulate the average of either mmr, rank, slp
	* based on $days_offset duration 
	*/
	private function compute_scholars_averages($data, $on_what, $days_offset)
	{
		///// build an array with the last $days_offset days 
		// today
		$date = AxieSchoolUtilities::date_utc();
		$history[$date] = $data;
		for ($i=1; $i <$days_offset ; $i++) 
		{ 
			$date = AxieSchoolUtilities::date_utc('-' . $i);
			$history[$date] = AxieSchoolDb::fetchDataByDate($this->store_name, $date);
		}

		// reverse it form oldest to newest
		$history = array_reverse($history, true);

		// build an array of the wanted value by date for each scholar
		$history_scholar = array();
		foreach ($history as $date => $scholar_data) 
		{
			// foreach scholar on history data
			foreach ($history[$date] as $k => $v) 
			{
				if(isset($history[$date][$k][$on_what]))
				{
					$history_scholar[$k][$date] = $history[$date][$k][$on_what];
				}
			}
		}

		/// caclulate average on data we have foreach scholar
		foreach ($history_scholar as $k => $v) {
			
			//foreach date of history we have
			$sum = 0;
			$based_on = 0;
			foreach ($history_scholar[$k] as $k2 => $v2) {
				
				$sum += $v2;
				$based_on++;
			}

			if($based_on)
			{
				$data[$k]['averages'][$days_offset]['based_on'] = $based_on;
				$data[$k]['averages'][$days_offset][$on_what] = round($sum/$based_on);
			}
			else
			{
				$data[$k]['averages'][$days_offset]['based_on'] = false;
				$data[$k]['averages'][$days_offset][$on_what] = false;
			}
		}

		// &#128517
		return $data;
	}

	/**
	* caclulate slp earnead between 2 days
	* formula :
	* (today lifetime_slp + today total_slp) - (yesterday lifetime_slp + yesterday total_slp)
	* the result will be correct even if there is a claim on SLP 
	*/
	private function compute_scholars_gained_today_slp($data)
	{
		$result = $data;
		$yesterday = AxieSchoolUtilities::date_utc('-1');
		$data_yesterday = AxieSchoolDb::fetchDataByDate($this->store_name, $yesterday);

		// foreach scholars
		foreach ($data as $k => $v)
		{
			$scholar_address = $k;

			// if there is data yesterday for this scholar
			if(isset($data_yesterday[$k]['lifetime_slp']))
			{
				// we take total of yesterday lifetime_slp and total_slp 
				$total_yesterday = $data_yesterday[$k]['lifetime_slp'] + $data_yesterday[$k]['total_slp'];
				// and total of today lifetime_slp and total_slp
				$total_today = $data[$k]['lifetime_slp'] + $data[$k]['total_slp'];

				//diff = today - yesterday
				$diff = $total_today - $total_yesterday;
				$result[$k]['gained_today_slp'] = $diff;
			}
			else
			{
				$result[$k]['gained_today_slp'] = false;
			}
		}

		return $result;
	}

	/**
	* format cache_last_updated from microtime to humain readeable date and time
	* check if cache_last_updated is at energy time
	* send a warning in the logs if the time of cache_last_updated is not on energy reset time
	*/
	private function format_cache_last_updated($data) :array
	{
		$result = $data;
		foreach ($data as $k => $v)
		{
			$scholar_name = $this->scholars[$k]['name'];
			$cache_last_updated = substr($data[$k]['cache_last_updated'], 0, -3);

			$time = new DateTime();
			$time->setTimestamp($cache_last_updated)->setTimezone(new DateTimeZone('UTC'));
			$date_utc = $time->format('d-m-Y H:i');
			$hour_utc = $time->format('H');

			if($hour_utc !== $this->hour_reset_energy_utc)
			{
				AxieSchoolLogs::write(
					'warning : there might be an error calculating gained_today_slp for scholar ' . $scholar_name . ' today, as the cache_last_updated is not on energy reset time but at ' 
					. $date_utc . ' UTC. The API returns cached data of 3 hours. Maybe someone else fetched the api url for this scholar before the script did)');
			}

			$result[$k]['cache_last_updated'] = $date_utc;
		}

		return $result;
	}

	/**
	* Build the query url for the API
	*/
	private function build_query() :string
	{

		$addresses = implode(',', array_keys(SCHOLARS));
		$query = $this->api_url . '' . $addresses;

		return $query;
	}
}

/**
* fetch prices from API
*/
class AxieSchoolFetchDataPrices extends AxieSchoolFetchData
{
	/**
	* preset variables by config 
	*/
	private array $coins = COINGECKO_COINS;
	private string $defaultCurrency = DEFAULT_CURRENCY;	
	private string $api_url = COINGECKO_API_URL;
	protected int $fetch_data_sleep_between = COINGECKO_API_FETCH_PRICES_SLEEP_BETWEEN;
	protected int $fetch_data_max_attempt = COINGECKO_API_FETCH_PRICES_MAX_ATTEMPT;
	protected string $store_name = PRICES_STORE_NAME;

	/**
	* variables set at construction
	*/
	protected string $query_url;

	public function __construct()
	{
		$this->set_currencies();
		$this->set_query_url();
	}

	/**
	* Set query url
	*/
	private function set_query_url() :void
	{
		$this->query_url = $this->build_query();
	}

	/**
	* Check on array keys and values if we can consider the result to be good or not
	*/
	protected function check_result_array($result_array) :bool
	{
		$coins_keys = array_keys($this->coins);
		$expected_key = $this->coins[$coins_keys[0]];
		if(isset($result_array[$expected_key]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Process data before inserting in db
	*/
	protected function process_data($data)
	{
		// no processing (yet) for prices
		return $data;
	}

	/**
	* Set the all_currencies variable
	* Get every prefered currencies from scholars
	* Add the default currencie
	*/
	private function set_currencies() :void
	{
		// get all currencies used by scholars
		$all_currencies = $this->extract_scholars_prefered_currencies();
		// add the default currency
		$all_currencies[] = $this->defaultCurrency;

		$this->all_currencies = array_unique($all_currencies);
	}

	/**
	* Build the query url for the prices API
	*/
	private function build_query() :string
	{

		$ids = implode(',', $this->coins);
		$vs_currencies = implode(',', $this->all_currencies);
		$query = $this->api_url . 'simple/price?ids=' . $ids . '&vs_currencies=' . $vs_currencies .'&include_24hr_change=true';

		return $query;
	}

	/**
	* extract every currencies defined in the scholars array config
	* return an array with every currencies used by each scholars
	*/
	private function extract_scholars_prefered_currencies() :array
	{
		$scholars_currencies = array();
		foreach ($this->scholars as $k => $v) 
		{
			if(isset($this->scholars[$k]['reporting']))
			{
				if($this->scholars[$k]['reporting'])
				{
					$scholars_currencies[] = $this->scholars[$k]['reporting']['currency'];
				}
			}
		}

		return array_unique($scholars_currencies);
	}
}
