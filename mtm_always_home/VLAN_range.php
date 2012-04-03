<?php
require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * Verify that traffic is tunneled for users when a VLAN range is defined as the
 * egress network binding.  For this test, WLAN1 (VLANs 2001-2003) is defined as
 * the egress network binding for msm10-1.  The range is not configured as a home
 * network for msm10-1.  VLANs 2001-2003 are defined locally on msm7xx-1.
 * The traffic will be egressed from the controller using a round-robin assignment
 * of the VLANs within the range.
 * 
 * 
 * Note(Anwar): Changed to Active
 * 
 * Steps:
 * 1.	Associate user1 to VSC1 on msm10-1.  Traffic is tunneled to msm7xx-1 and
 * egressed onto VLAN 2001.
 * 2.	Associate user2 to VSC1 on msm10-1.  Traffic is tunneled to msm7xx-1 and
 * egressed onto VLAN 2002.
 * 3.	Associate user3 to VSC1 on msm10-1.  Traffic is tunneled to msm7xx-1 and
 * egressed onto VLAN 2003.
 * 4.	Associate user4 to VSC1 on msm10-1.  Traffic is tunneled to msm7xx-1 and
 * egressed onto VLAN 2001.
 * 
 * Pass/Fail criteria:
 * -	Test passes if clients are tunneled to the controller and egressed in a round-
 * robin format.
 * 
 * Test results interpretation: Major
 * @author Yuanqiao Zhang Yuanqiao.Zhang@hp.com QA
 * @version 1.0
 * @updated 12-Mar-2012 4:37:06 PM
 */
class VLAN_range extends MTMTest
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
        $return_code=$this->createMobilityDomain();
        $this->preTest();
        if($return_code==FALSE)
        {
            Step::error("fail to prepare the mobility domain as in the documentation");
            return FAIL;
        }
        else
        {
            Step::ok("Prepare the mobility domain as in the documentation");                    
        }        
        
        $vlan_for_vsc1=substr($this->sHomeVlanForAp1,4,4);      //21##
        $vlan_for_vsc2=$vlan_for_vsc1+100;                        //22##
        
        $ssid1="MTM_VLAN_".$vlan_for_vsc1;
        $ssid2="MTM_VLAN_".$vlan_for_vsc2;
         
        $username1="user".$vlan_for_vsc1;
        $username2="user".$vlan_for_vsc2;
        
                 
        //Create a user that will egress to that network (21##)
        $user1=new User($username1,$username1,"Enabled");        
        $user1->updateEgressVlan($vlan_for_vsc1);        
         
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
                   
        $this->wcbAssociateAndAuth("wpa2_dynamic",$username1,$username1,$ssid1,"PEAPVER0");       
        
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
        $this->wcbAssociateAndAuth("wpa2_dynamic",$username1,$username1,$ssid2,"PEAPVER0");       
        
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