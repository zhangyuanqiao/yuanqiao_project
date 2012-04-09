<?php
require_once ('MTMTest.php');
require_once ('Utility.php');
                                                                                 
/**
 * Test Objective: Verify that a single V-HNS client can associate to a foreign AP
 * and be successfully tunneled home. RADIUS returns a VLAN ID to identify the
 * user's home network. For reference documents refer to Test case 20272 Scenario
 * described in document: MTM with 1 controller and a roaming client Steps: 1.
 * Prepare the mobility domain as in the documentation 2. Associate the user to ,
 * that user uses VLAN20. a. VLAN20 is not local on Group2 b. User is away,
 * associating with AP2 that uses VLAN40 as local network c. Verify traffic is
 * tunneled correctly. 2. Associate that user now to AP1 a. User is home and
 * traffic egresses locally. Pass/Fail criteria: - Test passes if client
 * validation is successful. Test results interpretation: Critical
 * @author Yuanqiao Zhang Yuanqiao.Zhang@hp.com QA
 * @version 1.0
 * @updated 27-Feb-2012 10:20:04 AM
 */
class demo extends MTMTest
{
                                  
	/**
	 * Clean up the particular setting for next test run
	 */
	public function Cleanup()
	{
	}

	/**
	 * Main entry for test
	 */
	public function MyExecute()
	{
        Utility::turnDebugOn();
        //config switch
//        $this->addPortToVlan("172.16.254.2",2143,"tagged",1);
//        $this->addPortToVlan("172.16.254.2",2143,"tagged",2); 
//        $this->addPortToVlan("172.16.254.2",2143,"tagged",3); 
//        $this->addPortToVlan("172.16.254.2",2143,"tagged",4); 
//        
//        $this->addPortToVlan("172.16.254.2",2343,"tagged",1);
//        $this->addPortToVlan("172.16.254.2",2343,"tagged",2); 
//        $this->addPortToVlan("172.16.254.2",2343,"tagged",3);
//        $this->addPortToVlan("172.16.254.2",2343,"tagged",4); 
        
//        $this->apRadioControl($this->scenario->GetDevice('AP2'),"Disabled");
//        
//        Utility::insertBreakPoint(); 
//        
//        $this->apRadioControl($this->scenario->GetDevice('AP2'),"Enabled");
//        
//        $this->syncAPs();
        
//       if($this->checkPage()==TRUE)
//        {
//           Utility::insertBreakPoint("hahahahaha"); 
//        }
//        else
//        {
//            system("banner baga");
//        }
        

        $MYVSC=new VSC("V2","V2","WPA2(AES/CCMP)","802.1X");
        $MYVSC->commit();
        
//        $MYVSC=new VSC("V3","V3","WEP","HTML");
//        $my_wireless=new WirelessProtection();
//        $my_wireless->setWirelessProtectionType("WEP");
//        $my_wireless->setKeySource("STATIC");
//        $my_wireless->setKey(array( "c67347dd12a787b7a132f690d0", "c67347dd12a797b7a132f690d1", "c673a7dd12a787b7a132f690d2", "c67347dd16a787b7a132f690d3", "2", "HEX"));
//        $MYVSC->configureWirelessProtection($my_wireless); 
//        $MYVSC->commit();
        
        $MYVSC=new VSC("V3","V3","WPA(TKIP)","HTML");
        $my_wireless=new WirelessProtection();
        $my_wireless->setWirelessProtectionType("WPA(TKIP)");
        $my_wireless->setKeySource("STATIC");
        $my_wireless->setKey("26d4a5f7cf549710a7aae2db4e712027");
        $MYVSC->configureWirelessProtection($my_wireless); 
        $MYVSC->commit();
        
                
        
//        $MYVSC=new VSC("V4","V4","None","MAC");
//        $MYVSC->commit();
//        
//        $MYVSC=new VSC("V5","V5","None","HTML_MAC");
//        $MYVSC->commit();
//        
 //       $MYVSC=new VSC("V6","V6","WEP","802.1X");
//        $MYVSC->commit();
//        
//        $MYVSC=new VSC("V7","V7","WPA2(AES/CCMP)","802.1X");
//        $MYVSC->commit();
//        
//        $MYVSC=new VSC("V8","V8","WPA/WPA2","802.1X");
//        $MYVSC->commit();
//        $MYVSC=new VSC("V2","V2","WEP");
//        $MYVSC->commit();  
//        
//        $MYVSC=new VSC("V3","V3","WEP","HTML");   
//        $MYVSC->commit();  
//        
                 
        Utility::insertBreakPoint();   
        return "passed";
        
	}
    
    public function checkPage()
    {
        //Start the selenium server and open up the browser 
        WEB::startBrowser(MTMEnv::$msc1_ip, MTMEnv::$product_name_of_msc1);
        //Login to the device's home.asp page
        WEB::login();
        $selenium_driver=WEB::getSelenium();
        Logger::toAll(MTMEnv::$msc1_ip."/stat/l3_overview.asp");
        $selenium_driver->open("stat/l3_overview.asp");
        sleep(10);
        
        $return_code=TRUE;
        $return_code=$selenium_driver->isTextPresent($this->scenario->GetDevice('AP1')->GetInterface('eth0')->GetMAC());  
        //$return_code=$selenium_driver->isTextPresent("Data");  
        WEB::logout();
        WEB::closeBrowser(TRUE);
        return  $return_code;
    }

}
?>