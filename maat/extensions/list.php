<?php
class MaatGroup_list implements MaatGroup
{
    function render(string $content, array $config): array
    {
        $renderedContent = $content;
        preg_match("/\*(.*)<br>\*/", $content, $list);
        if ($list) {
            $list = explode('<br>* ', $renderedContent);
            $list[0] = substr($list[0], 2);
            $renderedContent = '<ul>'."\n";
            for ($i=0; $i < count($list); $i++) {
                $renderedContent .= '<li><p>'.$list[$i].'</p></li>'."\n";
            }
            $renderedContent .= '</ul>';
        } else {
            preg_match("/1\.(.*)<br>1\./", $content, $list);
            if ($list) {
                $list = explode('<br>1. ', $renderedContent);
                $list[0] = substr($list[0], 3);
                $renderedContent = '<ol>'."\n";
                for ($i=0; $i < count($list); $i++) {
                    $renderedContent .= '<li><p>'.$list[$i].'</p></li>'."\n";
                }
                $renderedContent .= '</ol>';
            }
        }
        return array($renderedContent, true);
    }
}
