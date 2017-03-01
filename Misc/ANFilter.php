<?php


class ANFilter
{
    /**
     * @var array
     */
    public static $searchTypes = [
        0 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        1 => '#ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        2 => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    ];

    /**
     * @var int
     */
    protected $searchType;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    public $friendlyName;

    /**
     * @var string
     */
    public $style = 'plain';


    public function __construct($fieldName, $friendlyName = null)
    {
        $this->setfieldName($fieldName);
        $this->friendlyName = $friendlyName;
        $this->setSearchType();
    }

    /**
     * set the list of searchable characters from those availabe in self::$searchTypes
     * @param int $searchType - index of self::$searchTypes to use
     *   defaults to first search list if not provided
     *
     * @return AlphaNumericFilter $this
     */
    public function setSearchType($searchType = 0)
    {
        $searchType = (int)$searchType;
        if (isset(self::$searchTypes[$searchType])) {
            $this->searchType = $searchType;
        } else {
            throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: Selected character set is not found');
        }
        return $this;
    }

    /**
     * Set the field name for this filter
     * @param string $fieldName - the table fieldname to search in
     *
     * @return ANFilter
     */
    public function setFieldName($fieldName = null)
    {
        if (isset($fieldName)) {
            $this->fieldName = $fieldName;
        }
        return $this;
    }

    /**
     * return the list of searchable characters associated with this filter
     * @param bool $asString - return the string of characters rather than the
     *   numeric index
     *
     * @return string
     */
    public function getSearchType($asString = true)
    {
        if ($asString) {
            return (isset(self::$searchTypes[$this->searchType]) ? self::$searchTypes[$this->searchType] : '');
        } else {
            return $this->searchType;
        }
    }

    /**
     * return the fieldname for this filter
     */
    public function getFieldname()
    {
        return $this->fieldName;
    }

    /**
     * render the filter html
     * @param bool $includeTitles include title text or each character
     * @return string - the filter html
     */
    public function renderHTML($includeTitles = true)
    {
        $searchStr = $this->getSearchType();
        $curChar = $value = isset($_REQUEST['af']) ? $_REQUEST['af'] : null;

        $html = '<div id="alphaFilter_" class="aphaFilter">';

        for ($x = 0; $x < strlen($searchStr); $x++) {
            $html .= '<span class="af ">';
            $chr = $searchStr{$x};

            if ($includeTitles) {
                $title = 'Search for '.(isset($this->friendlyName) ? $this->friendlyName : $this->fieldName).' beginning with '.($chr == '#' ? 'a number' : $chr);
            } else {
                $title = '';
            }

            switch($this->style) {
                case 'plain' :
                default:
                    $nClass = ' aflink ';
                    $nClass .= ($chr == $curChar ? ' selected ' : '');
                    break;
            }

            if ($chr == '#') {
                $html .= '<a class="aflink '.$nClass.'" title="'.$title.'" href="?af=%23">'.$chr.'</a>';
            } else {
                $html .= '<a class="aflink '.$nClass.'" title="'.$title.'" href="?af='.$chr.'">'.$chr.'</a>';
            }
            $html .= '</span>';
        }
        switch($this->style) {
            case 'plain' :
            default:
                $html .= '<a class="afbutton " title="Remove Alphanumeric Filter" href="?af=none"><i class="icon-remove-sign"></i></a>';
                break;
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * return the sql string for the selected filter value
     * @param string $sql - the SQL string to append the filter SQL to
     * @param string $value - the value to search for, check $_REQUEST['af'] is parsed as null
     * @return string $sql - apppended SQL string
     */
    public function getSql($sql = '', $value = null)
    {
        if (!isset($value)) {
            $value = isset($_REQUEST['af']) ? $_REQUEST['af'] : 'none';
        }

        if ($value == 'none') {
            // clear the filter
            return $sql;
        }

        // check that what has been parsed as a search value is actually found
        // in our list of available characters
        if (self::valueIsValid($this->searchType, $value)) {
            // search for generic numeric value
            if ($value == '#') {
                $xtrsql = " IN ('0','1','2','3','4','5','6','7','8','9') ";
            } else {
                $xtrsql = " = '".strtoupper($value)."' ";
            }

            return $sql.(strpos($sql, 'WHERE')===false ? " WHERE " : " AND " )." UPPER(LEFT(".$this->fieldName.", 1)) ".$xtrsql." ";
        } else {
            return $sql;
        }
    }

    /**
     * check if a given search value exists for a given searchType
     * @param int $searchType the index of self::$searchTypes
     * @param string $value the search value to find
     * @return boolean true if the search value is valid
     */
    public static function valueIsValid($searchType, $value)
    {
        $valid = false;
        if (isset(self::$searchTypes[(int)$searchType])) {
            $searchables = self::$searchTypes[(int)$searchType];
            for ($x = 0; $x < strlen($searchables); $x++) {
                if ($value == $searchables{$x}) {$valid = true; break;}
            }
        }
        return $valid;
    }
}