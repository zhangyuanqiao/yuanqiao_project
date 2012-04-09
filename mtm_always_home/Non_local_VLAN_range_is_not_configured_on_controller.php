<?php
require_once ('MTMTest.php');

/**
 * Description: Test Objective:
 * Verify that when using E-HNS, the case is handled when a VSC has an egress
 * network range defined in the binding that is not configured as a local network
 * and that one or more of the VLANs is not defined locally on the controller.
 * 
 * Note(Anwar): Defined locally on the controller means bind to a local port on
 * the controller
 * 
 * 
 * 
 * Steps:
 * 1.	Create VLAN interfaces 2001 and 2002 on msm7xx-1
 * 2.	Configure VSC1 on msm10-1 to have an egress network binding of range WLAN1
 * (VLANs 2001-2003)
 * 3.	Do not configure WLAN1 as a home network on the Network Assignment page
 * Pass/Fail criteria:
 * 1.	Traffic is blocked.  (Check Status/Mobility page: "Blocked: Home network
 * unknown")
 * 
 * Test results interpretation: Major
 * @author yuanqiao
 * @version 1.0
 * @created 03-Apr-2012 11:53:19 AM
 */
class Non_local_VLAN_range_is_not_configured_on_controller extends MTMTest
{


	/**
	 * Clean up the particular setting for next test run
	 */
	public function Cleanup()
	{
	}

	/**
	 * Main entry for test
	 */
	public function MyExecute()
	{
		$this->preTest();
        $sTestUser="vlanuser1000s";
 		
		$sTestNpName="vlan1000s";
		$sNewVlan="1001-1050";
        
		$sSetupID=$this->framework->GetTestbed()->GetName();
		$iVlanID="21".substr($sSetupID,5,2);
		
		$sTestSsid="MTM_VLAN_21".substr($sSetupID,5,2);
		$this->addNetworkProfile($sTestNpName, "Enabled", $sNewVlan);
		
 		$sSoapCmd = "ControlledNetworkUpdateVirtualSCBinding grpName=AREA1-A vscProfile=$sTestSsid egressNetworkState=Enabled networkProfileName=$sTestNpName activeRadio=Radio_1_And_Radio_2 locationAwareGroup=AREA1-A\n";

        $retCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($retCode != 0) 
		{
            return FAIL;
        } 
		
        //Create a user that will egress to unknown vlan network (vlan 99)
        $oUser=new User($sTestUser,$sTestUser,"Enabled");        
        //$oUser->updateEgressVlan('$sNewVlan');
		
		$this->syncAPs();
		
        $this->wcbAssociateAndAuth("wpa2_dynamic",$sTestUser,$sTestUser,$sTestSsid,"PEAPVER0");
		
        Step::start("Check if the traffic must be blocked");
		
        if($this->checkContentInPage("/stat/l3_overview.asp","Blocked: Home network unknown")==FALSE)
        {
             Step::error("Traffic is not blocked,but should be");
             return FAIL;
        }
        else
        {
             Step::ok("Traffic is blocked");        
        }

        return PASS;            	
	}

}
?>