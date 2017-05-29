<?php
interface MaatGroup {
  function render ($content, $config);
}
class Maat {
  private $extensions = array();
  private $content = array();
  private $config = array();
  protected $dict = array(
    "/\(\(([^\(\)]*) ([^\(\)]*)\)\)/" => '<a href="$1">$2</a>',
    "/((?:https?|ftps?)\:\/\/[\w\d\#\.\/&=%-_!\?\@\*][^\s<>\"]*)(\s)/" => '<a href="$1">$1</a> ',
    "/((?:https?|ftps?)\:\/\/[\w\d\#\.\/&=%-_!\?\@\*][^\s<>\"]*)(<)/" => '<a href="$1">$1</a><',
    "/<p><br>/" => "<p>", //paragraph fix
    "/<br><\/p>/" => "</p>", //paragraph fix
    "/\*\*([^\*]*)\*\*/" => "<b>$1</b>", //bold
    "/\/\/([^\/*]*)\/\//" => "<i>$1</i>", //italic
    "/<p>>(.*)<\/p>/" => '<blockquote>$1</blockquote>',
    "/<p># (.*)<\/p>/" => '<h2>$1</h2>',
    "/<p>## (.*)<\/p>/" => '<h3>$1</h3>'
  );
  function __construct () {
    $this->config = include('config.php');
    $this->config['cwd'] = getcwd();
    $extensions = glob($this->config['directory'].'maat/extensions/*.php', GLOB_BRACE);
    foreach($extensions as $extension) {
      $this->load_extension($extension);
    }
  }
  function load_extension ($file) {
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
  function group_render($line){
    $render = $line;
    foreach ($this->extensions as $extension) {
      $render = $extension['instance']->render($render, $this->config);
    }
    return $render;
  }
  public function render($text){
    $lines = explode("\n", $text);
    $isHeader = false;
    $isList = false;
    $line = '';
    for ($i=0; $i < count($lines); $i++) {
      if ($lines[$i] !== '') {
        $line .= '<br>'.$lines[$i];
      }
      else {
        $line = '<p>'.$line.'</p>';
        if (!$isHeader && !$isList){
          $patterns = array_keys($this->dict);
          $values = array_values($this->dict);
          $line = str_replace($line, preg_replace($patterns, $values, $line) , $line);
          $line = $this->group_render($line);
        }
        $this->content[] = $line;
        $line = '';
      }
    }
    $renderedContent = implode($this->content, "\n");
    return $renderedContent;
  }
}
