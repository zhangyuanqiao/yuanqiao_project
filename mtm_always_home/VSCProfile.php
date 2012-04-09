<?php

require_once ('WirelessProtection.php');
require_once ('Authentication.php');

/**
 * @author Yuanqiao Zhang yuanqiao.zhang@hp.com QA
 * @version 1.0
 * @created 15-Feb-2012 10:18:38 AM
 */
class VSCProfile {

    /**
     * multiple authentications way exist for one VSC
     */
    private $aoAuthentications;

    /**
     * the IP address of device where I can create VSC. 
     */
    public  $radius_profile;
    private $bAccessControlState = "Enabled";
    private $bAuthenticationState = "Enabled";
    private $oWirelessProtection;
    private $sName;
    private $sSsid;
	public static $sMscIP;
	/**
	 * multiple authentications way exist for one VSC
	 */
	public $authentications;
	/**
	 * just one kind wireless protection exists for one VSC
	 */
	public $wireless_protection;

    /**
	 * @param wireless_protection_type
	 * @param authentication_type
	 * 
	 * @param name    the name of VSC
	 * @param ssid    the ssid of network
	 * @param wireless_protection_type
	 * @param authentication_type
	 */
    public function __construct($name, $ssid, $wireless_protection_type = "WEP", $authentication_type = "HTML") {
        $this->oWirelessProtection = null;
        $this->aoAuthentications = array();

        $this->sName = $name;
        $this->sSsid = $ssid;
        //Creat wireless protection
        if ($sWirelessProtectionType != "NONE") {
            $this->oWirelessProtection = new WirelessProtection();
            $this->oWirelessProtection->setWirelessProtectionType($sWirelessProtectionType);
        }


        //Create authentication       
        $oAuthentication = new Authentication();
        $oAuthentication->setAuthenticationType($sAuthenticationType);
        $oAuthentication->setRadiusProfile($this->radius_profile);
        array_push($this->aoAuthentications, $oAuthentication);
    }
    
    public function create4MTM()
    {
        $sVscName=$this->sName;
        $ssid= $this->sSsid;
                
        $soap_command = "AddVirtualSC vscName=$sVscName\n" 
                         . "UpdateVirtualSCAccessControl vscName=$sVscName state=Disabled\n" 
                         . "UpdateVirtualSCUseForAuthentication vscName=$sVscName state=Enabled\n" 
                         . "UpdateVirtualSCBroadcast vscName=$sVscName state=Enabled\n" 
                         . "UpdateVirtualSCSSID vscName=$sVscName ssid=$ssid\n" 
                         . "UpdateVirtualSCHTMLAuthentication vscName=$sVscName htmlState=Disabled localHTMLState=Disabled radiusHTMLState=Disabled authenticationRadiusName=picbois authenticationTimeout=40 radiusAccountingState=Disabled accountingRadiusName=picbois activeDirectoryHTMLState=Disabled\n" 
                         . "UpdateVirtualSCHTMLRedirect vscName=$sVscName state=Disabled\n" 
                         . "UpdateVirtualSCSecurity vscName=$sVscName wirelessProtection=WPA2(AES/CCMP)  authenticationState=Enabled activeDirectoryAuthenticationState=Disabled localAuthenticationState=Enabled radiusAuthenticationState=Disabled radiusAuthenticationServer=\"\" radiusAccountingState=Disabled radiusAccountingServer=\"\"\n";
        $iRetCode           = SOAP::sendCommand(VSCProfile::$sMscIP, $soap_command);
        
        if ($iRetCode == 1)
        {
            return FALSE;
        }
        return TRUE;
    }


    /**
	 * 
	 * @param state
	 */
    public function updateL3MobilityState($state) {
        $sSoapCmd = "UpdateVirtualSCL3MobilityState vscName=$this->sName state=$state";

        $sRetCode = SOAP::sendCommand(VSCProfile::$sMscIP, $sSoapCmd);

        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 
     * @param name
     */
    public static function deleteVSC($name) {
        $sSoapCmd = "DeleteVirtualSC vscName=$name";

        $sRetCode = SOAP::sendCommand(VSCProfile::$sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 
     * @param device_ip
     */
    public static function setDevice($device_ip) {
        $this->msc_ip = $device_ip;
    }

    /**
	 * 
	 * @param authentication
	 */
    public function addAuthentication($authentication) {
         array_push($this->aoAuthentications, $authentication);
    }

    /**
	 * @param wireless_pretection
	 * 
	 * @param wireless_pretection
	 */
    public function configureWirelessProtection($wireless_pretection) {
        $this->oWirelessProtection = $oWirelessProtection;
    }

	/*
	 * Update fallback method
	 *
	*/
	public function updateFallbackMethod($sFallbackMethod)
	{
		if($sFallbackMethod != "Assume_Home" && $sFallbackMethod != "None"){
			Logger::ToConsoleAndFile("wrong method.just support Assume_Home and None");	
			return FALSE;
		}

		$sSoapCmd = "UpdateVirtualSCL3Mobility vscName=$this->sName state=Enabled homeNetworkSelectionMethod=VLAN_Based homeNetworkSelectionFallbackMethod=$sFallbackMethod";
        $sRetCode = SOAP::sendCommand(VSCProfile::$sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
	}
	
	
    public function commit() {
        // create VSC
        $sSoapCmd = "AddVirtualSC vscName=$this->sName \n"
                . "UpdateVirtualSCUseForAuthentication vscName=$this->sName state=Enabled\n"
                . "UpdateVirtualSCBroadcast vscName=$this->sName state=Enabled\n"
                . "UpdateVirtualSCSSID vscName=$this->sName ssid=$this->sSsid\n";
        // set access control state
        $sSoapCmd.= "UpdateVirtualSCAccessControl vscName=$this->sName state=$this->bAccessControlState \n";
        // set authentication state
        $sSoapCmd.= "UpdateVirtualSCUseForAuthentication vscName=$this->sName state=$this->bAuthenticationState \n";
        SOAP::sendCommand(VSC::$msc_ip, $sSoapCmd);

        // setup authentication 
        foreach ($this->aoAuthentications as $key => $auth) {
            $auth->setRadiusProfile();
            $auth->commit(VSCProfile::$sMscIP, $this->sName);

            // setup wireless protection
            if ($this->oWirelessProtection != null) {
                $this->oWirelessProtection->commit(VSC::$msc_ip, $this->sName, $auth);
            }
        }
    }


    /**
	 * @param enabled_disabled_state
	 * 
	 * @param enabled_disabled_state
	 */
    public function updateAccessControlState($enabled_disabled_state) {
        $this->bAccessControlState = $bEnabledDisabledState;
    }

    /**
	 * @param enabled_diabled_state
	 * 
	 * @param enabled_diabled_state
	 */
    public function updateAuthenticationState($enabled_diabled_state) {
        $this->bAuthenticationState = $bEnabledDisabledState;
    }

    public function getVscName() {
        return $this->sName;
        
    }

    public function getWirelessProtection() {
        
    }

}

?>