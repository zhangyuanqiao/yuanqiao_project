<?php

require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * Test Objective:
 * Verify that two clients can associate to the same VSC when one is a roamer and
 * the other is not. This will test the scenario where only some clients have a
 * VLAN attribute returned for tunneling, while all others use the default VSC
 * binding settings.
 * 
 * For reference documents refer to Test case 20272
 * 
 * Steps
 * if executed after 7037:
 * 1. Disconnect LAPTOP3 from AP2/VSC2 and connect it to AP1/VSC1 so that both
 * clients are connected to the same VSC
 * 2. Verify that one client is a roamer while the other is not
 * 
 * Otherwise:
 * 1. Associate vlanuser1001 to vsc1 on msm11-1.
 * 2. Associate user1 to vsc1 on msm11-1.
 * 3. Vlanuser1001 should be tunneled back to msm10-1.
 * 4. Non-VLAN user should be egressed locally onto VLAN 1004.
 * 
 * Pass/Fail criteria: - Test passes if client validation is successful for both
 * clients. Test results interpretation: Major
 * @author Yuanqiao Zhang Yuanqiao.Zhang@hp.com QA
 * @version 1.0
 * @updated 09-Mar-2012 3:02:36 PM
 */
class Roaming_and_Non_roaming_clients extends MTMTest {

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
        $sSsid1 = "MTM_VLAN_" . $sVlanForVsc1;
        $sRoamingUsername = "user" . $sVlanForVsc1;
        $sNoRoamingUsername = "user" . "no_vlan";


        //Create a user that will egress to that network (vlan ID)
        $oUser1 = new User($sRoamingUsername, $sRoamingUsername, "Enabled");
        $oUser1->updateEgressVlan($sVlanForVsc1);


        //AP2 has the homework(23##) ,not 21## ,22##  ,turn the AP1 off ,and turn on AP2
        $this->apRadioControl($this->scenario->GetDevice('AP2'), "Enabled");
        $this->apRadioControl($this->scenario->GetDevice('AP1'), "Disabled");
        /*
         * simulate the step3:
         * user21## should be tunneled back to his homework.

         *  
         */

        Step::start("Roaming, data should be tunned");

        $this->wcbAssociateAndAuth("wpa2_dynamic", $sRoamingUsername, $sRoamingUsername, $sSsid1, "PEAPVER0");

        if ($this->checkContentInPage("/stat/l3_overview.asp", "Data tunnel") == FALSE) {
            Step::error("Roaming, data should be tunned,but not tunned");
            return FAIL;
        } else {
            Step::ok("Roaming, data tunned");
        }
        /*
         * 5,Non-VLAN user should be egressed locally.
         * 
         */
        Step::start("Non-VLAN user should be egressed locally");
        $this->wcbAssociateAndAuth("wpa2_dynamic", $sNoRoamingUsername, $sNoRoamingUsername, $sSsid1, "PEAPVER0");

        if ($this->checkContentInPage("/stat/l3_overview.asp", "Data tunnel") == TRUE) {
            Step::error("Non-VLAN user should be egressed locally,but tunned.WRONG");
            return FAIL;
        } else {
            Step::ok("Non-VLAN user should be egressed locally");
        }

        return PASS;
    }

}

?>