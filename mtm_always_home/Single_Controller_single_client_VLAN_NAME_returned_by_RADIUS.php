<?php

require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * Test Objective: Verify that a single V-HNS client can associate to a foreign AP
 * and be successfully tunneled home. RADIUS returns a VLAN name to identify the
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
 * @updated 09-Mar-2012 1:31:11 PM
 */
class Single_Controller_single_client_VLAN_NAME_returned_by_RADIUS extends MTMTest {

    /**
     * Main entry for test
     */
    public function MyExecute() {

        $this->doNeedCleanup = FALSE;
        Utility::turnDebugOff();
        /*
         * 1. Prepare the mobility domain as in the documentation
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

        $this->apRadioControl($this->scenario->GetDevice('AP2'), "Enabled");
        $this->apRadioControl($this->scenario->GetDevice('AP1'), "Enabled");

        $sVlanAp1 = substr($this->sHomeVlanForAp1, 4, 4);
        $sSsid = "MTM_VLAN_" . $sVlanAp1;
        $sUsername = "user" . $sVlanAp1;
        //Create a user that will egress to that network (vlan ID)
        $oUser1 = new User($sUsername, $sUsername, "Enabled");
        $oUser1->updateEgressVlan($this->sHomeVlanForAp1);

        /*
         * simulate the step2:
         *  2. Associate the user to , that user uses VLAN21##.
         *    a. VLAN20 is not local on Group1
         *    b. User is away, associating with AP2 that uses VLAN23## as local network
         *    c. Verify traffic is tunneled correctly.
         *  
         */

        Step::start("Roaming, data should be tunned");
        $this->apRadioControl($this->scenario->GetDevice('AP1'), "Disabled");
        $this->wcbAssociateAndAuth("wpa2_dynamic", $sUsername, $sUsername, $sSsid, "PEAPVER0");
        Utility::insertBreakPoint(6);
        if ($this->checkContentInPage("/stat/l3_overview.asp", "Data tunnel") == FALSE) {
            Step::error("Roaming, data should be tunned,but not tunned");
            return FAIL;
        } else {
            Step::ok("Roaming, data tunned");
        }
        /*
         * 2. Associate that user now to AP1
         *  a. User is home and traffic egresses locally.               * 
         */
        Step::start("User is home and traffic egresses locally");
        $this->apRadioControl($this->scenario->GetDevice('AP2'), "Disabled");
        $this->apRadioControl($this->scenario->GetDevice('AP1'), "Enabled");
        $this->wcbAssociateAndAuth("wpa2_dynamic", $sUsername, $sUsername, $sSsid, "PEAPVER0");
        if ($this->checkContentInPage("/stat/l3_overview.asp", "Data tunnel") == TRUE) {
            Step::error("User is home , but traffic is tunned. That is wrong");
            return FAIL;
        } else {
            Step::ok("User is home and traffic egresses locally");
        }

        return PASS;
    }

    /**
     * Clean up the particular setting for next test run
     */
    public function Cleanup() {
        
    }

}

?>