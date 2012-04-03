<?php 
require_once ("VSCProfile.php");
require_once ('MTMEnv.php');
require_once ('Group.php');
require_once ('Utility.php');
require_once ('User.php');

define('FAIL', "failed");
define('PASS', "passed");
define('NA', "na");
define('WCB_IP', "192.168.5.200");
define('TEST_CONFIGURATION', "test_configuration");
define('AP1', "AP1");
define('AP2', "AP2");
define('AP1_AP2', "AP1_AP2");
define('NONE', "NONE");
define('SWITCH_IP', "172.16.254.2");

/**
 * abstract class for test suite MTM. It is a subclass of Test.
 * Main purpose: to create the test enviroments.(software env and hardware env)
 * @author Yuanqiao Zhang yuanqiao.zhang@hp.com QA
 * @version 1.0
 * @updated 29-Mar-2012 11:40:09 AM
 */
abstract class MTMTest extends Test
{

    protected $sHomeVlanForAp1;
    protected $sHomeVlanForAp2;
    protected $sUserForHomeAp1;
    protected $sUserForHomeAp2;
    protected $sRadiusProfile;
    protected $aoApList;
    protected $aoNetworkProfileList;
    protected $aoUserList;
    protected $aoVscList;
    protected $aoWcbList;
    protected $asVlanList;
    protected $bDoNeedCleanup;

    /**
	 * this attribute is to indicate that the running case state.
	 * Enabled means that it is the last MTM test case.
	 * Disabled otherwise.  
	 */
    protected $bLastCaseState;

    /**
     * to indicate that the test enviroment state. READY OR NOTREADY. Normally after
     * we execute the preTest,the state should be READY, otherwise NOTREADY
     */
    protected $bTestEnvState;

    /**
	 * include the following info needed to serialize
	 * network profile list
	 * vlan list
	 * user list
	 * vsc list
	 * group list
	 */
    protected $fileTestConfiguration;

    /**
	 * the 1st MAP 
	 */
    protected $oAccessPoint;

    /**
     * The first controller. master or msc1
     */
    protected $oController;
    protected $oUserProfile;
    protected $oWcb;
    protected $sLicenseType;

    /**
     * the ip of MSC . If there is just 1 msc in test enviroment.
     */
    protected $sMscIP;
    protected $aoControllerList;
    protected $aoGroupList;
	public $network_profile_list;
	public $vsc_list;

    /**
	 * Construction of obj
	 * 
	 * @param framework
	 * @param testid
	 * @param testscript
	 */
    public function __construct($framework, $testid, $testscript) {
        parent::__construct($framework, $testid, $testscript);
        $this->job = $this->framework->GetJob();
        $this->scenario = $this->job->GetScenario();


        $aScenarioRoleList = $this->scenario->GetScenarioRole();
        $this->aoApList = $this->scenario->GetApList();
        $this->oAccessPoint = $this->aoApList[1]; 
        
        if (in_array('MASTER', $aScenarioRoleList)) {
            Logger::ToConsoleAndFile("This is a teaming scenario.\n", debug);
            $this->oController = $this->scenario->GetMaster();
            $this->aoControllerList=$this->scenario->GetControllerList();      
        } else {
            $this->oController = $this->scenario->GetDevice('MSC');
//            $controller_mngmt_ip = $this->oController->GetManagementInterfaceIP();
//            $serial_number = $this->oController->GetSerialNumber();
//            $product_name =  $this->oController->GetProductName();

//            $this->oController = $controllerObject;
//            $this->oAccessPoint = $this->aoApList[1];

//            MTMEnv::$msc1 = $controllerObject;
//            MTMEnv::$msc1_ip = $controller_mngmt_ip;
//            MTMEnv::$serial_of_msc1 = $serial_number;
//            MTMEnv::$product_name_of_msc1 = $product_name;
//            MTMEnv::$number_of_msc = 1;
        }
        
        $this->sMscIP=$this->oController->GetManagementInterfaceIP(); 

        $this->aoWcbList = $this->scenario->GetWCBList();
        $this->oWcb = $this->aoWcbList[1];
        $this->sMscIP = $this->oController->GetManagementInterfaceIP();
        VSCProfile::$sMscIP=$this->sMscIP;
        Group::$sMscIP = $this->sMscIP;
        User::$sMscIP  = $this->sMscIP;
        Utility::$msc_ip = $this->sMscIP;
//        Utility::$product_name = $this->oController->GetProductName();

        $this->aoNetworkProfileList = array();
        $this->aoVscList = array();
        $this->asVlanList = array();
        $this->group_list = array();
        $this->aoUserList = array();
        $this->aoApList = array();

        $this->fileTestConfiguration = array();
        $this->test_env_state = $this->getConfiguration();
    }

    /**
     * Clean up the particular setting for next test run
     */
    function Cleanup() {
        if (TRUE) 
        {
            Group::moveAPToDefaultGroup($this->scenario->GetDevice('AP1')->GetInterface("eth0")->GetMAC());
            Group::moveAPToDefaultGroup($this->scenario->GetDevice('AP2')->GetInterface("eth0")->GetMAC());
            Group::deleteGroup("AREA1-A");
            Group::deleteGroup("AREA1-B");
            //$this->deleteAllGroups();
            $this->deleteAllVlans();
            $this->deleteAllNetworkProfiles();
            $this->deleteAllUsers();
            $this->deleteAllUserAccountProfiles();
            $this->deleteAllVSCs();
            unlink("test_configuration");
        }
    }

    /**
     * Install mobility license
     */
    public function installMobilityLicense() {

        $sRetCode = $this->oController->UpdateDeviceLicense(MOBILITY, 10);
        if ($sRetCode == 1) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * remove all licenses
     */
    public function removeMobilityLicense() {
        $retCode = SOAP::sendCommand($this->sMscIP, "RemoveLicense");
        if ($retCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
	 * add a new network profile
	 * 
	 * @param name
	 * @param state
	 * @param vlanID
	 */
    public function addNetworkProfile($sName, $bState, $iVlanID) {
        $sSoapCmd = "AddNetworkProfile name=$sName vlanIDState=$bState vlanID=$iVlanID\n";

        $retCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($retCode != 0) {
            return FALSE;
        }
        return TRUE;
    }
	
	/*
	 *	Delete the network assignation. 
	 *	@param sLevel
	 * 	@param sEntityName
	 *	@param sNetworkDef
	*/
	public function deleteNetworkAssignation($sLevel, $sEntityName, $sNetworkDef) {
	    $sSoapCmd = "ControlledNetworkDeleteNetworkAssignation level=$sLevel entityName=$sEntityName networkDef=$sNetworkDef\n";

        $retCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($retCode != 0) {
            return FALSE;
        }
        return TRUE;
 
	}
    /**
     * Delete all network profiles
     */
    public function deleteAllNetworkProfiles() {
        $sSoapCmd = "DeleteAllNetworkProfiles \n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
	 * Map a vlan to a port
	 * AddVLAN(networkProfileName, portId, assignationMode, ipAddress, ipMask,
	 * ipGateway, natState) => ()
	 * Example:
	 * AddVLAN("myVLANNetwork","Internet_Port","IP_Static","192.168.10.10","255.255.
	 * address, and enabled it.
	 * AddVLAN("myVLANNetwork","Internet_Port","IP_Static","192.168.10.10","255.255.
	 * 255.0","192.168.10.254","Disabled") Add a VLAN on Internet Port with a static
	 * IP address, and disable it.
	 * AddVLAN("myVLANNetwork","Internet_Port","DHCP_IP","","","","Enabled") Add a
	 * VLAN on Internet Port that will get its IP address through DHCP, and enable it.
	 * 
	 * AddVLAN("myVLANNetwork","Internet_Port","DHCP_IP","","","","Disabled") Add a
	 * VLAN on Internet Port that will get its IP address through DHCP, and disable it.
	 * 
	 * AddVLAN("myVLANNetwork","Internet_Port","No_IP","","","","Enabled") Add a VLAN
	 * on Internet Port that will have no IP address, and enable it.
	 * 
	 * @param sNpName
	 * @param sPortID
	 * @param sAssignMode
	 * @param sIpAddress
	 * @param sIpMask
	 * @param sIpGateway
	 * @param bNatState
	 */
    public function addVlan($sNpName, $sPortID, $sAssignMode, $sIpAddress, $sIpMask, $sIpGateway, $bNatState) {
        $sSoapCmd = "AddVLAN networkProfileName=$sNpName portId=$sPortID assignationMode=$sAssignMode ipAddress=$sIpAddress ipMask=$sIpMask  ipGateway=$sIpGateway natState=$bNatState";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * let controllers to form a domain
     */
    public function createMobilityDomain() {
        $sSoapCmd = "UpdateMobilityControllerDiscovery state=Enabled primaryControllerState=Enabled primaryControllerAddress=\"\" ";
        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }

        if (MTMEnv::$number_of_msc > 1) {
            $aoControllerList = $this->scenario->GetControllerList();
            foreach ($aoControllerList as $index => $controller) {
                //we reserved the 1st controller as premary,
                if ($index == 1) {
                    continue;
                }

                $sControllerIp = $controller->GetManagementInterfaceIP();
                $sSoapCmd = "UpdateMobilityControllerDiscovery state=Enabled primaryControllerState=Disabled primaryControllerAddress=$this->sMscIP";
                $sRetCode = SOAP::sendCommand($sControllerIp, $sSoapCmd);
                if ($sRetCode != 0) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
	 * add a local network
	 * 
	 * @param oAP
	 * @param iVlanID
	 */
    public function addLocalNetwork($oAP, $iVlanID) {
        /*
         * soap command:
         * ControlledNetworkAddNetworkAssignation(level, entityName, networkDef) => ()
          ControlledNetworkLevel_t level
          string_t entityNameOn AP level, the entity name is the AP's MAC address
          string_t networkDef
         */
        $sApMac = $oAP->GetInterface('eth0')->GetMAC();
        $sSoapCmd = "ControlledNetworkUpdateL3MobilitySubnetListInheritance level=AP entityName=$sApMac state=Disabled\n"
                . "ControlledNetworkAddNetworkAssignation level=AP entityName=$sApMac networkDef=$iVlanID\n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
	 * add a local network (Group Level)
	 * 
	 * @param sGrpName
	 * @param sNpName
	 */
    public function addLocalNetworkGroupLevel($sGrpName, $sNpName) {
        $sSoapCmd = "ControlledNetworkAddNetworkAssignation level=Group entityName=$sGrpName networkDef=$sNpName \n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
	 * prepare for the test enviroment 
	 */
    public function preTest() {
        if ($this->test_env_state == TRUE) {
            return TRUE;
        }

        Utility::turnDebugOff();

        $this->installMobilityLicense();

        $oAP = $this->scenario->GetDevice('AP1');
        $oGroup1 = new Group("AREA1-A");
        $oGroup1->containAP($oAP->GetInterface('eth0')->GetMAC());
        array_push($this->aoGroupList, $oGroup1);

        $oAP = $this->scenario->GetDevice('AP2');
        $oGroup2 = new Group("AREA1-B");
        $oGroup2->containAP($oAP->GetInterface('eth0')->GetMAC());
        array_push($this->aoGroupList, $oGroup2);

        $this->getVLANs();

        $this->setDHCPD();
        $this->createNetworks();

        /*
         * bind VSCs to both groups 
         */
        foreach ($this->aoVscList as $key => $oVSC) {
            $oGroup1->bindToVSC($oVSC->getVscName());
            $oGroup2->bindToVSC($oVSC->getVscName());
        }

        /*
         * get the home vlan for AP1,AP2
         * get a user1 with home vlan for AP1,AP2        *  
         */
        foreach ($this->asVlanList as $vlan => $sPortID) {

            $sFirst2Digit = substr($vlan, 0, 2);
            if ($sFirst2Digit == "21") {
                $this->sHomeVlanForAp1 = "VLAN" . $vlan;
                $this->sUserForHomeAp1 = "MTM_user_id_VLAN" . $vlan;
            }

            if ($sFirst2Digit == "23") {
                $this->sHomeVlanForAp2 = "VLAN" . $vlan;
                $this->sUserForHomeAp2 = "MTM_user_id_VLAN" . $vlan;
            }
        }

        system("banner $home_vlan_for_ap1");
        $home_vlan_for_ap1 = "VLAN" . "21" . $this->framework->GetTestbed()->GetId();
        $home_vlan_for_ap1 = "VLAN" . "23" . $this->framework->GetTestbed()->GetId();

        $this->addLocalNetwork($this->scenario->GetDevice('AP1'),$home_vlan_for_ap1);
        $this->addLocalNetwork($this->scenario->GetDevice('AP2'),$home_vlan_for_ap2);

        /*
         * config the local switch.the port 1-7 is the member of any vlan. 11**-19**,21**-29** 
         */

        $this->configLocalSwitch();

        $this->syncAPs();

        $this->saveConfiguration();
    }

    /**
	 * The user login by the WCB
	 * 
	 * @param sUserName
	 * @param sPassWord
	 * @param sDefaultPage
	 */
    public function userLogin($sUserName, $sPassWord, $sDefaultPage) {
        $temparray = array();
        $sWcbIP=$this->oWcb->GetManagementInterfaceIP(); 
//      $this->CallBashFunction("g_do_login", "LOCAL NO_ACCT QUICK_LOGIN WCB_IP $sUserName $sPassWord $sDefaultPage", $temparray, $this->oController, $this->oAccessPoint, null);
        $this->CallBashFunction("g_do_login", "LOCAL NO_ACCT QUICK_LOGIN $sWcbIP $sUserName $sPassWord $sDefaultPage", $temparray, $this->oController, $this->oAccessPoint, null);
    }

    /**
	 * wcb assocates with AP,and get authenticated
	 * 
	 * @param sAuthType
	 * @param sUserName
	 * @param sPassWord
	 * @param sSsid
	 * @param sEapType
	 */
    public function wcbAssociateAndAuth($sAuthType, $sUserName, $sPassWord, $sSsid, $sEapType) {
        $oWCB = null;

        foreach ($this->job->getScenario()->getWCBList() as $index => $clientObj) {
            if ($clientObj->GetProductName() == 'M111') {
                Logger::ToConsoleAndFile("Found the WCB client");
                $oWCB = $clientObj;
                $this->oWcb = $oWCB;
                break;
            }
        }

        if ($oWCB === null) {
            Logger::ToConsoleAndFile("Could not find a WCB to use as wireless client. One is needed. Not_applicable");
            return FALSE;
        }

        $asConnectionProperties = array();
        $asConnectionProperties['authType'] = $sAuthType;
        $asConnectionProperties['username'] = $sUserName;
        $asConnectionProperties['password'] = $sPassWord;
        $asConnectionProperties['ssid']     = $sSsid;
        $asConnectionProperties['eapType'] =  $sEapType;

        $oWCB->configureWCB($asConnectionProperties);
        // Do not check the ssid in the ap's driver'
        if ($oWCB->Associate(TRUE) == '0') {

            Logger::ToConsoleAndFile("connecting client using PEAP version 0");

            if ($oWCB->Authenticate() != '0') {
                Logger::ToConsoleAndFile("Skipping internet access check");
                return FALSE;
            }

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * get the VLAN configuration of senario
     */
    public function getVLANs() {
        $aInterfaceList = array('eth0' => 'Internet_Port', 'eth1' => 'LAN_Port');
        $this->asVlanList = array();

        Logger::ToConsoleAndFile("
        /*
        Get all tagged network patched to the controller. for each create the following that egress to that network:
            -1 VSC with binding egress to network
            -1 user (user account + account profile) with attribute egress vlan to network
            -vlan on the controller
            -network profile on the controller
        
        Untagged vlan and network profile are present by default in the controller config - no need to create them
        Also no need to create a dhcp server for these network
            -controller is the dhcp server for lan side
            -nat device is the dhcp server for wan side  

        N.B. Some VSC and user will not be used during the tests 
            - i.e VSC and user with egress network to AP management network (the user will be simply locally bridge) 
        */
        ", debug);

        foreach ($aInterfaceList as $sInterfaceName => $sPortID) {

            foreach ($this->oController->GetInterface($sInterfaceName)->GetVirtualInterfaceList() as $index => $oVirtualInterface) {
                $sVlan = $oVirtualInterface->GetVlanInfo()->GetId();
                if (!in_array($sVlan, $this->asVlanList) && $oVirtualInterface->GetTagging() != "untagged") {
                    $this->asVlanList[$sVlan] = $sPortID;
                }
            }
        }
    }

    public function rematerilize() {
        
    }

    /**
     * start extern DHCP service in testbed
     */
    public function setDHCPD() {
        // Create the vlan interface on the testbed
        Logger::ToConsoleAndFile("The testbed IP on the VLAN will be as follow", debug);
        Logger::ToConsoleAndFile("    on the controller wan side (testbed eth0)", debug);
        Logger::ToConsoleAndFile("        -172.(last_two_digit_of_vlanID).254.1", debug);
        Logger::ToConsoleAndFile("    -on the controller lan side (testbed eth1)", debug);
        Logger::ToConsoleAndFile("        `192.168.(last_two_digit_of_vlanID).254.1", debug);

        Logger::toConsoleAndFile("Deleting existing DHCPD.conf file...");
        system("rm -f ~/tests/framework/services/DHCPD.conf", $RC);

        Logger::toConsoleAndFile("Delete returned a $RC.");
        //Get the dhcp service running on the testbed or create a new one
        $oDhcpServer = $this->framework->GetTestbed()->CreateDHCPD();

        foreach ($this->asVlanList as $vlanId => $port) {

            if ($port == "LAN_Port") {
                $sIpPrefix = "192.168.";
                $sTestbedIpSubnet = "192.168.1";
            } else {
                $sIpPrefix = "172.16.";
            }

            if ($vlanId != null) {
                Logger::ToConsoleAndFile("This is a tagged network-vlanId='$vlanId'. Need to create a virtual interface on the testbed for it", debug);

                $sFirst2VlanDigit = substr($vlanId, 0, 2);
                $sTestbedIpSubnet = $sIpPrefix . $sFirst2VlanDigit;
                $sTestbedIp = $sTestbedIpSubnet . ".254";

                $oTestbedVirtualInterface = $this->framework->GetTestbed()->GetVirtualInterface($vlanId);
                $oTestbedVirtualInterface->SetIP($sTestbedIp);
                $oTestbedVirtualInterface->GetVlanInfo()->SetdnsServer($sTestbedIp);
                $oTestbedVirtualInterface->GetVlanInfo()->SetDefaultGateway($sTestbedIp);
                $oTestbedVirtualInterface->GetVlanInfo()->SetBitMask('24');
                $this->framework->GetTestbed()->UnmaterializeVirtualInterface($vlanId);
                $this->framework->GetTestbed()->MaterializeVirtualInterface($vlanId);

                $oDhcpServer->AddSubnet($sTestbedIpSubnet . ".0", "255.255.255.0", $sTestbedIp, $sTestbedIpSubnet . ".20", $sTestbedIpSubnet . ".30", $sTestbedIpSubnet . ".255", "subnet" . $sFirst2VlanDigit, $sTestbedIp);
            }
        }
        $oDhcpServer->PrintCurrentConfig();
        $oDhcpServer->Start("");
    }

    /**
	 * create network profiles, VSCs,User, and vlans 
	 */
    public function createNetworks() {
        $aInterfaceList = array('eth0' => 'Internet_Port', 'eth1' => 'LAN_Port');
        Logger::ToConsoleAndFile("
        /*
        Get all tagged network patched to the controller. for each create the following that egress to that network:
            -1 VSC with binding egress to network
            -1 user (user account + account profile) with attribute egress vlan to network
            -vlan on the controller
            -network profile on the controller
        
        Untagged vlan and network profile are present by default in the controller config - no need to create them
        Also no need to create a dhcp server for these network
            -controller is the dhcp server for lan side
            -nat device is the dhcp server for wan side  

        N.B. Some VSC and user will not be used during the tests 
            - i.e VSC and user with egress network to AP management network (the user will be simply locally bridge) 
        */
        ", debug);

        $iNumOfVscForLan = 0;
        $iNumOfVscForWan = 0;
        $iMaxVsc = 3;
        foreach ($aInterfaceList as $sInterfaceName => $sPortID) {

            foreach ($this->oController->GetInterface($sInterfaceName)->GetVirtualInterfaceList() as $index => $oVirtualInterface) {
                $iVlanID = $oVirtualInterface->GetVlanInfo()->GetId();
                if (!in_array($iVlanID, $this->asVlanList) && $oVirtualInterface->GetTagging() != "untagged") {
                    //array_push($this->vlan_list,$vlan_id => $sPortID);
                    $sNpName = "VLAN" . $iVlanID;
                    $this->addNetworkProfile($sNpName, "Enabled", $iVlanID);
                    $this->addVlan($sNpName, $sPortID, "DHCP_IP", "1.1.1.1", "1.1.1.1", "1.1.1.1", "Enabled");
                    array_push($this->aoNetworkProfileList, $sNpName);

                    //NOT set the egree vlan to vsc yet
                    $oVSC = new VSCProfile("MTM_VLAN_" . $iVlanID, "MTM_VLAN_" . $iVlanID,"WPA2(AES/CCMP)","802.1X");
                    $oVSC->create4MTM();

//                    $vsc=new VSC("MTM_VLAN_".$vlan_id,"MTM_VLAN_".$vlan_id,"WPA2(AES/CCMP)","802.1X");
//                    $vsc->updateAccessControlState("Disabled");
//                    $vsc->commit();
//                    $vsc->enableL3Mobility($vsc->name);
                    $oVSC->updateL3MobilityState("Enabled");
                    array_push($this->aoVscList, $oVSC);
                    //$vscOBJ->setEgressNetworkProfileName($network_profile_name);                    
                    //Create a user that will egress to that network (vlan ID)
                    $oUser = new User("MTM_user_id_" . $sNpName, "MTM_user_id_" . $sNpName, "Enabled");
                    //Utility::insertBreakPoint("user");

                    $oUser->updateEgressVlan($iVlanID);
                    //Utility::insertBreakPoint("$vlan_id EGRESS");


                    array_push($this->aoUserList, $oUser);

                    if ($sPortID == "Internet_Port") {
                        $iNumOfVscForWan++;
                        if ($iNumOfVscForWan >= $iMaxVsc) {
                            break;
                        }
                    }
                    if ($sPortID == "LAN_Port") {
                        $iNumOfVscForLan++;
                        if ($iNumOfVscForLan >= $iMaxVsc) {
                            break;
                        }
                    }
                    
                    
                }
            }
        }
    }

    /**
	 * local switch configure
	 * 
	 * @param sSwitchIP
	 * @param iVlanID
	 * @param bTagState
	 * @param sPortID
	 */
    public function addPortToVlan($sSwitchIP, $iVlanID, $bTagState, $sPortID) {
        $sCmd = "config\n";
        $sCmd = $sCmd . "vlan $iVlanID $bTagState $sPortID\n";

        $filename = "/tmp/automation_logs/thistest8021x.cli";
        @unlink($filename);            // Delete the file
        $fp = fopen($filename, 'w');
        fwrite($fp, $sCmd);
        fclose($fp);

        // Send command to switch using system call  (parameters, switch IP and a command file)
        exec("/home/automation/tests/switchscripts/procurve_telnet.sh {$sSwitchIP} {$filename}", $output_lines_array, $RC);
        Logger::ToAll(implode("\n", $output_lines_array));
        return $RC;
    }

    /**
	 * Enable/Disable radio
	 * 
	 * @param oAP
	 * @param bRadioState
	 */
    public function apRadioControl($oAP, $bRadioState) {
        //ControlledNetworkUpdateRadioChannelAndMode(level, entityName, radioId, productType, radioState, autoChannelState, channel, radioOperatingMode, radioPhyType)
        //ControlledNetworkUpdateRadioChannelAndMode("Group", "Default Group", "Radio_1", "MSM320", "Enabled", "Enabled", "", "Access_Point", "802.11a");
        $sProductType = $oAP->GetProductName();

        if ($sProductType == "MSM410") {
            $sSoapCmd = "ControlledNetworkUpdateRadioInheritance level=AP entityName={$oAP->GetInterface('eth0')->GetMAC()} productType=$sProductType state=Disabled\n" .
                    "ControlledNetworkUpdateRadioChannelAndMode level=AP entityName={$oAP->GetInterface('eth0')->GetMAC()} radioId=Radio_1 productType=$sProductType radioState=$bRadioState autoChannelState=Enabled channel=\"\" radioOperatingMode=Access_Point radioPhyType=802.11b+g \n";
        }

        if ($sProductType == "MSM466") {
            $sSoapCmd1 = "ControlledNetworkUpdateRadioInheritance level=AP entityName={$oAP->GetInterface('eth0')->GetMAC()} productType=$sProductType state=Disabled\n" .
                    "ControlledNetworkUpdateRadioChannelAndMode level=AP entityName={$oAP->GetInterface('eth0')->GetMAC()} radioId=Radio_1 productType=$sProductType radioState=$bRadioState autoChannelState=Enabled channel=\"\" radioOperatingMode=Access_Point radioPhyType=802.11n+a \n";

            $sSoapCmd2 = "ControlledNetworkUpdateRadioInheritance level=AP entityName={$oAP->GetInterface('eth0')->GetMAC()} productType=$sProductType state=Disabled\n" .
                    "ControlledNetworkUpdateRadioChannelAndMode level=AP entityName={$oAP->GetInterface('eth0')->GetMAC()} radioId=Radio_2 productType=$sProductType radioState=$bRadioState autoChannelState=Enabled channel=\"\" radioOperatingMode=Access_Point radioPhyType=802.11n+b+g \n";
            $sSoapCmd = $sSoapCmd1 . $sSoapCmd2;
        }


        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);

        $this->syncAPs();

        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * sync all APs
     */
    public function syncAPs() {
        $aoApList = $this->scenario->GetApList();
        $this->oController->WaitForAllSynched($aoApList);
    }

    /**
	 * To check if the expected content is in the specified page.
	 * 
	 * @param sURL    the page we are checking
	 * @param sContent    the content expected, a string 
	 */
    public function checkContentInPage($sURL, $sContent) {
        //Start the selenium server and open up the browser 
        WEB::startBrowser($this->sMscIP, $this->oController->GetProductName());
        //Login to the device's home.asp page
        WEB::login();
        $oSeleniumDriver = WEB::getSelenium();
        Logger::toAll($this->sMscIP . "/stat/l3_overview.asp");
        $oSeleniumDriver->open($sURL);
        sleep(10);

        $sRetCode = TRUE;
        $sRetCode = $oSeleniumDriver->isTextPresent($sContent);
        //$sRetCode=$selenium_driver->isTextPresent("Data");  
        WEB::logout();
        WEB::closeBrowser(TRUE);
        return $sRetCode;
    }

    /**
     * get the test enviroment
     */
    public function getConfiguration() {
        $sSerializedData = file_get_contents(TEST_CONFIGURATION);
        $this->fileTestConfiguration = unserialize($sSerializedData);
        if ($this->fileTestConfiguration["pre_test_state"] != "READY") {
            return FALSE;
        }

        $this->aoNetworkProfileList = $this->fileTestConfiguration["np"];
        $this->aoVscList = $this->fileTestConfiguration["vsc"];
        $this->aoUserList = $this->fileTestConfiguration["user"];
        $this->group_list = $this->fileTestConfiguration["group"];
        $this->sHomeVlanForAp1 = $this->fileTestConfiguration["vlan1"];
        $this->sHomeVlanForAp2 = $this->fileTestConfiguration["vlan2"];
        $this->sUserForHomeAp1 = $this->fileTestConfiguration["user1"];
        $this->sUserForHomeAp2 = $this->fileTestConfiguration["user2"];

        return TRUE;
    }

    /**
     * save the test enviroment to a file in order that others test use it.
     */
    public function saveConfiguration() {
        $this->fileTestConfiguration["np"] = $this->aoNetworkProfileList;
        $this->fileTestConfiguration["vsc"] = $this->aoVscList;
        $this->fileTestConfiguration["user"] = $this->aoUserList;
        $this->fileTestConfiguration["group"] = $this->group_list;
        $this->fileTestConfiguration["vlan1"] = $this->sHomeVlanForAp1;
        $this->fileTestConfiguration["vlan2"] = $this->sHomeVlanForAp2;
        $this->fileTestConfiguration["user1"] = $this->sUserForHomeAp1;
        $this->fileTestConfiguration["user2"] = $this->sUserForHomeAp2;
        $this->fileTestConfiguration["pre_test_state"] = "READY";

        $sSerializedData = serialize($this->fileTestConfiguration);
        file_put_contents(TEST_CONFIGURATION, $sSerializedData);
    }

    /**
	 * 
	 * NONE ,both aps' radios are off
	 * AP1,AP1's radio is on
	 * AP2,AP2's radio is on
	 * AP1_AP2,AP1's radio adn AP2' ARE on
	 * 
	 * @param sAP
	 */
    public function turnOnAP($sAP) {
        if ($sAP == NONE) {
            $this->apRadioControl($this->scenario->GetDevice('AP1'), "Disabled");
            $this->apRadioControl($this->scenario->GetDevice('AP2'), "Disabled");
        }

        if ($sAP == AP1) {
            $this->apRadioControl($this->scenario->GetDevice('AP1'), "Enabled");
            $this->apRadioControl($this->scenario->GetDevice('AP2'), "Disabled");
        }

        if ($sAP == AP2) {
            $this->apRadioControl($this->scenario->GetDevice('AP1'), "Disabled");
            $this->apRadioControl($this->scenario->GetDevice('AP2'), "Enabled");
        }

        if ($sAP == AP1_AP2) {
            $this->apRadioControl($this->scenario->GetDevice('AP1'), "Enabled");
            $this->apRadioControl($this->scenario->GetDevice('AP2'), "Enabled");
        }
    }

    /**
     * add each port to any vlan
     */
    public function configLocalSwitch() {

        foreach ($this->asVlanList as $iVlanID => $sPortID) {
            for ($iPortNum = 1; $iPortNum < 8; $iPortNum++) {
                $this->addPortToVlan(SWITCH_IP, $iVlanID, "tagged", $iPortNum);
            }
        }
    }

    public function deleteAllGroups() {
        $sSoapCmd = "ControlledNetworkUpdateRadioChannelAndModeControlledNetworkDeleteAllGroups \n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Function as the name says
     */
    public function deleteAllUserAccountProfiles() {
        $sSoapCmd = "DeleteAllAccountProfiles\n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Function as the name says
     */
    public function deleteAllUsers() {
        $sSoapCmd = "ControlledNetworkUpdateRadioChannelAndModeDeleteAllUserAccounts \n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Function as the name says
     */
    public function deleteAllVlans() {
        $sSoapCmd = "DeleteAllVLANs \n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Function as the name says
     */
    public function deleteAllVSCs() {
        $sSoapCmd = "ControlledNetworkUpdateRadioChannelAndModeDeleteAllVirtualSCs \n";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

	/**
	 * Main entry for test
	 */
	public function MyExecute()
	{
	}

	/**
	 * Checks to make sure a client's traffic is egressed on the correct VLAN.
	 * This is accomplished by attempting to ping the client from the testbed using
	 * the interface cooresponding  to that VLAN.
	 * 
	 * @param sVlanID
	 */
	public function verifyTrafficEgressOnRightPort($sVlanID)
	{

        $iVlanID=trim($sVlanID,"A..Za..z ");    
        echo $iVlanID;
        $sInterface = $this->framework->GetTestbed()->GetInterfaceName($iVlanID);
        $aExecOutput=array();
        $sWcbIP=$this->oWcb->GetManagementInterfaceIp();
        //Logger::toConsoleAndFile("Executing: ping -I $sInterface\.$iVlanID -c 5 $sWcbIP");
        Logger::toConsoleAndFile("Executing: ping -I $sInterface -c 5 $sWcbIP");
        exec("ping -I $sInterface -c 5 $sWcbIP",$aExecOutput,$sRC); 
        Logger::toConsoleAndFile($aExecOutput);
        if($sRC==0)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        } 
	}

	/**
	 * 
	 * @param sNpName
	 * @param sPortID
	 * @param sAssignMode
	 * @param sIpAddress
	 * @param sIpMask
	 * @param sIpGateway
	 * @param bNatState
	 */
	public function updateVlan($sNpName, $sPortID, $sAssignMode, $sIpAddress, $sIpMask, $sIpGateway, $bNatState)
	{
        $sSoapCmd = "UpdateVLAN networkProfileName=$sNpName  assignationMode=$sAssignMode ipAddress=$sIpAddress ipMask=$sIpMask  ipGateway=$sIpGateway natState=$bNatState";

        $sRetCode = SOAP::sendCommand($this->sMscIP, $sSoapCmd);
        if ($sRetCode != 0) 
        {
            return FALSE;
        }
        return TRUE;
	}

}

?>
