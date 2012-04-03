<?php


/**
 * @author zhayuanq
 * @version 1.0
 * @created 15-Mar-2012 1:07:42 PM
 */
class TracedParameter
{

	/**
	 * For example: apIP ...
	 */
	public $name;
	private $para_type;
	private $para_value;

    /**
	 * 
	 * @param name
	 * @param para_value
	 */
    public function __construct($name, $para_value)
    {
        $this->name= $name;
        $this->para_value=$para_value;
    }
	function __destruct()
	{
	}



	public function getParameter()
	{
        return $this->para_value;
	}

	/**
	 * 
	 * @param item_value
	 */
	public function setParameter($item_value)
	{
        $this->para_value=$item_value;
	}



}
?>