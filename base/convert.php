<?php

foreach(glob(__DIR__ . '/geo/*/*.json') AS $jsonFile) {
  $p = pathinfo($jsonFile);
  $targetPath = str_replace('/geo/', '/topo/', $p['dirname']);
  if(!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
  }
  exec("/usr/local/bin/mapshaper -i {$jsonFile} -o format=topojson {$targetPath}/{$p['basename']}");
}

exit();

$areasFile = __DIR__ . '/areas.json';
if(!file_exists($areasFile)) {
  exec("/usr/bin/ogr2ogr -f \"geoJSON\" -s_srs EPSG:3826 -t_srs EPSG:4326 {$areasFile} 臺灣_最小統計區_面.shp");
}

/*
[properties] => Array
    (
        [U_ID] => 767
        [CODEBASE] => A9206-0002-00
        [CODE1] => A9206-02-001
        [CODE2] => A9206-02
        [VILLCODE] => A9206-002-00
        [VILLAGE_ID] => 002
        [VILLAGE] => 小坵村
        [TOWN_ID] => 09020060
        [TOWN] => 烏坵鄉
        [COUNTY_ID] => 09020
        [COUNTY] => 金門縣
        [USE_CODE] => 07
        [LINE] => 01
        [SPECODE] =>
        [X] => 95766.46276
        [Y] => 2764695.33383
        [AREA] => 971093.66654
        [M] =>
        [USECODE] =>
    )
*/

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
  $path = "{$f['properties']['COUNTY_ID']}/{$f['properties']['TOWN_ID']}";
  if(!isset($result[$path])) {
    $result[$path] = array(
      'type' => 'FeatureCollection',
      'features' => array(),
    );
  }
  $result[$path]['features'][] = $f;
}

file_put_contents(__DIR__ . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

foreach($result AS $path => $fc) {
  $parts = explode('/', $path);
  $fPath = __DIR__ . '/geo/' . $parts[0];
  if(!file_exists($fPath)) {
    mkdir($fPath, 0777, true);
  }
  file_put_contents($fPath . '/' . $parts[1] . '.json', json_encode($fc));
}
