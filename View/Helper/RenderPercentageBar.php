<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class RenderPercentageBar extends AbstractHelper
{

    /**
     * return  html to render a twitter bootstrap (v3) progress bar with percentage
     * @param mixed float|int $percentage
     * @param int $precision decimal places to display percentage value to
     * @param boolean $showPercentage display the percentage value in the bar
     *
     * @return strubg
     */
    public function __invoke($percentage, $precision = 0, $showPercentage = true)
    {
        $value = number_format($percentage, $precision);

        $html = '<div class="progress">
                        <div 
                            class="progress-bar" 
                            role="progressbar" 
                            aria-valuenow="' . $value . '" 
                            aria-valuemin="0" 
                            aria-valuemax="100" 
                            style="width: ' . $value . '%;"
                         >';

        if (!$showPercentage) {
            $html .= '<span class="sr-only">' . $value . '% Complete</span>';
        } else {
            $html .= $value . '%';
        }

        $html .= '</div></div>';

        return $html;
    }

}