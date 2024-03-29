<?php
require_once ('MTMTest.php');

/**
 * Test Objective:
 * Verify behavior when RADIUS returns a VLAN name that is not defined on a
 * controller in the network.  In this case, we do not know which VLAN ID should
 * be used (since only a name was returned).  Block traffic.
 * Steps:
 * 1.	Configure a client profile of noname in RADIUS that returns a VLAN name of
 * noname
 * 2.	Make sure that noname is not defined as a network profile on the controller.
 * 
 * 3.	Associate noname to msm10-1.
 * 4.	Traffic is blocked locally on msm10-1
 * Pass/Fail criteria:
 * 1.	Message must be logged that 'Network definition does not exist' on the AP
 * ie: Jul 28 11:40:15 err	eapolserver  CN0AD3300N Network definition 'noname'
 * does not exist (mac-address=00-24-D7-BE-3A-CC)
 * 2.	Traffic must be blocked
 * 
 * Test results interpretation: Major
 * @author yuanqiao
 * @version 1.0
 * @created 02-Apr-2012 2:26:22 PM
 * 
 * 
 */
class Unknown_VLAN_name_returned_by_RADIUS_at_home extends MTMTest
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
        $sTestUser="vlanuser99";
        $sSetupID=$this->framework->GetTestbed()->GetName();
        $sTestSsid="MTM_VLAN_23".substr($sSetupID,5,2);
        $this->updateFallbackMethod($sTestSsid,"Assume_Home");        
        //Create a user that will egress to unknown vlan network (vlan 99)
        $oUser=new User($sTestUser,$sTestUser,"Enabled");        
        $oUser->updateEgressVlan('vlan99');
		
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
        $this->updateFallbackMethod($sTestSsid,"None");
        return PASS;            	
	}

}
?>