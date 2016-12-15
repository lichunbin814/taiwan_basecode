<?php

$areasFile = __DIR__ . '/areas.json';
if(!file_exists($areasFile)) {
  exec("/usr/bin/ogr2ogr -f \"geoJSON\" -s_srs EPSG:3826 -t_srs EPSG:4326 {$areasFile} G97_TW_U0201_2015.shp");
}

$areas = json_decode(file_get_contents($areasFile), true);
$keys = array('CODEBASE', 'CODE1', 'CODE2', 'VILLCODE', 'TOWN_ID', 'COUNTY_ID');
$result = $meta = array();
foreach($areas['features'] AS $f) {
  if(!isset($meta[$f['properties']['COUNTY_ID']])) {
    $meta[$f['properties']['COUNTY_ID']] = array(
      'name' => $f['properties']['COUNTY'],
      'cities' => array(),
    );
  }
  if(!isset($meta[$f['properties']['COUNTY_ID']]['cities'][$f['properties']['TOWN_ID']])) {
    $meta[$f['properties']['COUNTY_ID']]['cities'][$f['properties']['TOWN_ID']] = $f['properties']['TOWN'];
  }
  foreach($f['properties'] AS $k => $v) {
    if(!in_array($k, $keys)) {
      unset($f['properties'][$k]);
    }
  }
  $path = "{$f['properties']['COUNTY_ID']}";
  if(!isset($result[$path])) {
    $result[$path] = array(
      'type' => 'FeatureCollection',
      'features' => array(),
    );
  }
  $result[$path]['features'][] = $f;
}

file_put_contents(__DIR__ . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$fPath = __DIR__ . '/geo/';
if(!file_exists($fPath)) {
  mkdir($fPath, 0777, true);
}
foreach($result AS $path => $fc) {
  file_put_contents($fPath . '/' . $path . '.json', json_encode($fc));
}

$targetPath = __DIR__ . '/topo';
if(!file_exists($targetPath)) {
  mkdir($targetPath, 0777, true);
}
foreach(glob(__DIR__ . '/geo/*.json') AS $jsonFile) {
  $p = pathinfo($jsonFile);
  exec("/usr/local/bin/mapshaper -i {$jsonFile} -o format=topojson {$targetPath}/{$p['basename']}");
}
