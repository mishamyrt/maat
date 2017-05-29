<?php
header ('Content-Type: text/html; charset=utf-8');
error_reporting (E_ALL);
ini_set('display_errors', 1);
error_reporting(~0);

function stopwatch () {
  list ($usec, $sec) = explode (' ', microtime ());
  return ((float) $usec + (float) $sec);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <style media="screen">
      body{
        font-family: sans-serif;
        font-size: 19px;
        line-height: 27px;
      }
      .video-container {
        max-width: 1200px;
        position: relative;
        margin-bottom: 18px;
        padding-bottom: 56.25%;
        height: 0;
      }
      .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
      }
      .info {
        color: #939393;
        line-height: 15px;
      }
      .note p{
            max-width: 42em;
      }
      .note{
        margin-bottom: 70px;
        margin-left: 8%;
      }
      .img-wrapper img{
        width: 100%;
        position: absolute;
      }
      .img-wrapper {
        position: relative;
      }
    </style>
    <meta charset="utf-8">
    <title>Maat test</title>
  </head>
  <body>
<?php
    if (!include 'maat/maat.php') die ('maat init failed');
    $files = glob('tests/*.{txt}', GLOB_BRACE);
    foreach($files as $file) {
?>
<div class="note">
  <?php
  $Mt = new Maat;
  $testName = basename($file, ".txt");
  echo "<h2>".$testName."</h2>";
?>
  <div class="content">
    <?php
  $note = file_get_contents($file);
  $stopwatch = stopwatch();
  echo $Mt->render($note);
     ?>
  </div>
  <div class="info">
    Execution time:
    <?php
echo stopwatch() - $stopwatch;
     ?>
  </div>
</div>
<?php } ?>
  </body>
</html>
