<?php
namespace Application\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DateTime;

class DateTimeStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    protected $format;

    /**
     * Handling saving and retrieving datetime values from forms
     * @param string $format PHP date format to hydrate from and extract to
     */
    public function __construct($format)
    {
        $this->format = $format;
    }

    public function extract($value)
    {
        if($value instanceOf DateTime) {
            return $value->format($this->format);
        } else {
            return '';
        }
    }

    public function hydrate($value)
    {
        if(is_null($value) || $value == '') {
            return '';
        }

        $datetime = DateTime::createFromFormat($this->format, $value);
        
        if($datetime === false) {
            return '';
        }

        return $datetime;
    }

}