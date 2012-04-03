<?php
require_once ('..\..\..\zhayuanq\Documents\MyScripts\mtm\mtm_always_home\MTMTest.php');

/**
 * Test Objective:
 * Verify behavior when RADIUS does not return a VLAN attribute.  Consider the
 * user at home and egress traffic on the untagged interface of the MAP.
 * Steps:
 * 1.	Configure a client profile of novlanuser in RADIUS that does not have a
 * return attribute.
 * 2.	Remove the egress network defined on the vsc1 binding of msm10-1.
 * 3.	Associate novlanuser to vsc1 on msm10-1.
 * 4.	Traffic is egressed locally from msm10-1 using the untagged interface.
 * 
 * Pass/Fail criteria:
 * 1.	Traffic must be egressed locally from MAP.
 * 
 * Test results interpretation: Major
 * @author yuanqiao
 * @version 1.0
 * @updated 02-Apr-2012 2:28:33 PM
 */
class VLAN_ID_not_returned_by_RADIUS extends MTMTest
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
        $sTestUser="vlanuser99_3";
        $sSetupID=$this->framework->GetTestbed()->GetName();
        $sTestSsid="MTM_VLAN_23".substr($sSetupID,5,2);
        
        //Create a user that will egress to unknown vlan network (vlan 99)
        $oUser=new User($sTestUser,$sTestUser,"Enabled");        
        //$oUser->updateEgressVlan('vlan99');
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

}
?>