<?php

$basePath = dirname(__DIR__);
foreach(glob($basePath . '/cunli/geo/*') AS $geoFile) {
  $geo = json_decode(file_get_contents($geoFile), true);
  $fc = [];
  foreach($geo['features'] AS $f) {
    $key = $f['properties']['TOWN_ID'];
    if(!isset($fc[$key])) {
      $fc[$key] = [];
    }
    $fc[$key][] = $f;
  }
}
exit();
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
