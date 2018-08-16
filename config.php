<?php
return array(
  'folder' => './',
  'profiles' => array(
    'rss' => array(
      'basic-html' => true,
      'banned-extensions' => ['hipsterquote'],
      'code-wrap' => array ('<pre><code>', '</code></pre>'), 
    )
  ),
  'extensions' => array(
    'image' => array(
      'src-prefix' => '',
      'folder' => '/img/pictures/',
      'wrapper-class' => 'note-img-wrapper',
      'max-width' => 1600,
      'container-class' => 'note-img honey'
    ),
    'onlinevideo' => array(
      'container-class' => 'note-video-container',
      'folder' => '/img/previews/',
      'max-width' => 1600,
      'wrapper-class' => 'note-video-wrapper'
    )
  ),
  'basic-html' => false,
  'code-wrap' => array ('<pre class="code-container"><code>', '</code></pre>'), 
  'banned-extensions' => array()
);
