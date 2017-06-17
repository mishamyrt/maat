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
    function render(array $group, array $config): string
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

        // $video = $content;
        // preg_match("/^https?:\/\/.*youtube\.com\/watch\?v=(.*)/", $content, $youtube);
        // if ($youtube) {
        //     $dir = $config['cwd'].$config['imagesDirectory'].'previews/';
        //     $video = '<div class="video-container"><div class="video-wrapper"><iframe width="854" height="480" src="https://www.youtube.com/embed/'.$youtube[1].'" frameborder="0" allowfullscreen></iframe></div></div>';
        //     if (!file_exists($dir.$youtube[1].'.full.jpg')) {
        //         $preview = imagecreatefromjpeg("https://img.youtube.com/vi/".$youtube[1]."/maxresdefault.jpg");
        //         imagejpeg($preview, $dir.$youtube[1].'.full.jpg');
        //         list($width, $height) = getimagesize($dir.$youtube[1].'.full.jpg');
        //         $thumb = imagecreatetruecolor(400, 225);
        //         imagecopyresized($thumb, $preview, 0, 0, 0, 0, 400, 225, $width, $height);
        //         imagejpeg($thumb, $dir.$youtube[1].'.thumb.jpg');
        //     }
        // }
    }
}
