var popup = new ol.Overlay.Popup();
var areasUrl = 'areas.json';
var vectorLayer = new ol.layer.Vector({
    source: new ol.source.Vector({
        url: areasUrl,
        format: new ol.format.GeoJSON()
    }),
    style: new ol.style.Style({
        fill: new ol.style.Fill({
            color: 'rgba(192,0,192,0.6)'
        })
    })
});

function onLayerClick(e) {
    map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
        if (layer.getSource().getUrl() === areasUrl) {
            var p = feature.getProperties();
            popup.show(e.coordinate, '<div><h2>' + p.FNAME + '</h2></div>');
        }
    });
}
var projection = ol.proj.get('EPSG:3857');
var projectionExtent = projection.getExtent();
var size = ol.extent.getWidth(projectionExtent) / 256;
var resolutions = new Array(20);
var matrixIds = new Array(20);
for (var z = 0; z < 20; ++z) {
    // generate resolutions and matrixIds arrays for this WMTS
    resolutions[z] = size / Math.pow(2, z);
    matrixIds[z] = z;
}

/*
 * layer
 * EMAP2: 臺灣通用電子地圖透明
 * EMAP6: 臺灣通用電子地圖(不含等高線)
 * EMAP7: 臺灣通用電子地圖EN(透明)
 * EMAP8: Taiwan e-Map
 * PHOTO2: 臺灣通用正射影像
 * ROAD: 主要路網
 */
var baseLayer = new ol.layer.Tile({
    source: new ol.source.WMTS({
        matrixSet: 'EPSG:3857',
        format: 'image/png',
        url: 'http://maps.nlsc.gov.tw/S_Maps/wmts',
        layer: 'PHOTO2',
        tileGrid: new ol.tilegrid.WMTS({
            origin: ol.extent.getTopLeft(projectionExtent),
            resolutions: resolutions,
            matrixIds: matrixIds
        }),
        style: 'default',
        wrapX: true,
        attributions: '<a href="http://maps.nlsc.gov.tw/" target="_blank">國土測繪圖資服務雲</a>'
    }),
    opacity: 0.8
});

var map = new ol.Map({
    layers: [
        baseLayer,
        new ol.layer.Tile({
            source: new ol.source.WMTS({
                matrixSet: 'EPSG:3857',
                format: 'image/png',
                url: 'http://maps.nlsc.gov.tw/S_Maps/wmts',
                layer: 'ROAD',
                tileGrid: new ol.tilegrid.WMTS({
                    origin: ol.extent.getTopLeft(projectionExtent),
                    resolutions: resolutions,
                    matrixIds: matrixIds
                }),
                style: 'default',
                wrapX: true
            }),
            opacity: 0.3
        }),
        vectorLayer,
    ],
    target: 'map',
    controls: ol.control.defaults({
        attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
            collapsible: false
        })
    }),
    view: new ol.View({
        center: ol.proj.fromLonLat([121, 24]),
        zoom: 10
    })
});
map.addOverlay(popup);
map.on('singleclick', onLayerClick);