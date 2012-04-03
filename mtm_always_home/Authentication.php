<?php


/**
 * @author zhayuanq
 * @version 1.0
 * @created 29-Feb-2012 4:14:13 PM
 */
class Authentication
{

	/**
	 * 
	 * <ul>
	 * 	<li>MAC </li>
	 * 	<li>802.1x </li>
	 * 	<li>HTML</li>
	 * </ul>
	 */
	public $authentication_type;
	public $local_state = "Enabled";
	public $radius_profile = "picbois";
	public $remote_state = "Disabled";
	public $accounting_radius_profile = "picbois";
	public $accounting_state = "Disabled";
	private $bAccountingState = "Disabled";
	private $bLocalState = "Enabled";
	private $bRadiusProfile = "picbois";
	private $bRemoteState = "Disabled";
	private $sAccountingRadiusProfile = "picbois";
	/**
	 * 
	 * <ul>
	 * 	<li>MAC </li>
	 * 	<li>802.1x </li>
	 * 	<li>HTML</li>
	 * </ul>
	 */
	private $sAuthenticationType;

	function __construct()
	{
	}

	function __destruct()
	{
	}

	                 
	/**
	 * 
	 * @param authentication_type
	 */
	public function setAuthenticationType($authentication_type = "HTML")
	{
        $this->authentication_type=  $authentication_type;
	}

	/**
	 * 
	 * @param enabled_diabled_state
	 */
	public function setLocalState($enabled_diabled_state = "Enabled")
	{
         $this->local_state=$enabled_diabled_state;
	}

	/**
	 * 
	 * @param radius_profile
	 */
	public function setRadiusProfile($radius_profile = "picbois")
	{
        $this->radius_profile= $radius_profile;
	}

	/**
	 * 
	 * @param enabled_diabled_state
	 */
	public function setRemoteState($enabled_diabled_state = "Disabled")
	{
        $this->remote_state= $enabled_diabled_state;
	}

	/**
	 * 
	 * @param msc_ip
	 * @param vsc_name
	 */
	public function commit($msc_ip, $vsc_name)
	{
        
        $soap_command=null;
        switch($this->authentication_type)
        {
            case "HTML" :
            {
                    $soap_command = "UpdateVirtualSCHTMLAuthentication vscName=$vsc_name htmlState=Enabled  localHTMLState=$this->local_state radiusHTMLState=$this->remote_state authenticationRadiusName=$this->radius_profile authenticationTimeout=40 radiusAccountingState=$this->accounting_state accountingRadiusName=$this->accounting_radius_profile activeDirectoryHTMLState=Disabled\n" 
                                    ."UpdateVirtualSCHTMLRedirect vscName=$vsc_name state=Disabled\n";         
            }
            break;

            case "MAC" :
            {
                    $soap_command = "UpdateVirtualSCHTMLAuthentication vscName=$vsc_name htmlState=Disabled localHTMLState=Disabled radiusHTMLState=Disabled authenticationRadiusName=$this->radius_profile authenticationTimeout=40 radiusAccountingState=$this->accounting_state accountingRadiusName=$this->accounting_radius_profile activeDirectoryHTMLState=Disabled\n" 
                                    ."UpdateVirtualSCHTMLRedirect vscName=$vsc_name state=Disabled\n"; 
                    $soap_command.="UpdateVirtualSCMACBasedAuth vscName=$vsc_name state=Enabled localAuthState=$this->local_state radiusAuthState=$this->remote_state authenticationRadiusName=$this->radius_profile radiusAccountingState=$this->accounting_state accountingRadiusName=$this->accounting_radius_profile \n";                                                                                                                     
                                         
            }
            break;

            case "802.1X" :
            {
                    $soap_command = "UpdateVirtualSCHTMLAuthentication vscName=$vsc_name htmlState=Disabled localHTMLState=Disabled radiusHTMLState=Disabled authenticationRadiusName=$this->radius_profile authenticationTimeout=40 radiusAccountingState=$this->accounting_state accountingRadiusName=$this->accounting_radius_profile activeDirectoryHTMLState=Disabled\n" 
                                    ."UpdateVirtualSCHTMLRedirect vscName=$vsc_name state=Disabled\n"; 
            }
                break;

            case "HTML_MAC" :
            {
                    $soap_command = "UpdateVirtualSCHTMLAuthentication vscName=$vsc_name htmlState=Enabled  localHTMLState=$this->local_state radiusHTMLState=$this->remote_state authenticationRadiusName=$this->radius_profile authenticationTimeout=40 radiusAccountingState=$this->accounting_state accountingRadiusName=$this->accounting_radius_profile activeDirectoryHTMLState=Disabled\n" 
                                    ."UpdateVirtualSCHTMLRedirect vscName=$vsc_name state=Disabled\n"; 
                    $soap_command.="UpdateVirtualSCMACBasedAuth vscName=$vsc_name state=Enabled localAuthState=$this->local_state radiusAuthState=$this->remote_state authenticationRadiusName=$this->radius_profile radiusAccountingState=$this->accounting_state accountingRadiusName=$this->accounting_radius_profile \n";                                                                                                                                          
            }
            break;

            default:
                break;
            
        }
        
        $retCode           = SOAP::sendCommand($msc_ip, $soap_command);
        
        if ($return_code == 1)
        {
            return FALSE;
        }
        return TRUE;
	}

	/**
	 * 
	 * @param accounting_radius_profile
	 */
	public function setAccountingRadiusProfile($accounting_radius_profile)
	{
       $this->accounting_radius_profile= $accounting_radius_profile;
	}

	/**
	 * 
	 * @param enabled_disabled_state
	 */
	public function setAccountingState($enabled_disabled_state)
	{
        $this->accounting_state=$enabled_disabled_state;
	}



}
?>