<?php
require_once ('..\..\..\zhayuanq\Documents\MyScripts\mtm\mtm_always_home\MTMTest.php');

/**
 * Test Objective:
 * Verify behavior when RADIUS does not return a VLAN attribute.  Consider the
 * user at home and egress traffic on the untagged interface of the MAP.
 * Steps:
 * 1.	Configure a client profile of novlanuser in RADIUS that does not have a
 * return attribute.
 * 2.	Remove the egress network defined on the vsc1 binding of msm10-1.
 * 3.	Associate novlanuser to vsc1 on msm10-1.
 * 4.	Traffic is egressed locally from msm10-1 using the untagged interface.
 * 
 * Pass/Fail criteria:
 * 1.	Traffic must be egressed locally from MAP.
 * 
 * Test results interpretation: Major
 * @author yuanqiao
 * @version 1.0
 * @updated 02-Apr-2012 2:28:33 PM
 */
class VLAN_ID_not_returned_by_RADIUS_at_home extends MTMTest
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