<?php

require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * Test Objective:
 *    Verify that two V-HNS clients can associate to different VSCs on a foreign
 * AP     and be successfully tunneled home.
 * 
 *    Reference: 7033 and/or 7034
 *    Scenario described in document: MTM with 2 controllers and a roaming client
 * 
 *    Steps:
 *    1.	Now use another laptop, LAPTOP3, to have two wireless clients
 *    2.       Let's call the previously created VSC (7033/7034), VSC1.
 *    3.       Create a new VSC, say VSC2, with the same configuration and binding
 *    as VSC1
 *    4.       Associate vlanuser1001 to VSC1 on msm11-1.  User is away, verify
 * traffic is tunneled as you did previously.
 *    5.	Associate the new user vlanuser1002 using LAPTOP3 to VSC2 on msm11-1.
 * User is away, verify traffic is tunneled.
 * 
 *    Pass/Fail criteria:
 *    -	Test passes if client validation is successful for both clients.
 * 
 *    Test results interpretation: Critical  
 * @author Yuanqiao Zhang Yuanqiao.Zhang@hp.com QA
 * @version 1.0
 * @updated 09-Mar-2012 2:30:09 PM
 */
class Single_Controller_two_clients extends MTMTest {

    /**
     * Main entry for test
     */
    public function MyExecute() {

        $this->doNeedCleanup = FALSE;
        Utility::turnDebugOff();
        /*
         * 1. Prepare the mobility domain as in the documentation .other necessary steps are implenment in preTest
         */
        Step::start("Prepare the mobility domain as in the documentation");
        $bRC = $this->createMobilityDomain();
        $this->preTest();
        if ($bRC == FALSE) {
            Step::error("fail to prepare the mobility domain as in the documentation");
            return FAIL;
        } else {
            Step::ok("Prepare the mobility domain as in the documentation");
        }

        $sVlanForVsc1 = substr($this->sHomeVlanForAp1, 4, 4);      //21##
        $sVlanForVsc2 = $sVlanForVsc1 + 100;                        //22##

        $sSsid1 = "MTM_VLAN_" . $sVlanForVsc1;
        $sSsid2 = "MTM_VLAN_" . $sVlanForVsc2;

        $sUsername1 = "user" . $sVlanForVsc1;

        //Create a user that will egress to that network (21##)
        $oUser1 = new User($sUsername1, $sUsername1, "Enabled");
        $oUser1->updateEgressVlan($sVlanForVsc1);

        //AP2 has the homework(23##) ,not 21## ,22##
        //$this->turnOnAP(AP2); to simulate the roaming
        $this->apRadioControl($this->scenario->GetDevice('AP2'), "Enabled");
        $this->apRadioControl($this->scenario->GetDevice('AP1'), "Disabled");
        /*
         * simulate the step2:
         * Associate user21## to VSC1 on AP2.  User is away, verify traffic is tunneled as you did previously. 
         *  
         */

        Step::start("Roaming, data should be tunned");

        $this->wcbAssociateAndAuth("wpa2_dynamic", $sUsername1, $sUsername1, $sSsid1, "PEAPVER0");

        if ($this->checkContentInPage("/stat/l3_overview.asp", "Data tunnel") == FALSE) {
            Step::error("Roaming, data should be tunned,but not tunned");
            return FAIL;
        } else {
            Step::ok("Roaming, data tunned");
        }
        /*
         * 5,Associate the new user user22## .  User is away, verify traffic is tunneled.
         * 
         */
        Step::start("User is away, verify traffic is tunneled");
        $this->wcbAssociateAndAuth("wpa2_dynamic", $sUsername1, $sUsername1, $sSsid2, "PEAPVER0");

        if ($this->checkContentInPage("/stat/l3_overview.asp", "Data tunnel") == FALSE) {
            Step::error("Roaming, data should be tunned,but not tunned");
            return FAIL;
        } else {
            Step::ok("Roaming, data tunned");
        }

        return PASS;
    }



}

?>