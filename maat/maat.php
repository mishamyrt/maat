<?php
interface MaatGroup
{
    function render($content, $config);
}
class Maat
{
    private $extensions = array();
    private $content = array();
    private $config = array();
    protected $lineDict = array(
        "/\(\(([^\(\)]*) ([^\(\)]*)\)\)/" => '<a href="$1">$2</a>',
        "/((?:https?|ftps?)\:\/\/[\w\d\#\.\/&=%-_!\?\@\*][^\s<>\"]*)(\s)/" => '<a href="$1">$1</a> ',
        "/((?:https?|ftps?)\:\/\/[\w\d\#\.\/&=%-_!\?\@\*][^\s<>\"]*)(<)/" => '<a href="$1">$1</a><',
        "/\*\*([^\*]*)\*\*/" => "<b>$1</b>", //bold
        "/\/\/([^\/*]*)\/\//" => "<i>$1</i>" //italic
    );
    protected $blockDict = array(
        array("/^>(.*)/", '<blockquote><p>$1</p></blockquote>'),
        array("/^# (.*)/", '<h2>$1</h2>'),
        array("/^## (.*)/", '<h3>$1</h3>')
    );

    function __construct()
    {
        $this->config = include('config.php');
        $this->config['cwd'] = getcwd();
        $extensions = glob($this->config['directory'].'maat/extensions/*.php', GLOB_BRACE);
        foreach ($extensions as $extension) {
            $this->load_extension($extension);
        }
    }
    private function load_extension($file)
    {
        $name = basename ($file);
        $name = basename($file, ".php");
        $MaatGroupClass = 'MaatGroup_' . $name;
        include_once $file;
        $this->extensions[$name] = array (
        'path' => dirname ($file) .'/'. $name .'/',
        'instance' => new $MaatGroupClass ($this),
        );
        return true;
    }
    private function group_render($line)
    {
        $render = array();
        $flag = false;
        $needFormating = true;
        foreach ($this->extensions as $extension) {
            $render = $extension['instance']->render($line, $this->config);
            if ($render[0] != $line){
                $needFormating = $render[1];
                $flag = true;
                break;
            }
        }
        return array($render[0], $needFormating, $flag);
    }
    public function render($text)
    {
        $text .= "\n";
        $lines = explode("\n", $text);
        $linePatterns = array_keys($this->lineDict);
        $lineValues = array_values($this->lineDict);
        $isHTML = false;
        $line = '';
        for ($i=0; $i < count($lines); $i++) {
            $trimedLine = trim($lines[$i]);
            switch ($trimedLine){
                case '<html>':
                    $isHTML = true;
                    $line = $trimedLine."\n";
                    break;
                case '':
                    if ($isHTML) $isHTML = false;
                    else {
                        foreach ($this->blockDict as $block) {
                            $regexp = $block[0];
                            $replacement = $block[1];
                            preg_match($regexp, $line, $result);
                            if ($result) {
                                $line = preg_replace($regexp, $replacement, $line);
                                break 1;
                            }
                        }
                        $needFormating = true;
                        $result = $this->group_render($line);
                        if ($result[2]){
                            $line = $result[0];
                            $needFormating = $result[1];
                        }
                        if ($needFormating){
                            $line = preg_replace($linePatterns, $lineValues, $line);
                        }
                        else {
                            $line = '<p>'.$line.'</p>';
                        }
                    }
                    $this->content[] = $line;
                    $line = '';
                    break;
                default:
                    if ($isHTML) 
                        $line .= $trimedLine."\n";
                    else {
                        if ($line == '')
                            $line .= $trimedLine;
                        else
                            $line .= '<br>'.$trimedLine;
                    }
                    break;
            }
        }
        $renderedContent = implode($this->content, "\n");
        return $renderedContent;
    }
}
