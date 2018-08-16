<?php
class MaatExtension_image implements Maat\Extension
{
    private $maat;
    function __construct($maat)
    {
        $this->maat = $maat;
        $maat->define_trigger(
            'image',
            'img',
            '(.*).(jpe?g|gif|png)(?: +(.+))?',
            false
        );
    }

    function render(array $group) : string
    {
        $urldir = $group['config']['folder'];
        $dir = '.' . $group['config']['folder'];
        $alt = isset($group['class-data'][3]) ? $group['class-data'][3] : '';
        $description = strstr($group['line'], "<br>") ? '<p>' . explode("<br>", $group['line'])[1] . '</p>' : '';
        @list($link, $newalt) = explode(' ', $alt, 2);
        if (preg_match('/[a-z]+\:.+/i', $link)) {
            $alt = $newalt;
        } else {
            $link = '';
        }
        $linkbegin = ($link !== '') ? '<a href="' . $link . '" class="img-link">' : '';
        $linkend = ($link !== '') ? '</a>' : '';
        $alt = $alt !== '' ? 'alt="' . $alt . '" ' : '';
        $file = $group['class-data'][1] . '.' . $group['class-data'][2];
        $retina = substr($group['class-data'][1], -3, 3) === '@2x' ? true : false;
        if (file_exists($dir . $file)) {
            $size = getimagesize($dir . $file);
            $width = $retina ? $size[0] / 2 : $size[0];
            $width = min(array($width, $group['config']['max-width']));
            // $width = $width > $group['config']['max-width'] : $group['config']['max-width']
            $proportion = round(100 * $size[1] / $size[0], 2);
            if ($this->maat->config['basic-html']) {
                $img = '<img src="' . $urldir . $file . '" style="max-width:100%" width="' . $width . 'px" ' . $alt . '>';
                if ($link) {
                    $img = '<a href="' . $link . '">' . $img . '</a>';
                }
            } else {
                $img = '<div style="max-width:' . $width . 'px" class="' . $group['config']['container-class'] . '">' .
                    $linkbegin . '<div class="' . $group['config']['wrapper-class'] . '" style="padding-bottom:' . $proportion . '%">' .
                    '<img src="' . $urldir . $file . '" ' . $alt . '>' .
                    '</div>' . $description . $linkend . '</div>';
            }
            return $img;
        }
        return '';
    }
}
