<?php
require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * Test Objective: Verify that a single V-HNS client can associate to a foreign AP
 * and be successfully tunneled home. RADIUS returns a VLAN ID to identify the
 * user's home network.
 * For reference documents refer to Test case 20272
 * Scenario described in document:
 * MTM with 1 controller and a roaming client Steps:
 * 
 * 1. Prepare the mobility domain as in the documentation
 *  2. Associate the user to , that user uses VLAN20.
 *    a. VLAN20 is not local on Group2
 *    b. User is away, associating with AP2 that uses VLAN40 as local network
 *    c. Verify traffic is tunneled correctly.
 * 
 *  2. Associate that user now to AP1
 *    a. User is home and traffic egresses locally.
 * Pass/Fail criteria: - Test passes if client validation is successful. Test
 * results interpretation: Critical
 * @author Yuanqiao Zhang Yuanqiao.Zhang@hp.com QA
 * @version 1.0
 * @updated 08-Mar-2012 1:54:41 PM
 */
class Single_Controller_single_client_VLAN_ID_returned_by_RADIUS extends MTMTest
{
                                  

	/**
	 * Main entry for test
	 */
	public function MyExecute()
	{
        /*
        * Debug mode is off default.
        * if this test is the last one in this suite. we should set the doNeeedClanup TRUE for cleanup,skip it otherwise.                                         
        */

        $this->bDoNeedCleanup=FALSE; 
        Utility::turnDebugOff();
        /*
        * 1. Prepare the mobility domain as in the documentation
        */
        Step::start("Prepare the mobility domain as in the documentation,and test enviroment");              
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
        
        //turn on radios of 2 APs.
        $this->apRadioControl($this->scenario->GetDevice('AP2'),"Enabled");
        $this->apRadioControl($this->scenario->GetDevice('AP1'),"Enabled");
        
        $vlan_ap1=substr($this->sHomeVlanForAp1,4,4);
        $ssid="MTM_VLAN_".$vlan_ap1;
        $username="user".$vlan_ap1;
        //Create a user that will egress to that network (vlan ID)
        $user1=new User($username,$username,"Enabled");        
        $user1->updateEgressVlan($vlan_ap1);
        
        /*
        *simulate the step2:
        *  2. Associate the user to , that user uses VLAN21##.
        *    a. VLAN20 is not local on Group1
        *    b. User is away, associating with AP2 that uses VLAN23## as local network
        *    c. Verify traffic is tunneled correctly.
        *  
        */ 
        
        Step::start("Roaming, data should be tunned");
        //just let the radio of AP2 on. to simulate the roaming                   
        $this->apRadioControl($this->scenario->GetDevice('AP1'),"Disabled");                   
        $this->wcbAssociateAndAuth("wpa2_dynamic",$username,$username,$ssid,"PEAPVER0");       
        
        //if($this->checkContentInPage("/stat/l3_overview.asp",$this->wcb->GetInterface('br0')->GetMAC())==FALSE)
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
         * 2. Associate that user now to AP1
         *  a. User is home and traffic egresses locally.               * 
        */
        Step::start("User is home and traffic egresses locally"); 
         //just let the radio of AP1 on. the traffice will be at home                       
        $this->apRadioControl($this->scenario->GetDevice('AP2'),"Disabled"); 
        $this->apRadioControl($this->scenario->GetDevice('AP1'),"Enabled");                    
        $this->wcbAssociateAndAuth("wpa2_dynamic",$username,$username,$ssid,"PEAPVER0");       
        
        //if($this->checkContentInPage("/stat/l3_overview.asp",$this->wcb->GetInterface('br0')->GetMAC())==TRUE)
        if($this->checkContentInPage("/stat/l3_overview.asp","Data tunnel")==TRUE)
        {
             Step::error("User is home , but traffic is tunned. That is wrong");
             return FAIL;
        }
        else
        {
             Step::ok("User is home and traffic egresses locally");        
        }
        
        return PASS;
        
	}
    
}
?>