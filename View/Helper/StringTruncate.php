<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Exception\InvalidArgumentException;

class StringTruncate extends AbstractHelper
{
	const MIN_LENGTH = 5;
	
	/**
	 * return a given string truncated to x characters 
	 * @param string $value - string value to truncate
	 * @param int $returnLength - final length of returned string
	 * @param string $append - string to append to returned value to identify truncation
	 */
	public function __invoke($value, $returnLength, $append = null)
	{
		if (strlen($value) < $returnLength)
			return $value;
		
		if ($returnLength < self::MIN_LENGTH)
			return $value;
		
		$splitLength = is_null($append) ? $returnLength : $returnLength - strlen($append);
		
		$returnValue = substr($value, 0, $splitLength);
		
		if (!is_null($append))
			$returnValue = $returnValue . $append;
		
		return $returnValue;
	}
}