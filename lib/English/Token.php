<?php
/**
* English
*
* by Chris Alexander
*/

/**
* A token from English
*/
class English_Token
{
	/**
	* The value of this token
	*
	* @var string
	*/
	protected $value;
	
	/**
	* Constructs the token
	*/
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	/**
	* Renders this token as a string
	*/
	public function __tostring()
	{
		return $this->value;
	}
}