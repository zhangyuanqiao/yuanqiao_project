<?php
require_once ('..\..\..\zhayuanq\Documents\MyScripts\mtm\mtm_always_home\MTMTest.php');

/**
 * Test Objective:
 * Verify behavior when RADIUS returns a VLAN ID that is not defined on a
 * controller in the network.  Consider the user at home and egress the client’s
 * traffic locally from the MAP using the VLAN ID returned by RADIUS.
 * Steps:
 * 1.	Configure a client profile of vlanuser99 in RADIUS that returns VLAN ID of
 * 99
 * 2.	Make sure that VLAN 99 is not defined as a network profile or an interface
 * on the controller.
 * 3.	Associate vlanuser99 to msm10-1.
 * 4.	Traffic is egressed locally at the MAP using VLAN 99.
 * Pass/Fail criteria:
 * 1.	Message must be logged that an unknown VLAN was returned
 * 2.	Traffic must be egressed locally from MAP using VLAN 99.
 * 
 * Test results interpretation: Major
 * @author yuanqiao
 * @version 1.0
 * @created 02-Apr-2012 2:25:34 PM
 */
class Unknown_VLAN_ID_returned_by_RADIUS_at_home extends MTMTest
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
	}

}
?>