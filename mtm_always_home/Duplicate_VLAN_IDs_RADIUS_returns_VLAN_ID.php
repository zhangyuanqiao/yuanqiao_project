<?php
require_once ('MTMTest.php');

/**
Test Objective: 
Verify that the same VLAN ID can be defined on both the LAN and Internet ports of the controller and that the correct interface is used when egressing traffic.  To do this, two separate network profiles must be created with the same VLAN ID, but unique names.  
Steps: 
1.	Create network profile NP-1 with a VLAN ID of 1001
2.	Create network profile NP-2 with a VLAN ID of 1001
3.	Assign NP-1 to the LAN port
4.	Assign NP-2 to the Internet port
5.	Associate client np-1user
6.	Verify traffic is egressed out the LAN port
7.	Associate client np-2user
8.	Verify traffic is egressed out the Internet port

Pass/Fail criteria:
-	Test passes if traffic is egressed out the correct port on the controller.

Test results interpretation: Major   


 * 
 * Test results interpretation: Major
 * @author zhayuanq
 * @version 1.0
 * @created 29-Mar-2012 11:50:17 AM
 */
class Duplicate_VLAN_IDs_RADIUS_returns_VLAN_ID  extends MTMTest
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
        $sTestNpName1="testNP1";
		$sTestNpName2="testNP2";
        $sTestUser1="mtmUser1";
		$sTestUser2="mtmUser2";
        /*
        *create networkprofile vlan25## 
        */ 
		
		$sSetupID=$this->framework->GetTestbed()->GetName();
		$iVlanID="25".substr($sSetupID,5,2);
		$sTestSsid="MTM_VLAN_23".substr($sSetupID,5,2);
		$this->addNetworkProfile($sTestNpName1, "Enabled", $iVlanID);
 //       $this->addNetworkProfile($sTestNpName1, "Enabled", 2543);
        $this->addVlan($sTestNpName1, "LAN_Port", "DHCP_IP", "1.1.1.1", "1.1.1.1", "1.1.1.1", "Enabled");   
		
		$this->addNetworkProfile($sTestNpName2, "Enabled", $iVlanID);
//		$this->addNetworkProfile($sTestNpName2, "Enabled", 2543);
        $this->addVlan($sTestNpName2, "Internet_Port", "DHCP_IP", "1.1.1.1", "1.1.1.1", "1.1.1.1", "Enabled");   

        //Create a user that will egress to that network (25##)
        $oUser=new User($sTestUser1,$sTestUser1,"Enabled");
        $oUser->updateEgressVlan($iVlanID); 
		
		$oUser=new User($sTestUser2,$sTestUser2,"Enabled");
        $oUser->updateEgressVlan($iVlanID); 
		
        $this->addLocalNetwork($this->scenario->GetDevice('AP1'),$sTestNpName1);
        $this->addLocalNetwork($this->scenario->GetDevice('AP2'),$sTestNpName1);
		
		$this->addLocalNetwork($this->scenario->GetDevice('AP1'),$sTestNpName2);
        $this->addLocalNetwork($this->scenario->GetDevice('AP2'),$sTestNpName2);

        /*
        *For each vlan setting, update vlan adn check egress traffic 
        */
        $aUsers=array("mtmUser1","mtmUser2");
		
        foreach($aUsers as $key=>$sTestUser)
        {
            Step::start("To check if traffic is egressed out the correct port on the controller. ");
          
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

		return PASS;


	}

}
?>
