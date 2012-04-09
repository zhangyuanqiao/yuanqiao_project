<?php


/**
 * @author zhayuanq
 * @version 1.0
 * @created 29-Feb-2012 4:13:40 PM
 */
class WirelessProtection
{

	public $key;
	/**
	 * DYNAMIC
	 * STATIC/PRESHARED
	 */
	public $key_source;
	/**
	 * No_Encryption
	 * WEP
	 * TKIP
	 * AES/CCMP
	 * WPA(TKIP)
	 * WPA(AES/CCMP)
	 * WPA/WPA2
	 * AES/3DES
	 * 3DES 
	 */
	public $wireless_protection_type;

	function __construct()
	{
	}

	function __destruct()
	{
	}


	/**
	 * 
	 * @param key
	 */
	public function setKey($key)
	{
        $this->key=$key;
	}

	/**
	 * 
	 * @param key_source
	 */
	public function setKeySource($key_source = "DYNAMIC")
	{
        $this->key_source=  $key_source;
	}

	/**
	 * 
	 * @param wireless_protection_type
	 */
	public function setWirelessProtectionType($wireless_protection_type = "WEP")
	{
        $this->wireless_protection_type= $wireless_protection_type;
	}




	/**
	 * 
	 * @param msc_ip
	 * @param vsc_name
	 * @param auth
	 */
	public function commit($msc_ip, $vsc_name, Authentication $auth)
	{
        /*
        *WEP
        *WPA(TKIP) 
        WPA2(AES/CCMP) 
        WPA/WPA2 
        None  
        */
        $soap_command="";
        $need_8021x_authen="Disabled";
        if($auth->authentication_type=="802.1X")
        {
            $need_8021x_authen="Enabled";  
        }
        
        
        if($this->key_source=="STATIC")
        {
            if($this->wireless_protection_type=="WEP")
            {
                $soap_command = "UpdateVirtualSCWEP vscName=$vsc_name key1=$this->key[1] key2=$this->key[2] key3=$this->key[3] key4=$this->key[4] transmissionKey=$this->key[5] format=$this->key[6] \n";  

            }
            else
            {
//                $soap_command = "UpdateVirtualSCWEP vscName=$vsc_name key1=$this->key[1] key2=$this->key[2] key3=$this->key[3] key4=$this->key[4] transmissionKey=$this->key[5] format=$this->key[6] \n";                                                                                                                                             
                $soap_command = "UpdateVirtualSCPSK vscName=$vsc_name psk=$this->key \n" ;
            }
          
        }
        
      $soap_command =$soap_command."UpdateVirtualSCSecurity vscName=$vsc_name wirelessProtection=$this->wireless_protection_type  authenticationState=$need_8021x_authen activeDirectoryAuthenticationState=Disabled localAuthenticationState=$auth->local_state radiusAuthenticationState=$auth->remote_state radiusAuthenticationServer=$auth->radius_profile radiusAccountingState=$auth->accounting_state radiusAccountingServer=$auth->accounting_radius_profile \n";
     //     $soap_command =$soap_command."UpdateVirtualSCSecurity vscName=$vsc_name wirelessProtection=WEP  authenticationState=$need_8021x_authen activeDirectoryAuthenticationState=Disabled localAuthenticationState=$auth->local_state radiusAuthenticationState=$auth->remote_state radiusAuthenticationServer=$auth->radius_profile radiusAccountingState=$auth->accounting_state radiusAccountingServer=$auth->accounting_radius_profile \n";
        $retCode           = SOAP::sendCommand($msc_ip, $soap_command);
        
        if ($return_code == 1)
        {
            return FALSE;
        }
        return TRUE;
	}   



}
?>