<?php

$key = 'VILLAGE_MOI_';
foreach (glob(__DIR__ . '/shp/*/*.shp') as $shpFile) {
  $p = pathinfo($shpFile);
  $y = pathinfo($p['dirname']);
  $geoFile = __DIR__ . '/geo/' . $y['filename'] . '.json';
  if (!file_exists($geoFile)) {
    exec('/usr/bin/ogr2ogr -f "GeoJSON" ' . $geoFile . ' ' . $shpFile);
    //exec('/usr/bin/ogr2ogr -t_srs EPSG:4326 -s_srs EPSG:3826 -f "GeoJSON" -lco ENCODING=UTF-8 ' . $geoFile . ' ' . $shpFile);
  }
}

$targetPath = __DIR__ . '/topo';
if (!file_exists($targetPath)) {
  mkdir($targetPath, 0777, true);
}
foreach (glob(__DIR__ . '/geo/*.json') as $jsonFile) {
  $p = pathinfo($jsonFile);
  $targetFile = "{$targetPath}/{$p['basename']}";
  if (!file_exists($targetFile)) {
    exec("/usr/local/bin/mapshaper -i {$jsonFile} -o format=topojson {$targetFile}");
  }
}