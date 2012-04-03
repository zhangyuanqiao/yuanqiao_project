<?php

require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * <b>Summary:</b> Add Network Profiles
 * <b>id:</b> 7032
 * <b>Description:</b>
 * 2.1.2 Add Network Profiles
 * [SR R-01-01001 through R-01-010011]
 * 
 * NOTE:
 * - "Optionally" indicates that steps are not necessary for basic functionality
 * but are executed here if they are not covered anywhere else;
 * -
 * 
 * Test Objective:
 * Verify that Network Profiles (NP) can be added.
 * 
 * Steps:
 * 1.	Goto the MSC-> Network-> Network Profiles page
 * 2.	Add a NP with a VLAN name of 64 characters and with VLAN ID 4094
 * 3.      Optionally:
 *        a. Try to add a NP with a VLAN name longer than 64 characters
 *           and
 *           a VLAN ID below 4094; this should fail
 *        b. Try to add a NP with a VLAN ID larger than 4094
 *           and
 *           a VLAN name of 5 characters; this should fail
 *        c. Try to add a NP with a VLAN ID of 0
 *           and
 *           a VLAN name of 5 characters; this should fail
 *        d. Try to add a NP with an empty VLAN name
 *           and
 *           a VLAN ID below 4094; this should fail
 *        e. Try to add a NP with a name that starts with a numeric digit
 *           - with a VLAN ID
 *           then
 *           - without a VLAN ID
 *           ; this should fail
 * 4.      Add a NP that has:
 *        - an 8 characters VLAN name
 *        - a non-numeric character as a first letter
 *        - a range for the VLAN ID (not overlapping with other defined
 * NP/ranges)
 * 5.      Add another NP that:
 *        - overlaps with the one created in 4
 *        - try to associates both NPs to the LAN port or another port
 *        - you should not be able to do so
 *        ; this should fail
 * 
 * Pass/Fail criteria:
 * -	Test passes if each of the steps behaved as described.
 * @author Yuanqiao Zhang Yuanqiao.zhang@hp.com QA
 * @version 1.0
 * @updated 27-Feb-2012 10:19:28 AM
 */
class AddNetworkProfile extends MTMTest {

    /**
     * Clean up the particular setting for next test run
     */
    public function Cleanup() {
        $this->deleteAllNetworkProfiles();
    }

    /**
     * Main entry for test
     */
    public function MyExecute() {

        $s64CharsName = "abcdefgh" . "abcdefgh" . "abcdefgh" . "abcdefgh"
                . "abcdefgh" . "abcdefgh" . "abcdefgh" . "abcdefgh";

        /*
         * 1.    Goto the MSC-> Network-> Network Profiles page   
         */
        Step::start("Go to the MSC-> Network-> Network Profiles page");
        Step::ok();

        /*
         * 2.    Add a NP with a VLAN name of 64 characters and with VLAN ID 4094 
         */
        Step::start("Create a network profile $s64CharsName");
        $bRetCode = $this->addNetworkProfile($s64CharsName, "Enabled", 4094);
        if ($bRetCode == FALSE) {
            Step::error("Fail to create a network profile");
            //return "failed";
            return FAIL;
        } else {
            //Logger::toAll("Succeed to create a network profile,that is what we expected");
            Step::ok("Succeed to create a network profile,that is what we expected");
        }


        /*
         * 3.      Optionally:
         *        a. Try to add a NP with a VLAN name longer than 64 characters
         *           and
         *           a VLAN ID below 4094; this should fail
         *        b. Try to add a NP with a VLAN ID larger than 4094
         *           and
         *           a VLAN name of 5 characters; this should fail
         *        c. Try to add a NP with a VLAN ID of 0
         *           and
         *           a VLAN name of 5 characters; this should fail
         *        d. Try to add a NP with an empty VLAN name
         *           and
         *           a VLAN ID below 4094; this should fail
         *        e. Try to add a NP with a name that starts with a numeric digit
         *           - with a VLAN ID 
         *           then
         *           - without a VLAN ID
         *           ; this should fail
         */
        Step::Start("Optionally");
        //For the array, vlan neme=>vlan id
        $aNpNameAndVlan = array($s64CharsName . "mtm_automation" => 200, // 3a below 4094
            "abcde" => 5000, //3b
            "abcdf" => 0, //3c
            "" => 200, //3d
            "1a" => 200, //3e1
            "1b" => ""                 //3e2
        );
        foreach ($aNpNameAndVlan as $name => $vlanID) {
            Step::start("Create a network profile $name");
            $bRetCode = $this->addNetworkProfile($name, "Enabled", $vlanID);
            if ($bRetCode == TRUE) {
                Step::error("Succeed to create a network profile,but should not");
                return FAIL;
            } else {
                Step::ok("Fail to create a network profile,that is what we expected");
            }
        }

        Step::ok();

        /*
         * 4.      Add a NP that has:
         *        - an 8 characters VLAN name
         *        - a non-numeric character as a first letter
         *        - a range for the VLAN ID (not overlapping with other defined
         */
        Step::start("Create a network profile");
        $bRetCode = $this->addNetworkProfile("abcdefgh", "Enabled", "10-20");
        if ($bRetCode == FALSE) {
            Step::error("Fail to create a network profile");
            return FAIL;
        } else {
            Step::ok("Succeed to create a network profile,that is what we expected");
        }

        /*
         * 5.      Add another NP that:
         *        - overlaps with the one created in 4
         *        - try to associates both NPs to the LAN port or another port
         *        - you should not be able to do so
         *        ; this should fail         
         */
        Step::start("Create a network profile vlan10");
        $this->addNetworkProfile("vlan10", "Enabled", "10");
        $bRetCode = $this->addVlan("vlan10", "LAN_Port", "DHCP_IP", "", "", "", "Enabled");
        $bRetCode2 = $this->addVlan("abcdefgh", "LAN_Port", "DHCP_IP", "", "", "", "Enabled");
        if ($bRetCode or $bRetCode2) {
            Step::error("Success to associates both NPs to the LAN port");
            throw new TestExepction("sdfafafasf");
            //return FAIL;
        } else {
            Step::ok("Fail to associates both NPs to the LAN port,that is what we expected");
        }

        return PASS;
    }

}

?>