<?php
require_once ('MTMEnv.php');   

    /*-------------------------------------------------------------------------------------------------------------------------
     Debugging utilities
     -This section contains code to help your debugging.
      To access interactive debugging in your code, simply add the line:
      eval(__DebugCode__);
      where you want the debugging to take place.
     --------------------------------------------------------------------------------------------------------------------------*/
function readline( $prompt = '' ) {
    echo $prompt;
    return rtrim( fgets( STDIN ), "\n" );
}
// This is the definition of the __DebugCode__ code snippet.
define (__DebugCode__,
    'while (0 < strlen($myLine = readline("\n" . __FILE__ . ":" . __FUNCTION__ . "\nType a PHP command or hit <enter> to resume program > "))) {
     try {
        $result = eval($myLine.";");
     } catch (Exception $e) {
        echo "Exception received: $e->getMessage()";
     }
     echo "\nYour command was: <$myLine>\n";
    }');
    
    



/**
 * @author zhayuanq
 * @version 1.0
 * @updated 23-Feb-2012 1:34:56 PM
 */
class Utility
{
	public static $msc_ip;
	public static $product_name;


	function __construct()
	{
	}

	function __destruct()
	{
	}



	/**
	 * configure the switch
	 * 
	 * @param switch_ip
	 * @param switch_port
	 * @param vlan_id
	 * @param vlan_tagging
	 */
	public static function configSwitch($switch_ip, $switch_port, $vlan_id, $vlan_tagging)
	{
        ;
	}

	/**
	 * 
	 * @param username
	 * @param password
	 */
	public static function userLogin($username, $password) 
	{
        $url="www.google.ca";
        system("g_do_login  MTMEnv::$wcb_ip $username $password $url",$RC1);
        if(RC1==0)
        {
            return TRUE;
        } 
        else
        {
            return FALSE;
        }
	}

    /**
	 * 
	 * @param username
	 */
    public static function userLogout($username)
    {
        return $this->sendSoapCommand("ExecuteUserAccountLogout username=$username");
    }

	/**
	 * 
	 * @param url
	 * @param expected_content
	 */
	public function checkPage($url, $expected_content)
	{
      
      
      $ch = curl_init();
      $timeout = 5;
      curl_setopt($ch,CURLOPT_URL,$url);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
      $data = curl_exec($ch);
      curl_close($ch);
      echo $data;
      if(preg_match("/$expected_content/", $data))
      {
        return TRUE;
      }
      else
      {
        return FALSE;    
      }

	}

	/**
	 * 
	 * @param url
	 */
	public static function getPage($url)
	{
        ;
	}

	public static function postSelenium()
	{
        WEB::logout();
        WEB::closeBrowser(TRUE);
	}

	public static function preSelenium()
	{
        //Start the selenium server and open up the browser 
        WEB::startBrowser(Utility::$msc_ip, Utility::$product_name);
        //Login to the device's home.asp page
        WEB::login();
        $selenium_driver=WEB::getSelenium();
        return $selenium_driver;
	}

	/**
	 * 
	 * @param command_data
	 */
	public static function sendSoapCommand($command_data)
	{
                
        $return_code           = SOAP::sendCommand(Utility::$msc_ip, $command_data);
        if ($return_code != 0)
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
	}

	/**
	 * 
	 * @param device_ip
	 * @param command_data
	 */
	public static function sendSoapCommandX($device_ip, $command_data)
	{
        $return_code           = SOAP::sendCommand($device_ip, $command_data);
        if ($return_code != 0)
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
	}

	/**
	 * soap command: ControlledNetworkUpdateRadioPowerControl(level, entityName,
	 * radioId, productType, powerControlMode, powerdBm, interval) => ()
	 * ControlledNetworkLevel_t level string_t entityNameOn AP level, the entityName
	 * is the AP's MAC address DeviceId_t radioId ControlledNetworkProductType_t
	 * productType PowerControlMode_t powerControlMode string_t powerdBm
	 * RadioInterval_t interval Example:
	 * ControlledNetworkUpdateRadioPowerControl("Group", "Default Group", "Radio_1",
	 * "MSM320", "Manual", "15", "5min"); Set power control for "Radio_1" on MSM320
	 * products for group "Default Group" to 15dBm in manul mode with an interval of
	 * 5min.
	 * 
	 * @param ap_mac
	 * @param ap_type
	 * @param gain
	 */
	public static function apPowerControl($ap_mac, $ap_type, $gain)
	{
        return $this->sendSoapCommand("ControlledNetworkUpdateRadioPowerControl level=AP entityName=$ap_mac radioId=Radio_1  productType=$ap_type powerControlMode=Manual powerdBm=$gain interval=5");                                        
	}

     /**
	 * insert a breakpoint
	 * 
	 * @param info
	 */
    public static function insertBreakPoint($info = null)
    {
        system("banner $info");
        if(MTMEnv::$debug_mode==1)
        eval(__DebugCode__);
    }
	/**
	 * turn debug off
	 */
	public static function turnDebugOff()
	{
        MTMEnv::$debug_mode=0;    
	}

	/**
	 * turn debug on
	 */
	public static function turnDebugOn()
	{
        MTMEnv::$debug_mode=1;  
	}

	/**
	 * highlight something
	 * 
	 * @param data
	 */
	public static function banner($data)
	{
        if(MTMEnv::$debug_mode==1)
        system("banner $data");                 
	}



}


?>