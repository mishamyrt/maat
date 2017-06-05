<?php
class MaatGroup_youtube implements MaatGroup
{
    function render($content, $config)
    {
        $video = $content;
        preg_match("/.*youtube\.com\/watch\?v=(.*)/", $content, $youtube);
        if ($youtube) {
            $dir = $config['cwd'].$config['imagesDirectory'].'previews/';
            $video = '<div class="video-container"><div class="video-wrapper"><iframe width="854" height="480" src="https://www.youtube.com/embed/'.$youtube[1].'" frameborder="0" allowfullscreen></iframe></div></div>';
            if (!file_exists($dir.$youtube[1].'.full.jpg')) {
                $preview = imagecreatefromjpeg("https://img.youtube.com/vi/".$youtube[1]."/maxresdefault.jpg");
                imagejpeg($preview, $dir.$youtube[1].'.full.jpg');
                list($width, $height) = getimagesize($dir.$youtube[1].'.full.jpg');
                $thumb = imagecreatetruecolor(400, 225);
                imagecopyresized($thumb, $preview, 0, 0, 0, 0, 400, 225, $width, $height);
                imagejpeg($thumb, $dir.$youtube[1].'.thumb.jpg');
            }
        }
        return array($video, false);
    }
}
