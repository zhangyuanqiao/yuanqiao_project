<?php

/**
 * @author zhayuanq
 * @version 1.0
 * @created 27-Feb-2012 11:42:57 AM
 */
class Group {

    /**
     * the name of group
     */
    public $sName;
    public static $sMscIP;

    /**
     * ip address of msc if there is just 1 msc in the test enviroment.
     * Here, just for convient.
     */

    /**
	 * @param name    name of Group
	 * @param name    name of Group
	 * 
	 * @param name    name of Group
	 */
    public function __construct($sName) {
        $this->sName = $sName;
        $this->createGroup($sName);
    }

    function __destruct() {
        ;
    }

    /**
	 * alreay includes the feature:execute the sync
	 * 
	 * @param vsc_name
	 */
    public function bindToVSC($sVscName) {

        $sSoapCmd = "ControlledNetworkAddVirtualSCBinding grpName=$this->sName vscProfile=$sVscName egressNetworkState=Disabled networkProfileName=\"\" activeRadio=Radio_1_And_Radio_2 locationAwareGroup=$this->sName\n"
                . "ControlledNetworkExecuteAction level=Group entityName=$this->sName action=Synchronize\n";

        $sRetCode = SOAP::sendCommand(Group::$sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
	 * @param ap_mac
	 * @param ap_mac
	 * 
	 * @param ap_mac
	 */
    public function containAP($sApMac) {
        $sSoapCmd = "ControlledNetworkUpdateAPGroup macAddr=$sApMac grpName=$this->sName \n";

        $sRetCode = SOAP::sendCommand(Group::$sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
	 * @param name
	 * @param name
	 * 
	 * @param name
	 */
    public function createGroup($sGroupName) {
        $sSoapCmd = "ControlledNetworkAddGroup grpName=$sGroupName";

        $sRetCode = SOAP::sendCommand(Group::$sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
	 * @param ap_mac
	 * @param ap_mac
	 * 
	 * @param ap_mac
	 */
    public static function moveAPToDefaultGroup($sApMac) {
        $sSoapCmd = "ControlledNetworkUpdateAPGroup macAddr=$sApMac grpName=\"Default Group\" \n";

        $sRetCode = SOAP::sendCommand(Group::$sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
	 * @param group_name
	 * @param group_name
	 * 
	 * @param group_name
	 */
    public static function deleteGroup($sGroupName) {
        $sSoapCmd = "ControlledNetworkDeleteGroup grpName=$sGroupName \n";

        $sRetCode = SOAP::sendCommand(Group::$sMscIP, $sSoapCmd);
        if ($sRetCode != 0) {
            return FALSE;
        }
        return TRUE;
    }

}

?>