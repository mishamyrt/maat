<?php
declare (strict_types = 1);
namespace Maat;

interface Extension
{
    function render(array $group) : string;
}
class Renderer
{
    private $extensions = [];
    private $content = [];
    private $triggers = [];
    public $config = [];
    private $lineDict = [
        '/\(\(([^\(\)\s]*)\s([^\(\)]*)\)\)/' => '<a href="$1">$2</a>',
        '/(^|\s)((?:https?|ftps?)\:\/\/[\w\d\#\.\/&=%-_!\?\@\*][^\s<>\"\,]*)/' => '$1<a href="$2">$2</a>',
        '/\*\*([^\*]*)\*\*/' => '<b>$1</b>',
        '/\/\/([^\/\"]+)\/\//' => '<i>$1</i>',
        '/--([^\/\"]+)--/' => '<s>$1</s>',
        '/\b[A-ZА-ЯЁ][A-ZА-ЯЁ]+\b[\.\s]/u' => '<span class="caps">$0</span>'
    ];
    private $blockDict = [
        ["/^>\s*(.*)/", '<blockquote><p>$1</p></blockquote>'],
        ["/^##\s*(.*)/", '<h3>$1</h3>'],
        ["/^#\s*(.*)/", '<h2>$1</h2>']
    ];

    function __construct(string $profile = '')
    {
        $this->config = require __DIR__ . '/config.php';
        if (isset($this->config['profiles'][$profile])) {
            foreach ($this->config['profiles'][$profile] as $key => $value) {
                $this->config[$key] = $value;
            }
            $this->config['profile'] = $profile;
        } elseif ($profile !== '') {
            echo 'There is no profile "' . $profile . '", falling back to default';
        }
        $extensions = glob(__DIR__ . '/extensions/*.php', GLOB_BRACE);
        for ($i = 0; $i < sizeof($extensions); $i++) {
            $this->load_extension($extensions[$i]);
        }
    }

    private function render_with_extension(string $line) : array
    {
        for ($i = 0; $i < sizeof($this->triggers); $i++) {
            preg_match($this->triggers[$i][2], $line, $result);
            if ($result) {
                $group = [
                    'class' => $this->triggers[$i][1],
                    'class-data' => $result,
                    'line' => $line,
                    'config' => isset($this->config['extensions'][$this->triggers[$i][0]]) ? $this->config['extensions'][$this->triggers[$i][0]] : ''
                ];
                return [
                    $this->extensions[$this->triggers[$i][0]]->render($group),
                    $this->triggers[$i][3]
                ];
            }
        }
        return [false, false];
    }

    private function load_extension(string $file) : bool
    {
        $name = basename($file, ".php");
        $MaatExtensionClass = 'MaatExtension_' . $name;
        require $file;
        $this->extensions[$name] = new $MaatExtensionClass($this);
        return true;
    }
    public function define_trigger(string $extension, string $class, string $regex, bool $formatting) : bool
    {
        if (!in_array($extension, $this->config['banned-extensions'], true)) {
            $this->triggers[] = [$extension, $class, '/^' . $regex . '/', $formatting];
        }
        return true;
    }
    public function render(string $text) : string
    {
        if (trim($text) === '') {
            return '';
        }
        $text .= "\n";
        $lines = explode("\n", $text);
        $linePatterns = array_keys($this->lineDict);
        $lineValues = array_values($this->lineDict);
        $isHTML = false;
        $isCode = false;
        $line = '';
        for ($i = 0; $i < sizeof($lines); $i++) {
            $trimedLine = trim($lines[$i]);
            switch ($trimedLine) {
                case '<html>':
                    $isHTML = true;
                    $line = $trimedLine . "\n";
                    break;
                case '<style>':
                    if (!$isHTML) {
                        $isHTML = true;
                        $line = $trimedLine . "\n";
                    } else
                        $line .= $trimedLine . "\n";
                    break;
                case '<code>':
                    $isCode = true;
                    $line = $trimedLine;
                    break;
                case '':
                    if ($isHTML) {
                        $isHTML = false;
                    } elseif ($isCode) {
                        preg_match("/<code>(.*)<\/code>/s", $line, $quote);
                        $code = htmlspecialchars($quote[1]);
                        $code = str_replace('{', "&#123;", str_replace('}', "&#125;", $code));
                        $line = $this->config['code-wrap'][0] . $code . $this->config['code-wrap'][1];
                        $isCode = false;
                    } else {
                        $lenght = strlen($line);
                        if ($lenght > 0) {
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
                            $result = $this->render_with_extension($line);
                            if ($result[0] !== false) {
                                $p = false;
                                $line = $result[0];
                                $needFormating = $result[1];
                            }
                            if ($needFormating) {
                                $line = preg_replace($linePatterns, $lineValues, $line);
                            }
                            if ($p) {
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
                                    $line = mb_substr($line, $k + 1, $lenght - $k);
                                }
                                if ($class !== '') {
                                    $line = '<p class="' . $class . '">' . $line . '</p>';
                                } else {
                                    $line = '<p>' . $line . '</p>';
                                }
                            }
                        }
                    }
                    if ($line !== '<p></p>') {
                        $this->content[] = $line;
                    }
                    $line = '';
                    break;
                default:
                    if ($isHTML) {
                        $line .= $trimedLine . "\n";
                    } elseif ($isCode) {
                        $line .= $lines[$i] . "\n";
                    } else {
                        if ($line === '') {
                            $line .= $trimedLine;
                        } else {
                            $line .= '<br>' . $trimedLine;
                        }
                    }
                    break;
            }
        }
        $renderedContent = implode($this->content, "\n");
        unset($this->content);
        return $renderedContent;
    }
}
