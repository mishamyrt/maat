<?php
class MaatExtension_onlinevideo implements MaatExtension
{
    function __construct($maat)
    {   
        $maat->define_trigger(
            'onlinevideo',
            'youtube',
            '^https?:\/\/.*youtube\.com\/watch\?v=(.*)',
            false
        );
        $maat->define_trigger(
            'onlinevideo',
            'vimeo',
            '^https?:\/\/.*vimeo\.com\/(.*)',
            false
        );
    }
    function render(array $group): string
    {   
        switch ($group['class']) {
            case 'youtube':
                $src = 'https://www.youtube.com/embed/'.$group['class-data'][1];
            break;
            case 'vimeo':
                $src = 'https://player.vimeo.com/video/'.$group['class-data'][1];
            break;
        }
        return '<div class="video-container"><div class="video-wrapper">'.
               '<iframe src="'.$src.'"'.
               'width="854" height="480" frameborder="0" allowfullscreen>'.
               '</iframe></div></div>';
    }
}
