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


class AxieSchoolDb{

	/**
	* insert data in $storename
	*/
	static public function insert($storeName, $data) :?array
	{
		$store = new \SleekDB\Store($storeName, DATA_DIR);
		$results = $store->insert($data);
		return $results;
	}

	/**
	* fetch all data in $storename
	*/
	static public function fetchAll($storeName, $limit) :array
	{
		$store = new \SleekDB\Store($storeName, DATA_DIR);
		return $store->findAll(null, $limit);
	}

	/**
	* fetch data by date in $storename
	*/
	static public function fetchDataByDate($storeName, $date = false) :array
	{
		// if date is not given, set it to today
		if($date == false)
		{
			$date = AxieSchoolUtilities::date_utc();
		}	

		$store = new \SleekDB\Store($storeName, DATA_DIR);
		$result = $store->findBy(["date", "=", $date]);

		if(isset($result[0]))
		{
			return $result[0];
		}
		else
		{
			return $result;
		}
	}

	/**
	* update data by id in $storename
	*/
	static public function updateById($storeName, $id, $data) :array
	{
		$store = new \SleekDB\Store($storeName, DATA_DIR);
		return $store->updateById($id, $data);
	}

	/**
	* fetch last data for one scholar
	*/
	static public function fetchOneScholarLastData($ronin) :array
	{
		$result = AxieSchoolDb::fetchAllScholarsLastData();
		if(isset($result[$ronin]))
		{
			return $result[$ronin];
		}
		else
		{
			return array();
		}
	}

	/**
	* fetch all scholars last data
	*/
	static public function fetchAllScholarsLastData() :array
	{
		$store = new \SleekDB\Store(SCHOLARS_STORE_NAME, DATA_DIR);
		$last_id = AxieSchoolDb::last_id(SCHOLARS_STORE_NAME);
		$result = $store->findBy(["_id", "=", $last_id]);

		if(isset($result[0]))
		{
			return $result[0];
		}
		else
		{
			return $result;
		}
	}

	/**
	* fetch last inserted id in $storeName
	*/
	static public function last_id($storeName) :int
	{

		$store = new \SleekDB\Store($storeName, DATA_DIR);
		$result = $store->findAll(["_id" => "desc"], 1);

		if(isset($result[0]['_id']))
		{
			return $result[0]['_id'];
		}
		else
		{
			return false;
		}

	}

	/**
	* fetch last prices data
	*/
	static public function fetchLastPrices() :?array
	{
		$store = new \SleekDB\Store(PRICES_STORE_NAME, DATA_DIR);	
		$last_price_id = AxieSchoolDb::last_id(PRICES_STORE_NAME);

		if($last_price_id)
		{
			$last_prices = $store->findById($last_price_id);
			return $last_prices;
		}
		else
		{
			return null;
		}
	}
}












