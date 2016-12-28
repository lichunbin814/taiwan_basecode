<?php

$json = json_decode(file_get_contents(__DIR__ . '/city.geo.json'), true);
foreach ($json['features'] AS $k => $f) {
    if (strlen($json['features'][$k]['properties']['COUNTYCODE']) === 2) {
        $json['features'][$k]['properties']['COUNTYCODE'] = $json['features'][$k]['properties']['COUNTYCODE'] . '000';
        $json['features'][$k]['properties']['TOWNCODE'] = $json['features'][$k]['properties']['COUNTYCODE'] . substr($json['features'][$k]['properties']['TOWNCODE'], 3, 3);
    } elseif(strlen($json['features'][$k]['properties']['TOWNCODE']) !== 8) {
        $json['features'][$k]['properties']['TOWNCODE'] = $json['features'][$k]['properties']['COUNTYCODE'] . str_pad(substr($json['features'][$k]['properties']['TOWNCODE'], 5), 3, '0', STR_PAD_RIGHT);
    }
}

file_put_contents(__DIR__ . '/city.geo.json', json_encode($json));