<?php
class MaatGroup_vimeo implements MaatGroup {
   function render ($content, $config) {
     $video = $content;
     preg_match("/<p><a href=\".*vimeo\.com\/.*\">.*vimeo\.com\/(.*)<\/a><\/p>/", $content, $vimeo);
     if ($vimeo) {
        $dir = $config['cwd'].$config['imagesDirectory'].'previews/';
        $video = '<div class="video-container"><iframe src="https://player.vimeo.com/video/'.$vimeo[1].'" width="854" height="480" frameborder="0" allowfullscreen></iframe></div>';
        if (!file_exists($dir.$vimeo[1].'.full.jpg')){
          $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$vimeo[1].php"))[0];
          $preview = imagecreatefromjpeg($hash['thumbnail_large']);
          imagejpeg($preview, $dir.$vimeo[1].'.full.jpg');
          list($width, $height) = getimagesize($dir.$vimeo[1].'.full.jpg');
          $thumb = imagecreatetruecolor(400, 225);
          imagecopyresized($thumb, $preview, 0, 0, 0, 0, 400, 225, $width, $height);
          imagejpeg($thumb, $dir.$vimeo[1].'.thumb.jpg');
        }
     }
    return $video;
   }
}
