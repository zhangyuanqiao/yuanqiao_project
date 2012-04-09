<?php


/**
 * @author zhayuanq
 * @version 1.0
 * @created 23-Feb-2012 4:13:08 PM
 */
class User
{

	private $bLocalState;
	private $sPassword;
	private $sUsername;
	public static $sMscIP;


	function __destruct()
	{
	}



	/**
	 * 
	 * @param state
	 */
	public function updateAccessControlMode($bState)
	{
        $sSoapCmd = "UpdateUserAccount username=$this->sUsername password=$this->sPassword activeState=Enabled accessControlledState=$bState\n ";
        
        $iRetCode  = SOAP::sendCommand(User::$sMscIP, $sSoapCmd);
        if ($iRetCode != 0)
        {
            return FALSE;
        }
        return TRUE;    
	}

	/**
	 * 
	 * @param vlan_id
	 */
	public function updateEgressVlan($sVlanID)
	{
        $sAccountProfileName= "account_profile_vlan_".$sVlanID;
        User::createAccountProfile($sAccountProfileName);
          
        User::addAttributeToAccountProfile($sAccountProfileName,"Tunnel-Medium-Type","Disabled"," ",65,"Integer",6);
        User::addAttributeToAccountProfile($sAccountProfileName,"Tunnel-Type","Disabled"," ",64,"Integer",13);
        User::addAttributeToAccountProfile($sAccountProfileName,"Tunnel-Private-Group-ID","Disabled"," ",81,"Text",$sVlanID);
        
        $this->addAccountProfileRestriction($sAccountProfileName);
        
        
	}

	/**
	 * 
	 * @param username
	 * @param password
	 * @param local_state
	 */
	public function __construct($sUsername, $sPassword, $bLocalState = "Enabled")
	{
        $this->sUsername=$sUsername;
        $this->sPassword=$sPassword;
        $this->bLocalState="Enabled";
        $sSoapCmd="AddUserAccount  username=$sUsername password=$sPassword activeState=Enabled accessControlledState=Disabled\n";
        SOAP::sendCommand(User::$sMscIP, $sSoapCmd);       
//        $this->create();
	}

	public function create()
	{
        
        $sSoapCmd = "AddUserAccount  username=$this->sUsername password=$this->sPassword activeState=Enabled accessControlledState=Disabled\n ";
        
        $iRetCode           = SOAP::sendCommand(User::$sMscIP, $sSoapCmd);
        if ($iRetCode != 0)
        {
            return FALSE;
        }
        return TRUE;    
	}


	/**
	 * 
	 * @param name    the name of account profile
	 */
	public static function createAccountProfile($sName)
	{
        $sSoapCmd = "AddAccountProfile profileName=$sName\n"
                        ."UpdateAccountProfileAccessControlState profileName=$sName accessControlled=Disabled \n";
        
        $iRetCode           = SOAP::sendCommand(User::$sMscIP, $sSoapCmd);
        if ($iRetCode != 0)
        {
            return FALSE;
        }
        return TRUE;            
	}

	/**
	 * 
	 * @param account_profile_name
	 */
	public function addAccountProfileRestriction($sAccountProfileName)
	{
        $sSoapCmd = "AddUserAccountNonAccessControlledAccountProfileRestriction username=$this->sUsername accountProfileName=$sAccountProfileName \n"
                        ."UpdateUserAccountNonAccessControlledAccountProfileRestriction username=$this->sUsername accountProfileRestrictionState=Enabled \n";
        
        $iRetCode           = SOAP::sendCommand(User::$sMscIP, $sSoapCmd);
        if ($iRetCode != 0)
        {
            return FALSE;
        }
        return TRUE;            
	}

	/**
	 * 
	 * @param profile_name
	 * @param attribute_name
	 * @param vsa_state
	 * @param vendor_id
	 * @param attribute_type
	 * @param attribute_format
	 * @param attribute_value
	 */
	public static function addAttributeToAccountProfile($sAccountProfileName, $sAttributeName, $sVsaState, $sVendorID, $sAttributeType, $sAttributeFormat, $sAttributeValue)
	{
        $sSoapCmd = "AddAccountProfileCustomAttribute profileName=$sAccountProfileName attributeName=$sAttributeName vsaState=$sVsaState vendorId=\"\" attributeType=$sAttributeType attributeFormat=$sAttributeFormat attributeValue=$sAttributeValue\n";
        
        $iRetCode           = SOAP::sendCommand(User::$sMscIP, $sSoapCmd);
        if ($iRetCode != 0)
        {
            return FALSE;
        }
        return TRUE;            
	}

}
?>