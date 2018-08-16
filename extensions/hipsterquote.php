<?php
class MaatExtension_hipsterquote implements Maat\Extension
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
    function render(array $group) : string
    {
        return '<div class="hipster-quote">' . $group['class-data'][1] . '</div>';
    }
}
