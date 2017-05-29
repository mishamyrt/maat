<?php
header ('Content-Type: text/html; charset=utf-8');
error_reporting (E_ALL);
ini_set('display_errors', 1);
error_reporting(~0);

function stopwatch()
{
    list ($usec, $sec) = explode (' ', microtime ());
    return ((float) $usec + (float) $sec);
}

if (!include 'maat/maat.php') {
    die ('maat init failed');
}

$notes = '';
$files = glob('tests/*.{txt}', GLOB_BRACE);
foreach ($files as $file) {
    $notes .= '<div class="note">'."\n";
    $noteContent = file_get_contents($file);
    $Mt = new Maat;
    $testName = basename($file, ".txt");
    $notes .= '<h2 class="title">'.$testName.'</h2>'."\n";
    $notes .= '<div class="content">'."\n";
    $stopwatch = stopwatch();
    $notes .= $Mt->render($noteContent)."\n";
    $notes .= '<div class="info">'."\n";
    $notes .= stopwatch() - $stopwatch;
    $notes .= '</div>'."\n";
    $notes .= '</div>'."\n";
    $notes .= '</div>'."\n";
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Maat test</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <?php echo $notes ?>
  </body>
</html>