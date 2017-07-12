<?php
return array(
  'folder' => './maat',
  'profiles' => array(
    'rss' => array(
      'basic-html' => true,
      'banned-extensions' => ['onlinevideo', 'hipsterquote']
    )
  ),
  'extensions' => array(
    'image' => array(
      'src-prefix' => '',
      'folder' => '/img/pictures/',
      'wrapper-class' => 'img-wrapper',
      'max-width' => 1600,
      'container-class' => 'img-container honey'
    ),
    'onlinevideo' => array(
      'container-class' => 'video-container',
      'folder' => '/img/previews/',
      'max-width' => 1600,
      'wrapper-class' => 'video-wrapper'
    )
  ),
  'basic-html' => false,
  'banned-extensions' => array()
);
