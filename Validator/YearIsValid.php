<?php
namespace Plants\Validator;

use Zend\Validator\AbstractValidator;
use DateTime;

class YearIsValid extends AbstractValidator
{
	const MIN_YEAR = 1900; 
	
	/**
	 * Validate $value as a year (YYYY) 
	 * @param mixed $value
	 * 
	 * @return boolean
	 */
	public function isValid($value)
	{
		$value = (int)$value;
		$now = new DateTime();
		$currentYear = $now->format('Y');
		
		if ($value >= self::MIN_YEAR && $value <= (int)$currentYear)
			return true;
		
		return false;
	}
	
}