<?php
require_once ('MTMTest.php');

/**
 * Verify that any VLAN interface can be used on the controller to egress traffic.
 * For each of the steps below, associate vlanuser1001 to msm10-1 after changing
 * the VLAN definition on the controller.
 * Steps:
 * 1.	Assign VLAN 1001 to the LAN port of the MSC with no IP address
 * 2.	Assign VLAN 1001 to the LAN port of the MSC with a static IP address
 * 3.	Assign VLAN 1001 to the LAN port of the MSC with a DHCP address
 * 4.	Assign VLAN 1001 to the Internet port of the MSC with no IP address
 * 5.	Assign VLAN 1001 to the Internet port of the MSC with a static IP address
 * 6.	Assign VLAN 1001 to the Internet port of the MSC with a DHCP address
 * 
 * Pass/Fail criteria:
 * -	Test passes if traffic is egressed out the correct port on the controller.
 * 
 * Test results interpretation: Major
 * @author zhayuanq
 * @version 1.0
 * @created 29-Mar-2012 11:50:17 AM
 */
class Termination_using_any_interface_on_the_ccontroller extends MTMTest
{



	/**
	 * Clean up the particular setting for next test run
	 */
	public function Cleanup()
	{
	}

	/**
	 * Main entry for test:30:100

	 */
	public function MyExecute()
	{
		$this->preTest();
        /*
        *Create a NP 
        */
        $sTestNpName="testNP";
        $sTestUser="mtmUser";
        /*
        *create networkprofile vlan25## 
        */ 
        
		$sSetupID=$this->framework->GetTestbed()->GetName();
		$iVlanID="25".substr($sSetupID,5,2);
		$sTestSsid="MTM_VLAN_23".substr($sSetupID,5,2);
		$this->addNetworkProfile($sTestNpName, "Enabled", $iVlanID);
  
        $this->addVlan($sTestNpName, "LAN_Port", "DHCP_IP", "1.1.1.1", "1.1.1.1", "1.1.1.1", "Enabled");   
        //Create a user that will egress to that network (25##)
        $oUser=new User($sTestUser,$sTestUser,"Enabled");
        $oUser->updateEgressVlan($sTestNpName);       
        $this->addLocalNetwork($this->scenario->GetDevice('AP1'),$sTestNpName);
        $this->addLocalNetwork($this->scenario->GetDevice('AP2'),$sTestNpName);
        /*
        *Set a configuration array 
        */  
            
        $aVlanConfiguration=array(
        array("port"=>"LAN_Port","assignMode"=>"No_IP","ipAddress"=>"192.168.1.100","ipMask"=>"255.255.255.0","ipGateway"=>"192.168.1.254","natState"=>"Enabled"),
        array("port"=>"LAN_Port","assignMode"=>"IP_Static","ipAddress"=>"192,168,1,100","ipMask"=>"255,255,255,0","ipGateway"=>"192.168.1.254","natState"=>"Enabled"),
        array("port"=>"LAN_Port","assignMode"=>"DHCP_IP","ipAddress"=>"192.168.1.100","ipMask"=>"255.255.255.0","ipGateway"=>"192.168.1.254","natState"=>"Enabled")
        );
        
        $this->syncAPs();
        /*
        *For each vlan setting, update vlan adn check egress traffic 
        */
        
        foreach($aVlanConfiguration as $key=>$vlanConfig)
        {
            Step::start("Update vlan ");
            
            $this->updateVlan("testNP",$vlanConfig["port"],$vlanConfig["assignMode"],$vlanConfig["ipAddress"],$vlanConfig["ipMask"],$vlanConfig["ipGateway"],$vlanConfig["natState"]) ;
            $this->wcbAssociateAndAuth("wpa2_dynamic",$sTestUser,$sTestUser,$sTestSsid,"PEAPVER0");
            $bRC=$this->verifyTrafficEgressOnRightPort($iVlanID);
            if ($bRC == FALSE) 
            {
                Step::error("traffic is NOT egressed out the correct port on the controller");
                return FAIL;
            } 
            else 
            {
                Step::ok("traffic is egressed out the correct port on the controller");
            }
            
        }

        $this->addVlan($sTestNpName, "Internet_Port", "DHCP_IP", "1.1.1.1", "1.1.1.1", "1.1.1.1", "Enabled");   
        
        $aVlanConfiguration=array(
        array("port"=>"Internet_Port","assignMode"=>"No_IP","ipAddress"=>"172.16.0.100","ipMask"=>"255.255.255.0","ipGateway"=>"172.16.0.1","natState"=>"Enabled"),
        array("port"=>"Internet_Port","assignMode"=>"IP_Static","ipAddress"=>"172.16.0.2","ipMask"=>"255,255,255,0","ipGateway"=>"172.16.0.254","natState"=>"Enabled"),
        array("port"=>"Internet_Port","assignMode"=>"DHCP_IP","ipAddress"=>"172.16.0.100","ipMask"=>"255.255.255.0","ipGateway"=>"172.16.0.1","natState"=>"Enabled")
        );
        
        
        /*
        *For each vlan setting, update vlan adn check egress traffic 
        */
        
        foreach($aVlanConfiguration as $key=>$vlanConfig)
        {
            Step::start("Update vlan ");
            
            $this->updateVlan("testNP",$vlanConfig["port"],$vlanConfig["assignMode"],$vlanConfig["ipAddress"],$vlanConfig["ipMask"],$vlanConfig["ipGateway"],$vlanConfig["natState"]) ;
            $this->wcbAssociateAndAuth("wpa2_dynamic",$sTestUser,$sTestUser,$sTestSsid,"PEAPVER0"); 
            $bRetCode=$this->verifyTrafficEgressOnRightPort($iVlanID);
            if ($bRetCode == FALSE) 
            {
                Step::error("traffic is NOT egressed out the correct port on the controller");
                return FAIL;
            } 
            else 
            {
                Step::ok("traffic is egressed out the correct port on the controller");
            }
            
        }        
        return PASS;
	}

}
?>
