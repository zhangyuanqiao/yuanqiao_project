<?php

require_once ('MTMTest.php');
require_once ('Utility.php');

/**
 * Test Objective:
 * Verify that Wireless Mobility cannot be enabled unless a valid L2/L3 Mobility
 * license is installed on the Service Controller.  Note: this test is not valid
 * on an MSM765 since it does not require an L2/L3 Mobility license to be
 * installed.
 * Steps:
 * 1.	Remove any existing L2/L3 Mobility license from the MSC (MSC-> Maintenance->
 * Licenses)
 * 2.	Create a new VSC with Access Control disabled
 * 3.	Enable Wireless Mobility options and save the VSC.  An error should be
 * generated.
 * 4.	Install/re-install L2/L3 License
 * 5.	Verify that Wireless Mobility options are now available for configuration
 * 
 * Pass/Fail criteria:
 * -	Test passes if Wireless Mobility cannot be enabled if a valid license is not
 * installed.
 * 
 * Test results interpretation: Critical
 * @author Yuanqiao Zhang yuanqiao.zhang@hp.com QA
 * @updated 27-Feb-2012 10:19:40 AM
 */
class MobilityLicenseValidation extends MTMTest {

    public function Cleanup() {
        VSCProfile::deleteVSC("MTM1");
    }

    public function MyExecute() {

        Utility::turnDebugOn();

        if ($this->oController->GetProductName() == "MSM765") {
            Logger::toAll("We do not support MSM765");
            return NA;
        }

        Utility::banner("start");

        /*
         * 1.    Remove any existing L2/L3 Mobility license from the MSC (MSC-> Maintenance->   
         */
        Step::start("Remove all licenses");
        $this->removeMobilityLicense();   //note : to make thing easy to understand. we do not need check if it success because it will fail when I try to remove empty license. 
        Step::ok();
        /*
         * 2.    Create a new VSC with Access Control disabled    
         */
        Step::start("Create a vsc");
        $oVSC = new VSCProfile("MTM1", MTMEnv::$serial_of_msc1 . "MTM", "WPA2(AES/CCMP)", "802.1X");
        $sRetCode = $oVSC->create4MTM();
        if ($sRetCode == FALSE) {
            Step::error("fail to create a new VSC with Access Control disabled");
            return FAIL;
        } else {
            Step::ok("success to create a new VSC with Access Control disabled");
        }

        /*
         * 3.    Enable Wireless Mobility options and save the VSC.  An error should be generated.
         */
        Step::start("Enable Wireless Mobility options and save the VSC");
        $sRetCode = $this->enableMobilityBySelenium();
        if ($sRetCode != TRUE) {
            Step::error("An error does not show up");
            return FAIL;
        } else {
            Step::ok("An error is generated");
        }

        /*
         * 4.    Install/re-install L2/L3 mobility License 
         */
        Step::start("Install/re-install L2/L3 mobility license");
        $sRetCode = $this->installMobilityLicense();
        if ($sRetCode == FALSE) {
            Step::error("fail to install/re-install L2/L3 License");
            return FAIL;
        } else {
            Step::ok("Install/re-install L2/L3 License is ok");
        }
        /*
         * 5.    Verify that Wireless Mobility options are now available for configuration 
         */
        Step::start("Verify that Wireless Mobility options are now available for configuration ");
        $sRetCode = $this->enableMobilityBySelenium();
        if ($sRetCode == TRUE) {
            Step::error("Wireless Mobility options are NOT  available for configuration, SHOULD BE AVAILABLE");
            return FAIL;
        } else {
            Logger::toAll("Wireless Mobility options are now available for configuration");
            Step::ok();
        }

        Utility::banner("end");

        return PASS;
    }

    public function enableMobilityBySelenium() {
        //Start the selenium server and open up the browser 
        WEB::startBrowser($this->sMscIP, $this->oController->GetProductName());
        //Login to the device's home.asp page
        WEB::login();
        $oSeleniumDriver = WEB::getSelenium();
        Logger::toAll($this->sMscIP . "/centcfg/vsc_edit.asp?entity=vsc&selector=2");
        $oSeleniumDriver->open("centcfg/vsc_edit.asp?entity=vsc&selector=2");
        $oSeleniumDriver->click("l3Mobility");
        sleep(5);
        $oSeleniumDriver->click("add-2");
        $oSeleniumDriver->click("add");
        sleep(10);
        $sRetCode = $oSeleniumDriver->isTextPresent("A license must be installed");
        WEB::logout();
        WEB::closeBrowser(TRUE);
        return $sRetCode;
    }

}

?>