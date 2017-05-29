<?php
class MaatGroup_image implements MaatGroup {
  private $config = array ();
   function render ($content, $config) {
     $this->config = $config;
     $renderedContent = $content;
     preg_match("/<p>(.*).(jpe?g|gif|png)<\/p>/", $content, $img);
     if ($img) {
       return $this->getImage($img, false, false);
     }
     else {
       preg_match("/<p>(.*).(jpe?g|gif|png)([^<]*)<\/p>/", $content, $img);
       if ($img) {
         return $this->getImage($img, true, false);
       }
       else{
         preg_match("/<p>(.*).(jpe?g|gif|png)([^<]*)<br>([^<]*)<\/p>/", $content, $img);
         if ($img) {
           return $this->getImage($img, true, true);
         }
       }
     }
     return $renderedContent;
   }
   function getImage ($img, $alt, $description) {
     if ($alt && $img[3] !== '')
      $altAttr = 'alt="'.trim($img[3]).'"';
     else $altAttr = '';
     if ($description && $img[4] !== '')
      $description = '<p>'.trim($img[4]).'</p>';
     else
      $description = '';
     $urldir = $this->config['imagesDirectory'].'pictures/';
     $dir = $this->config['cwd'].$this->config['imagesDirectory'].'pictures/';
     $filename = $img[1].'.'.$img[2];
     if (file_exists($dir.$filename)) {
        $size = getimagesize($dir.$filename);
        if ($altAttr && $altAttr == 'alt="2x"')
          $width = $size[0] / 2;
        else $width = $size[0];
        $proportion = round(100*$size[1]/$size[0], 2);
        return '<div style="max-width:'.$width.'px" class="img-container honey"><div class="img-wrapper" style="padding-bottom:'.$proportion.'%"><img src="'.$urldir.$filename.'" '.$altAttr.'></div>'.$description.'</div>';
     }
     else
        return '';
   }
}
