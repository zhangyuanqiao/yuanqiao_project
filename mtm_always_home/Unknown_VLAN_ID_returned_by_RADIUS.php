<?php
require_once ('MTMTest.php');

/**
 * Test Objective:
 * Verify behavior when RADIUS returns a VLAN ID that is not defined on a
 * controller in the network.  Consider the user at home and egress the client’s
 * traffic locally from the MAP using the VLAN ID returned by RADIUS.
 * Steps:
 * 1.	Configure a client profile of vlanuser99 in RADIUS that returns VLAN ID of
 * 99
 * 2.	Make sure that VLAN 99 is not defined as a network profile or an interface
 * on the controller.
 * 3.	Associate vlanuser99 to msm10-1.
 * 4.	Traffic is egressed locally at the MAP using VLAN 99.
 * Pass/Fail criteria:
 * 1.	Message must be logged that an unknown VLAN was returned
 * 2.	Traffic must be egressed locally from MAP using VLAN 99.
 * 
 * Test results interpretation: Major
 * @author yuanqiao
 * @version 1.0
 * @created 02-Apr-2012 2:25:34 PM
 */
class Unknown_VLAN_ID_returned_by_RADIUS extends MTMTest
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
        $sTestUser="vlanuser99_2";
        $sSetupID=$this->framework->GetTestbed()->GetName();
        $sTestSsid="MTM_VLAN_23".substr($sSetupID,5,2);
        
        //Create a user that will egress to unknown vlan network (vlan 99)
        $oUser=new User($sTestUser,$sTestUser,"Enabled");        
        $oUser->updateEgressVlan('99');
		
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