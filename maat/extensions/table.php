<?php
class MaatGroup_table implements MaatGroup
{
    function render($content, $config)
    {
        $renderedContent = $content;
        preg_match("/<p>-{5}<br>(.*)<br>-{5}<\/p>/", $content, $tableString);
        if ($tableString) {
            $renderedContent = '<table>'."\n";
           $tableLines = explode('<br>', $tableString[1]);
           foreach($tableLines as $line){
             $els = explode('|',$line);
             $renderedContent .= '<tr>'."\n";
             for ($i=1; $i < count($els) - 1; $i++) { 
                $left = false;
                $right = false;
                $align = '';
                if (substr($els[$i], 0, 1) == ' ') {
                    $left = true;
                }
                if (substr($els[$i], -1, 1) == ' ') {
                    $right = true;
                }
                if ($right && $left){
                    $align = ' align="center"';
                }
                else if ($left) {
                    $align = ' align="right"';
                }
                $renderedContent .= '<td><p'.$align.'>'.$els[$i].'</p></td>'."\n";
             }
            $renderedContent .= '</tr>'."\n";
           }
           $renderedContent .= '</table>'."\n";
        //    var_dump($renderedContent);
        }
        return $renderedContent;
    }
}
