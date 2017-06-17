<?php
class MaatExtension_hipsterquote implements MaatExtension
{
    function __construct($maat)
    {   
        $maat->define_trigger(
            'hipsterquote',
            'hipstr',
            '&&(.*)&&',
            true
        );
    }
    function render(array $group, array $config): string
    {
        return  '<div class="hipster-quote">'.$group['class-data'][1].'</div>';
    }
}
