<?php
namespace Plants\Validator;

use Zend\Validator\AbstractValidator;
use DateTime;

class YearMonthIsValid extends AbstractValidator
{
	const MIN_YEAR = 1900; 
	
	/**
	 * Validate $value as a year (YYYY) or a year/month (YYYY-MM)
	 * @param mixed $value
	 * 
	 * @return boolean
	 */
	public function isValid($value)
	{
		$value 	= trim($value);
		$length = strlen($value);
		
		if ($length == 4)
		{
			return validateYear($value);
		}
		
		if ($length == 7)
		{
			$year 			= substr($value, 0, 4);
			$month 			= substr($value, 5, 7);
			
			$yearIsValid 	= $this->validateYear($year);
			$monthIsValid 	= $this->validateMonth($month);
			
			if ($yearIsValid && $monthIsValid)
				return true;
		}
		
		return false;
	}
	
	
	private function validateYear($year)
	{
		$now = new DateTime();
		$currentYear = $now->format('Y');
			
		if ($year >= self::MIN_YEAR && $year <= (int)$currentYear)
			return true;
		
		return false;
	}
	
	
	private function validateMonth($month)
	{
		$month = (int)$month;
		if ($month >=1 && $month <=12)
			return true;
		
		return false;
	}
	
}