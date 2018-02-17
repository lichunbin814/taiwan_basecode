<?php

$keys = array('CODEBASE', 'CODE1', 'CODE2', 'VILLCODE', 'TOWN_ID', 'COUNTY_ID');
$result = $meta = array();
foreach(glob('/home/kiang/Downloads/20180217172139009/*/*.shp') AS $shpFile) {
  $p = pathinfo($shpFile);
  $jsonFile = $p['dirname'] . '/' . $p['filename'] . '.json';
  if(!file_exists($jsonFile)) {
    exec("/usr/bin/ogr2ogr -f \"geoJSON\" -s_srs EPSG:3826 -t_srs EPSG:4326 {$jsonFile} {$shpFile}");
  }
  $json = json_decode(file_get_contents($jsonFile), true);
  foreach($json['features'] AS $f) {
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
    $path = "{$f['properties']['COUNTY_ID']}/{$f['properties']['TOWN_ID']}";
    if(!isset($result[$path])) {
      $result[$path] = array(
        'type' => 'FeatureCollection',
        'features' => array(),
      );
    }
    $result[$path]['features'][] = $f;
  }
}

ksort($meta);

file_put_contents(__DIR__ . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

foreach($result AS $path => $fc) {
  $parts = explode('/', $path);
  $fPath = __DIR__ . '/geo/' . $parts[0];
  if(!file_exists($fPath)) {
    mkdir($fPath, 0777, true);
  }
  file_put_contents($fPath . '/' . $parts[1] . '.json', json_encode($fc));
}

foreach(glob(__DIR__ . '/geo/*/*.json') AS $jsonFile) {
  $p = pathinfo($jsonFile);
  $targetPath = str_replace('/geo/', '/topo/', $p['dirname']);
  if(!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
  }
  exec("/usr/local/bin/mapshaper -i {$jsonFile} -o format=topojson {$targetPath}/{$p['basename']}");
}
