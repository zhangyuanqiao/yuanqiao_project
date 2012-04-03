<?php
//require_once ('Device.php');
//require_once ('CCommand.php');

/**
 * Just to store all configurations of testing enviroment.
 * @author Yuanqiao Zhang yuanqiao.zhang@hp.com QA
 * @version 1.0
 * @updated 27-Feb-2012 10:18:12 AM
 */
class MTMEnv
{

	/**
	 * The first AP , an object which is get from scenario
	 */
	public static $ap1;
	/**
	 * IP address of the 1st AP
	 */
	public static $ap1_ip;
	public static $ap2;
	/**
	 * IP address of the second AP
	 */
	public static $ap2_ip; 
	/**
	 * We have 4 kind of possible commands : SOAP ,WEB,CLI and SNMP 
	 */
	public static $command_type;
	/**
	 * First controll. an object from the scenario
	 */
	public static $msc1;
	/**
	 * IP address of the first controller
	 */
	public static $msc1_ip;
	/**
	 * Second controll. an object from the scenario
	 */
	public static $msc2;
	/**
	 * IP address of the second controller
	 */
	public static $msc2_ip;
	/**
	 * The number of APs in the scenario
	 */
	public static $number_of_ap;
	/**
	 * The number of controllser in the scenario
	 */
	public static $number_of_msc;
	/**
	 * The serial name of 1st controller
	 */
	public static $serial_of_msc1;
	public $m_CCommand;
	/**
	 * product name of the first msc
	 */
	public static $product_name_of_msc1;
	/**
	 * the state of teaming
	 * Enabled or Disabled
	 */
	public static $teaming_state;
	public static $wcb;
	public static $wcb_ip = "192.168.5.200";
	/**
	 * debug mode. if 1 ,debug is on ,elsewise off
	 */
	public static $debug_mode;

	function __construct()
	{
		echo __METHOD__;
	}

	function __destruct()
	{
		echo __METHOD__;
	}



	public static function show_setting()
	{

		//Print all hardware infos for example,
		//how many controllers ,aps...   
		echo __METHOD__;
        echo "the serial number of controller is  \N";
        echo self::$serial_of_msc1;
        echo "\n";
	}

}
?>