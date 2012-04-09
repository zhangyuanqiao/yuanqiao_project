<?php
require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * Test Objective:
 *    Verify that the controller uses load balancing to evenly distribute tunneled
 *    data when a VLAN range is returned by RADIUS.  All users for this test will
 *   have RADIUS profiles defined to return a VLAN name corresponding to a VLAN
 * range on the controller.
 *    Steps:
 *    1.	NP of WLAN1 (consisting of VLAN range 2001-2003) is defined on     msm7xx-
 * 1.
 *    2.	Associate range1user1 to msm10-1.  User will be tunneled to msm7xx-1
 * and egressed from there.  User should be assigned VLAN 2001.
 *    3.	Associate range1user2 to msm10-1.  User will be tunneled to msm7xx-1
 * and egressed from there.  User should be assigned VLAN 2002.
 *    4.	Associate range1user3 to msm10-1.  User will be tunneled to msm7xx-1
 * and egressed from there.  User should be assigned VLAN 2003.
 *    5.	Associate range1user4 to msm10-1.  User will be tunneled to msm7xx-1
 * and egressed from there.  User should be assigned VLAN 2001.
 * 
 *    Pass/Fail criteria:
 *    1.	Test passes if client validation is successful.
 *    2.	Controller must assign clients to each of the VLANs defined within
 * the range of the NP in a round-robin fashion.
 * 
 * @author Yuanqiao Zhang Yuanqiao.Zhang@hp.com QA
 * @version 1.0
 * @updated 28-Mar-2012 12:06:56 PM
 */
class Load_Balancing_with_a_single_controller_RADIUS_returned_attribute extends MTMTest
{
                                  

	/**
	 * Main entry for test
	 */
	public function MyExecute()
	{
        
        $this->bDoNeedCleanup=FALSE; 
        Utility::turnDebugOff();
        /*
        * 1. Prepare the mobility domain as in the documentation .other necessary steps are implenment in preTest
        */
        Step::start("Prepare the mobility domain as in the documentation");              
        $bRetCode=$this->createMobilityDomain();
        $this->preTest();
        
        if($bRetCode==FALSE)
        {
            Step::error("fail to prepare the mobility domain as in the documentation");
            return FAIL;
        }
        else
        {
            Step::ok("Prepare the mobility domain as in the documentation");                    
        }        
        
//        $this->addLocalNetwork($this->scenario->GetDevice('AP1'),"VLAN2143");
//        $this->addLocalNetwork($this->scenario->GetDevice('AP2'),"VLAN2343");
//        
        
        $sVlanForVsc1=substr($this->sHomeVlanForAp1,4,4);      //21##
        $sVlanForVsc2=substr($this->sHomeVlanForAp2,4,4);      //22##
        
        
        $sVlanForVsc1="2143";
        $ssid1="MTM_VLAN_".$sVlanForVsc1;
        $ssid2="MTM_VLAN_".$sVlanForVsc2;
         
        $username1="user".$sVlanForVsc1;
        $username2="user".$sVlanForVsc2;
        
        $sTestUser="testUser";
        $sTestNpName="testNP";
        $sTestUser="MTM_user_id_VLAN2143";
        /*
        *create networkprofile with multiple vlan. first vlan is vlan21## another is vlan23## 
        */ 
        $this->addNetworkProfile($sTestNpName, "Enabled", "2143"); 
        //Create a user that will egress to that network (21##)
        $user1=new User($sTestUser,$sTestUser,"Enabled");        
//        $user1->updateEgressVlan("$sVlanForVsc1,$sVlanForVsc2");        
        $user1->updateEgressVlan($sTestNpName);        
         
        //AP2 has the homework(23##) ,not 21## ,22##
        //$this->turnOnAP(AP2); to simulate the roaming
        $this->apRadioControl($this->scenario->GetDevice('AP2'),"Enabled"); 
        $this->apRadioControl($this->scenario->GetDevice('AP1'),"Disabled");                         
        /*
        *simulate the step2:
        * Associate user21## to VSC1 on AP2.  User is away, verify traffic is tunneled as you did previously. 
        *  
        */ 
        
        Step::start("Roaming, data should be tunned"); 
        
        $this->wcbAssociateAndAuth("wpa2_dynamic",$sTestUser,$sTestUser,"MTM_VLAN_2143","PEAPVER0");       
        
        if($this->checkContentInPage("/stat/l3_overview.asp","Data tunnel")==FALSE)
        {
             Step::error("Roaming, data should be tunned,but not tunned");
             return FAIL;
        }
        else
        {
             Step::ok("Roaming, data tunned");        
        }
        /*
         * 5,Associate the new user user22## .  User is away, verify traffic is tunneled.
    * 
        */
        Step::start("User is away, verify traffic is tunneled");                   
        $this->wcbAssociateAndAuth("wpa2_dynamic",$sTestUser,$sTestUser,"MTM_VLAN_2143","PEAPVER0");       
        
        if($this->checkContentInPage("/stat/l3_overview.asp","Data tunnel")==FALSE)
        {
             Step::error("Roaming, data should be tunned,but not tunned");
             return FAIL;
        }
        else
        {
             Step::ok("Roaming, data tunned");        
        }
        
        return PASS;
        
	}

	/**
	 * Clean up the particular setting for next test run
	 */
	public function Cleanup()
	{
	}
    
}
?>