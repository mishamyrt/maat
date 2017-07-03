<?php
class MaatExtension_image implements MaatExtension
{
    function __construct($maat)
    {
        $maat->define_trigger(
            'image',
            'img',
            '(.*).(jpe?g|gif|png)(?: +(.+))?',
            false
        );
    }

    function render(array $group, array $config): string
    {
        $urldir = $config['imagesDirectory'].'pictures/';
        $dir = $config['cwd'].$urldir;
        $alt = isset($group['class-data'][3]) ? $group['class-data'][3] : '';
        $description = strstr($group['line'], "<br>") ? '<p>' . explode("<br>", $group['line'])[1] . '</p>' : '';
        @list ($link, $newalt) = explode (' ', $alt, 2);
        if (preg_match ('/[a-z]+\:.+/i', $link)) {
            $alt = $newalt;
        } else {
            $link = '';
        }
        $linkbegin = ($link !== '') ? '<a href="'.$link.'" class="img-link">' : '';
        $linkend = ($link !== '') ? '</a>' : '';
        $alt = $alt !== '' ? 'alt="'.$alt.'" ' : '';
        $file = $group['class-data'][1].'.'.$group['class-data'][2];
        $retina = (($alt === 'alt="2x"') || substr($group['class-data'][1], -3, 3) === '@2x') ? true : false;
        if (file_exists($dir.$file)) {
            $size = getimagesize($dir.$file);
            $width = $retina ? $size[0] / 2 : $size[0];
            $proportion = round(100*$size[1]/$size[0], 2);
            return '<div style="max-width:'.$width.'px" class="img-container honey">'.
                   $linkbegin.'<div class="img-wrapper" style="padding-bottom:'.$proportion.'%">'.
                   '<img src="'.$urldir.$file.'" '.$alt.'>'.
                   '</div>'.$description.$linkend.'</div>';
        }
        return '';
    }
}
