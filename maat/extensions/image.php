<?php
class MaatGroup_image implements MaatGroup
{
    private $config = array ();
    function render(string $content, array $config): array
    {
        $this->config = $config;
        $renderedContent = $content;
        preg_match("/(.*).(jpe?g|gif|png)\s((?:https?|ftps?)([^<]*))/", $content, $img);
        if ($img) {
            $renderedContent = $this->getImage($img, false, false, true);
        } else {
            preg_match("/(.*).(jpe?g|gif|png)([^<]*)<br>([^<]*)/", $content, $img);
            if ($img) {
                $renderedContent = $this->getImage($img, true, true);
            } else {
                preg_match("/(.*).(jpe?g|gif|png)([^<]*)/", $content, $img);
                if ($img) {
                    $renderedContent = $this->getImage($img, true, false);
                } else {
                    preg_match("/(.*).(jpe?g|gif|png)/", $content, $img);
                    if ($img) {
                        $renderedContent = $this->getImage($img, false, false);
                    }
                }
            }
        }
        return array($renderedContent, false);
    }
    function getImage($img, $alt, $description, $link = false)
    {
        if ($link){
            $linkbegin = '<a href="'.trim($img[3]).'" class="img-link">';
            $linkend = '</a>';
        }
        if ($alt && $img[3] !== '') {
            $altAttr = 'alt="'.trim($img[3]).'"';
        } else {
            $altAttr = '';
        }
        if ($description && $img[4] !== '') {
            $description = '<p>'.trim($img[4]).'</p>';
        } else {
            $description = '';
        }
        $urldir = $this->config['imagesDirectory'].'pictures/';
        $dir = $this->config['cwd'].$this->config['imagesDirectory'].'pictures/';
        $filename = $img[1].'.'.$img[2];
        $retina = false;
        if (($altAttr && $altAttr === 'alt="2x"') || substr($img[1],-3, 3) === '@2x')
            $retina = true;
        if (file_exists($dir.$filename)) {
            $size = getimagesize($dir.$filename);
            if ($retina) {
                $width = $size[0] / 2;
            } else {
                $width = $size[0];
            }
            $proportion = round(100*$size[1]/$size[0], 2);
            return '<div style="max-width:'.$width.'px" class="img-container honey">'.$linkbegin.'<div class="img-wrapper" style="padding-bottom:'.$proportion.'%"><img src="'.$urldir.$filename.'" '.$altAttr.'></div>'.$description.$linkend.'</div>';
        } else {
            return '';
        }
    }
}
