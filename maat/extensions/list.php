<?php
class MaatExtension_list implements MaatExtension
{
    function __construct($maat)
    {   
        $maat->define_trigger(
            'list',
            'ul',
            '\*(.*)<br>\*',
            true
        );
        $maat->define_trigger(
            'list',
            'ol',
            '1\.(.*)<br>1\.',
            true
        );
    }

    function render(array $group, array $config): string
    {
        $render = '';
        switch($group['class']){
            case 'ul':
                $list = explode('<br>* ', $group['line']);
                $list[0] = substr($list[0], 2);
            break;
            case 'ol':
                $list = explode('<br>1. ', $group['line']);
                $list[0] = substr($list[0], 3);
            break;
        }
        for ($i=0; $i < sizeof($list); $i++) {
            $render .= '<li><p>'.$list[$i].'</p></li>'."\n";
        }
        return '<'.$group['class'].'>'.$render.'</'.$group['class'].'>';
    }
}
