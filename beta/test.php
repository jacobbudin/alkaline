<?php

$html = '<!-- START(PHOTOS) --><p>alsdkja<p><!-- END(PHOTOS) -->';

preg_match('(\<!--\s*START\(PHOTOS\)\s*-->(.*?)<!--\s*END\(PHOTOS\)\s*--\>)', $html, $matches);

print_r($matches);

?>