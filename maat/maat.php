<?php
declare(strict_types=1);
interface MaatExtension
{
    function render(array $group): string;
}
class Maat
{
    public $config      = array();
    private $extensions = array();
    private $triggers   = array();
    private $line_dict   = array(
        '/\(\(([^\(\)\s]*)\s([^\(\)]*)\)\)/'                                   => '<a href="$1">$2</a>',          // ((http://ya.ru/ яндекс))
        '/(^|\s)((?:https?|ftps?)\:\/\/[\w\d\#\.\/&=%-_!\?\@\*][^\s<>\"\,]*)/' => '$1<a href="$2">$2</a>',        // http://ya.ru
        '/\*\*([^\*]*)\*\*/'                                                   => '<b>$1</b>',                    // **bold**
        '/\/\/([^\/\"]+)\/\//'                                                 => '<i>$1</i>',                    // //italic//
        '/--([^\/\"]+)--/'                                                     => '<s>$1</s>',                    // --strike-through--
        '/\b[A-ZА-ЯЁ][A-ZА-ЯЁ]+\b[\.\s]/u'                                     => '<span class="caps">$0</span>'  // Class to upper case words
    );
    private $blockDict = array(
        array("/^>\s*(.*)/",  '<blockquote><p>$1</p></blockquote>'),
        array("/^##\s*(.*)/", '<h3>$1</h3>'),
        array("/^#\s*(.*)/",  '<h2>$1</h2>')
    );

    public function define_trigger(string $extension, string $class, string $regex, bool $formatting): bool
    {
        if (!in_array($extension, $this->config['banned-extensions'], true)) {
            $this->triggers[] = array($extension, $class, '/^'.$regex.'/', $formatting, true);
        }
        return true;
    }

    function __construct(string $profile = '')
    {
        $this->config = include('config.php');
        if (isset($this->config['profiles'][$profile])) {
            foreach ($this->config['profiles'][$profile] as $key => $value) {
                $this->config[$key] = $value;
            }
            $this->config['profile'] = $profile;
        } elseif ($profile !== '') {
            echo 'There is no profile "'.$profile.'", falling back to default';
        }
        $this->line_patterns = array_keys($this->line_dict);
        $this->line_replacement = array_values($this->line_dict);
        $extensions = glob($this->config['folder'].'/extensions/*.php', GLOB_BRACE);
        for ($i=0; $i < sizeof($extensions); $i++) {
            $this->load_extension($extensions[$i]);
        }
    }

    private function render_with_extensions(string $line): array
    {
        for ($i=0; $i < sizeof($this->triggers); $i++) {
            preg_match($this->triggers[$i][2], $line, $result);
            if ($result) {
                $group = array(
                    'class' => $this->triggers[$i][1],
                    'class-data' => $result,
                    'line' => $line,
                    'config' => isset($this->config['extensions'][$this->triggers[$i][0]]) ?: null
                );
                return array(
                    $this->extensions[$this->triggers[$i][0]]->render($group),
                    $this->triggers[$i][3]
                );
            }
        }
        return array(false, false);
    }

    private function load_extension(string $file): bool
    {
        $name = basename($file, ".php");
        $MaatExtensionClass = 'MaatExtension_' . $name;
        include $file;
        $this->extensions[$name] = new $MaatExtensionClass($this);
        return true;
    }

    public function render(string $text): string
    {
        $tree = $this->get_tree($text);
        return implode("\n", $tree);
    }

    private function format_paragraph(string $paragraph): string
    {
        return preg_replace($this->line_patterns, $this->line_replacement, $paragraph);
    }

    private function get_tree(string $text): array
    {
        $outputBlock = array();
        if ($text[mb_strlen($text) - 1] !== "\n") {
            $text .= "\n";
        }
        $lines = explode("\n", $text);
        $blocks = array();
        $innerTypes = array('header', 'paragraph', 'quote');
        $lastBlock = 'p';
        $content = '';
        $break = false;
        for ($i = 0; $i < sizeof($lines); $i++) {
            $line = trim($lines[$i]);
            if (mb_strlen($line) > 0) {
                switch ($line[0]) {
                    case '#':
                        if ($content !== '') {
                            $break = true;
                            $i--;
                        } else {
                            $content = $line . "\n";
                            $break = true;
                            $lastBlock = 'header';
                        }
                        break;
                    case '>':
                        if ($content !== '') {
                            if ($lastBlock = 'quote') {
                                $content .= $line . "\n";
                            } else {
                                $break = true;
                                $i--;
                            }
                        } else {
                            $content = $line . "\n";
                            $lastBlock = 'quote';
                        }
                       
                        break;
                    case '<':
                        if (mb_substr($line, 0, 6) == '<code>'){
                            if ($content !== '') {
                                $break = true;
                                $i--;
                            } else {
                                $content = $lines[$i] . "\n";
                                $lastBlock = 'code';
                            }
                            break;
                        }
                    default:
                        if ($content === '') {
                            $content = $line . "\n";
                            $lastBlock = 'paragraph';
                        } elseif ($lastBlock === 'paragraph') {
                            $content .= $line . "\n";
                        } elseif ($lastBlock === 'code'){
                            $content .= $lines[$i] . "\n";
                        } else {
                            $break = true;
                            $i--;
                        }
                        break;
                }
            } else {
                $break = true;
            }
            if ($break) {
                if ($content !== '') {
                    $content = mb_substr($content, 0, mb_strlen($content) - 1);
                    $function = "render_".$lastBlock;
                    $outputBlock[] = $this->$function($content);
                    $blocks[] = array('content' => $content, 'type' => $lastBlock);
                    $content = '';
                }
                $break = false;
            }
        }
        if ($content !== '') {
            $content[mb_strlen($content) - 1] = '';
            $blocks[] = array('content' => $content, 'type' => $lastBlock);
            $content = '';
        }
        return $outputBlock;
    }
    private function render_code (string $line): string{
        $line = htmlspecialchars($line);
        $line = str_replace('{', "&#123;", str_replace('}', "&#125;", $line));
        return $line;
    }
    private function render_header (string $line): string{
        $level = 1;
        for ($i = 1; $i < mb_strlen($line); $i++) {
            if ($line[$i] !== ' ') {
                $level++;
            } else {
                break;
            }
        }
        $tag = 'h' . ($level + 1);
        return '<' . $tag . '>' . mb_substr($line, $level + 1) . '</' . $tag . '>';
    }
    private function render_paragraph (string $paragraph): string{
        return '<p>' . $this->format_paragraph($paragraph) . '</p>';
    }
    private function render_quote (string $quote): string {
        $lines = explode("\n", $quote);
        $content = array();
        $tagsOpened = 0;
        $tagsClosed = 0;
        $lastLevel = 1;
        $result = '';
        for ($i = 0; $i < sizeof($lines); $i++){
            $level = $this->get_quote_level($lines[$i]);
            $line = ltrim($lines[$i], '> ');
            if ($level > $lastLevel){
                $result .= str_repeat('<blockquote><p>' . "\n", $level - $lastLevel);
                $tagsOpened += $level - $lastLevel;
            } else if ($level < $lastLevel){
                $result .= str_repeat('</p></blockquote>' . "\n", $lastLevel - $level);
                $tagsClosed += $lastLevel - $level;
            }
            $result .= $this->format_paragraph($line) . '<br>' . "\n";
            $lastLevel = $level;
        }
        for ($i = 0; $i < $tagsOpened - $tagsClosed; $i++){
            $result .= '</p></blockquote>' . "\n";
        }
        return '<blockquote><p>' . "\n" . $result . '</p></blockquote>';
    }
    private function get_quote_level(string $line): int{
        $level = 1;
        for ($i = 1; $i < mb_strlen($line); $i++) {
            if ($line[$i] !== ' ' && $line[$i] !== '>') {
                break;
            } else {
                $level++;
            }
        }
        return $level;
    }
    function mb_trim($str) {
        return preg_replace("/(^\s+)|(\s+$)/us", "", $str); 
      }
        // $text .= "\n";
        // $lines = explode("\n", $text);
        // $isHTML = false;
        // $isCode = false;
        // $line = '';
        // for ($i=0; $i < sizeof($lines); $i++) {
        //     $trimedLine = trim($lines[$i]);
        //     switch ($trimedLine) {
        //         case '<html>':
        //             $isHTML = true;
        //             $line = $trimedLine."\n";
        //             break;
        //         case '<style>':
        //             if (!$isHTML) {
        //                 $isHTML = true;
        //                 $line = $trimedLine."\n";
        //             } else {
        //                 $line .= $trimedLine."\n";
        //             }
        //             break;
        //         case '<code>':
        //             $isCode = true;
        //             $line = $trimedLine;
        //             break;
        //         case '':
        //             if ($isHTML) {
        //                 $isHTML = false;
        //             } elseif ($isCode) {
        //                 preg_match("/<code>(.*)<\/code>/s", $line, $quote);
                            // $code = htmlspecialchars($quote[1]);
                            // $code = str_replace('{', "&#123;", str_replace('}', "&#125;", $code));
        //                     $line = $this->config['code-wrap'][0].$code.$this->config['code-wrap'][1];
        //                 $isCode = false;
        //             } else {
        //                 $lenght = strlen($line);
        //                 if ($lenght > 0) {
        //                     $p = true;
        //                     foreach($this->blockDict as $pattern => $format){
        //                         preg_match($pattern, $line, $result);
        //                         if ($result) {
        //                             $p = false;
        //                             $line = preg_replace($pattern, $format, $line);
        //                             break;
        //                         }
        //                     }
        //                     $needFormating = true;
        //                     $result = $this->render_with_extensions($line);
        //                     if ($result[0]) {
        //                         $line = $result[0];
        //                         $needFormating = $result[1];
        //                     }
        //                     else{
        //                         wrapParagraph($line);
        //                     }
        //                     if ($needFormating) {
        //                         formatParagraph($line);
        //                     }
        //                     if ($p) {
                                
        //                     }
        //                 }
        //             }
        //             if ($line !== '<p></p>') {
        //                 $this->content[] = $line;
        //             }
        //             $line = '';
        //             break;
        //         default:
        //             if ($isHTML) {
        //                 $line .= $trimedLine."\n";
        //             } elseif ($isCode) {
        //                 $line .= $lines[$i] . "\n";
        //             } else {
        //                 if ($line === '') {
        //                     $line .= $trimedLine;
        //                 } else {
        //                     $line .= '<br>'.$trimedLine;
        //                 }
        //             }
        //             break;
        //     }
        // }
        // $renderedContent = implode($this->content, "\n");
        // unset($this->content);
        // return $renderedContent;

    private function wrapParagraph(string $line) : string
    {
        $class = '';
        if ($line[0] == '.') {
            $k;
            for ($k = 1; $k < $lenght; $k++) {
                if ($line[$k] !== ' ') {
                    $class .= $line[$k];
                } else {
                    break;
                }
            }
            $line = mb_substr($line, $k + 1, $lenght-$k);
        }
        if ($class !== '') {
            $line = '<p class="'.$class.'">'.$line.'</p>';
        } else {
            $line = '<p>'.$line.'</p>';
        }
        return $line;
    }
}
