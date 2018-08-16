<?php
class MaatExtension_table implements Maat\Extension
{
    function __construct($maat)
    {
        $maat->define_trigger(
            'table',
            't',
            '[-–—]{5}<br>(.*)<br>[-–—]{5}',
            true
        );
    }
    function render(array $group) : string
    {
        $render = '';
        $lines = explode('<br>', $group['line']);
        for ($i = 0; $i < sizeof($lines); $i++) {
            $render .= '<tr>' . "\n";
            $elements = explode('|', $lines[$i]);
            for ($j = 1; $j < sizeof($elements) - 1; $j++) {
                $left = false;
                $right = false;
                $align = '';
                if (substr($elements[$j], 0, 1) == ' ') $left = true;
                if (substr($elements[$j], -1, 1) == ' ') $right = true;
                if ($right && $left) $align = ' align="center"';
                elseif ($left) $align = ' align="right"';
                $render .= '<td><p' . $align . '>' . $elements[$j] . '</p></td>' . "\n";
            }
            $render .= '</tr>' . "\n";
        }
        return '<table>' . $render . '</table>';
    }
}
