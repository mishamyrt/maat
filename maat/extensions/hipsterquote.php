<?php
class MaatGroup_hipsterquote implements MaatGroup
{
    function render(string $content, array $config): array
    {
        $renderedContent = $content;
        preg_match("/&&(.*)&&/", $content, $quote);
        if ($quote) {
            $renderedContent = '<div class="hipster-quote">'.$quote[1].'</div>';
        }
        return array($renderedContent, false);
    }
}
