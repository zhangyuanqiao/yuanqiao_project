<?php

require_once ('MTMTest.php');

/**
 * Objective: To define a mobility domain
 * 
 * For MTM test cases, use the following documents as reference:
 * - ScenariosToAutomate.docx that can be found on Sharepoint
 * - MSC's Management and configuration guide: Describes how to setup Radius user
 * - For supplicant configuration: http://teams7.sharepoint.hp.
 * com/teams/mobilityrd/sqa/QA%20Docs/Training%20materials/Supplicant%20configurati
 * on.docx
 * 
 * Some test cases will have a reference to a particular section.
 * 
 * Setup:
 * The following controllers should have proper MTM licensing installed on them:
 * - Primary Controller:   Any controller that support mobility
 * - Mobility Team:        A controller team with controllers that support
 * mobility
 * - Mobility Controller:  Another controller that supports mobility
 *  (or use one of the team members after unteaming)
 * 
 * Test steps:
 * 1.	Start with the Primary Controller's web interface
 * 2.      Goto the SC-> Management-> Device Discovery Page
 * 3.      Start with the Mobility controller discovery box unchecked
 * 4.      Check the checkbox "Mobility controller discovery"
 * 5.      This is your primary mobility controller, so check the box:
 *        "This is the primary mobility controller"
 * 6.      Click Save
 * 7.      This should enable mobility on this controller provided your license is
 * loaded
 * 8.      For your "Mobility Team", then repeat for the "Mobility Controller",
 * configure them for mobility as well:
 *        a. Goto the SC-> Management-> Device Discovery Page
 *        b. Start with the Mobility controller discovery box unchecked
 *        c. Check the checkbox "Mobility controller discovery"
 *        d. Make sure the checkbox "This is the primary mobility controller" is
 * not checked
 *        e. In the input area "IP address of the primary mobility controller"
 * enter the IP address of the Primary Controller
 *        f. Click Save
 * 9.      Go to the Primary Controller web interface
 * 10.     Click Status-> Mobility and verify that your configured controllers are
 * listed under Controllers.  The controller on which you are logged in might show
 * in the list and this could be a bug, check QC for any bug.
 * 
 * Pass/Fail criteria:
 * - Controllers are configured and listed in Status
 * 
 * Network diagram:
 * 
 * Test results interpretation: Critical
 * @author zhayuanq
 * @version 1.0
 * @updated 27-Feb-2012 10:19:51 AM
 */
class DefiningTheMobilityDomain extends MTMTest {

    /**
     * Main entry for test
     */
    public function MyExecute() {
        /*
         * step 1-8 
         *  
         * 1.    Start with the Primary Controller's web interface
         * 2.      Goto the SC-> Management-> Device Discovery Page
         * 3.      Start with the Mobility controller discovery box unchecked
         * 4.      Check the checkbox "Mobility controller discovery"
         * 5.      This is your primary mobility controller, so check the box:
         *        "This is the primary mobility controller"
         * 6.      Click Save
         * 7.      This should enable mobility on this controller provided your license is
         * loaded
         * 8.      For your "Mobility Team", then repeat for the "Mobility Controller",
         * configure them for mobility as well:
         *        a. Goto the SC-> Management-> Device Discovery Page
         *        b. Start with the Mobility controller discovery box unchecked
         *        c. Check the checkbox "Mobility controller discovery"
         *        d. Make sure the checkbox "This is the primary mobility controller" is
         * not checked
         *        e. In the input area "IP address of the primary mobility controller"
         * enter the IP address of the Primary Controller
         *        f. Click Save
         */
        Step::start("Set up a mobility domain");
        $bRetCode = $this->createMobilityDomain();
        if ($bRetCode == FALSE) {
            Step::error("Fail to set up a domain");
            return FAIL;
        } else {
            Step::ok("Succeed to create a network profile,that is what we expected");
        }

        /*
         * 9.      Go to the Primary Controller web interface
         * 10.     Click Status-> Mobility and verify that your configured controllers are        
         */
        Step::start("Click Status-> Mobility and verify that your configured controllers are listed under Controllers");
        $bRetCode = $this->checkMobilityStatus();
        if ($bRetCode == FALSE) {
            Step::error("The controller on which you are logged in DOES NOT show in the list");
            return FAIL;
        } else {
            Step::ok("The controller on which you are logged in DOES show in the list");
        }


        return PASS;
    }

    private function checkMobilityStatus() {
        //Start the selenium server and open up the browser 
        WEB::startBrowser($this->sMscIP, $this->oController->GetProductName());
        //Login to the device's home.asp page
        WEB::login();
        $selenium_driver = WEB::getSelenium();
        Logger::toAll($this->sMscIP . "/stat/l3_overview.asp");
        $selenium_driver->open("stat/l3_overview.asp");
        sleep(10);

        $sRetCode = TRUE;

 //       if (MTMEnv::$teaming_state == "Enabled") 
        if (count($this->aoControllerList)>1) 
        {
            //all controllers will show up in this domain.
            $aoControllerList = $this->scenario->GetControllerList();
            foreach ($aoControllerList as $index => $controller) {
                $serial_name = $controller->GetSerialNumber();
                if ($serial_name == $this->oController->GetSerialNumber()) {
                    continue; // do not need show myself                              
                }

                $sRetCode = $selenium_driver->isTextPresent($serial_name);
                if ($sRetCode == FALSE) {
                    break;
                }
            }
        } else {
            ;
            // Anwar thinks this is a bug, he think all controllers(including himself) show up. The following script reserved for future .
            //$return_code=$selenium_driver->isTextPresent(MTMEnv::$serial_of_msc1);  
        }

        WEB::logout();
        WEB::closeBrowser(TRUE);
        return $sRetCode;
    }

    /**
     * Clean up the particular setting for next test run
     */
    public function Cleanup() {
        ;
    }

}

?>