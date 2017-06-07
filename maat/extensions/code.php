<?php
class MaatGroup_code implements MaatGroup
{
    function render(string $content, array $config): array
    {
        $renderedContent = $content;
        preg_match("/<code>(.*)<\/code>/", $content, $quote);
        if ($quote) {
            $code = htmlspecialchars(str_replace('<br>', "nneewwlliinnee", $quote[1]));
            $code = substr(str_replace("nneewwlliinnee", '<br>'."\n", $code), 5);
            $code = substr($code, 0, -5);
            $code = str_replace('{', "&#123;", str_replace('}',"&#125;" ,$code));
            $renderedContent = '<code>'.$code.'</code>';
        }
        return array($renderedContent, false);
    }
}
