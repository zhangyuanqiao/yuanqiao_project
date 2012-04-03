<?php
require_once ('TracedParameter.php');

/**
 * @author zhayuanq
 * @version 1.0
 * @created 15-Mar-2012 1:08:49 PM
 */
class EventStub
{

	public $parameter_name_list_for_event;
	public $traced_paramters;
	public $m_TracedParameter;

	function __construct()
	{
        $this->traced_paramters=array();
        $this->parameter_name_list_for_event=array("type"=>"-t ","scID"=>"-s","sta"=>"-S");
	}

	function __destruct()
	{
	}



	/**
	 * 
	 * @param trace_parameter
	 */
	public function addTracedParameter(TracedParameter $trace_parameter)
	{
        array_push($this->traced_paramters,$trace_parameter);
	}

	/**
	 * 
	 * @param para_name
	 */
	public function getParameter($para_name)
	{
        foreach($this->traced_paramters as $key =>$trace_parameter )
        {
            if($trace_parameter->name==$para_name)
            {
                return   $trace_parameter->getParameter();
            }
        }
	}

	/**
	 * 
	 * @param para_name
	 * @param para_value
	 */
	public function setParameter($para_name, $para_value)
	{
        foreach($this->traced_paramters as $key =>$trace_parameter )
        {
            if($trace_parameter->name==$para_name)
            {
                return   $trace_parameter->setParameter($para_value);
            }
        }
	}

	public function createEvent()
	{
        // addevent -t 2 -S 00:00:00:11:22:33 -a 00:00:00:11:22:33 -l eth0 -b 00:00:00:11:22:33
        $command="addevent "; 
        foreach($this->parameter_name_list_for_event as $name => $reduced_name)
        {
            $command=$command.$reduced_name."  ";  
            $command=$command.$this->getParameter($name)." ";
        }
        
        return  $command;
	}

}
?>