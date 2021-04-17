<?php

$targetPath = __DIR__ . '/topo';
if(!file_exists($targetPath)) {
  mkdir($targetPath, 0777, true);
}
foreach(glob(__DIR__ . '/geo/*.json') AS $jsonFile) {
  $p = pathinfo($jsonFile);
  $targetFile = "{$targetPath}/{$p['basename']}";
  if(!file_exists($targetFile)) {
    exec("/usr/local/bin/mapshaper -i {$jsonFile} -o format=topojson {$targetFile}");
  }
}
