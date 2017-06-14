<?php
declare(strict_types=1);
interface MaatGroup
{
    function render(string $content, array $config): array;
}
class Maat
{
    private $extensions = array();
    private $content = array();
    private $config = array();
    private $lineDict = array(
        "/\(\(([^\(\)\s]*)\s([^\(\)]*)\)\)/" => '<a href="$1">$2</a>', // ((http://ya.ru/ яндекс))
        "/(^|\s)((?:https?|ftps?)\:\/\/[\w\d\#\.\/&=%-_!\?\@\*][^\s<>\"\,]*)/" => '$1<a href="$2">$2</a>', //http://ya.ru
        "/\*\*([^\*]*)\*\*/" => "<b>$1</b>", //bold
        "/\/\/([^\/\"]+)\/\//" => "<i>$1</i>" //italic
    );
    private $blockDict = array(
        array("/^>\s*(.*)/", '<blockquote><p>$1</p></blockquote>'),
        array("/^##\s*(.*)/", '<h3>$1</h3>'),
        array("/^#\s*(.*)/", '<h2>$1</h2>')
    );

    function __construct()
    {
        $this->config = include('config.php');
        $this->config['cwd'] = getcwd();
        $extensions = glob($this->config['directory'].'maat/extensions/*.php', GLOB_BRACE);
        for ($i=0; $i < sizeof($extensions); $i++) {
            $this->load_extension($extensions[$i]);
        }
    }
    public function render(string $text): string
    {
        $text .= "\n";
        $lines = explode("\n", $text);
        $linePatterns = array_keys($this->lineDict);
        $lineValues = array_values($this->lineDict);
        $isHTML = false;
        $isCode = false;
        $line = '';
        for ($i=0; $i < sizeof($lines); $i++) {
            $trimedLine = trim($lines[$i]);
            switch ($trimedLine) {
                case '<html>':
                    $isHTML = true;
                    $line = $trimedLine."\n";
                    break;
                case '<code>':
                    $isCode = true;
                    $line = $trimedLine . "\n";
                    break;
                case '':
                    if ($isHTML) {
                        $isHTML = false;
                    } elseif ($isCode) {
                        preg_match("/<code>(.*)<\/code>/s", $line, $quote);
                            $code = htmlspecialchars($quote[1]);
                            $code = str_replace('{', "&#123;", str_replace('}', "&#125;", $code));
                            $line = '<code>'.$code.'</code>';
                        $isCode = false;
                    } else {
                        $p = true;
                        for ($j = 0; $j < sizeof($this->blockDict); $j++) {
                            preg_match($this->blockDict[$j][0], $line, $result);
                            if ($result) {
                                $p = false;
                                $line = preg_replace($this->blockDict[$j][0], $this->blockDict[$j][1], $line);
                                break;
                            }
                        }
                        $needFormating = true;
                        $result = $this->group_render($line);
                        if ($result[2]) {
                            $p = false;
                            $line = $result[0];
                            $needFormating = $result[1];
                        }
                        if ($needFormating) {
                            $line = preg_replace($linePatterns, $lineValues, $line);
                        }
                        if ($p) {
                            $line = '<p>'.$line.'</p>';
                        }
                    }
                    $this->content[] = $line;
                    $line = '';
                    break;
                default:
                    if ($isHTML) {
                        $line .= $trimedLine."\n";
                    } elseif ($isCode) {
                        $line .= $lines[$i] . "\n";
                    } else {
                        if ($line === '') {
                            $line .= $trimedLine;
                        } else {
                            $line .= '<br>'.$trimedLine;
                        }
                    }
                    break;
            }
        }
        $renderedContent = implode($this->content, "\n");
        unset($this->content);
        return $renderedContent;
    }
    private function load_extension(string $file): bool
    {
        $name = basename($file, ".php");
        $MaatGroupClass = 'MaatGroup_' . $name;
        include_once $file;
        $this->extensions[] = new $MaatGroupClass ($this);
        return true;
    }
    private function group_render(string $line): array
    {
        $render = array();
        $flag = false;
        $length = strlen($line);
        $needFormating = true;
        for ($i=0; $i < sizeof($this->extensions); $i++) {
            $render = $this->extensions[$i]->render($line, $this->config);
            if (strlen($render[0]) !== $length) {
                $needFormating = $render[1];
                $flag = true;
                break;
            }
        }
        return array($render[0], $needFormating, $flag);
    }
}
