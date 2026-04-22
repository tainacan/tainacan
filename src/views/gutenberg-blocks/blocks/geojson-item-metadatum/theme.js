import * as TainacanLeaflet from 'leaflet';
import 'leaflet/dist/leaflet.css';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

delete TainacanLeaflet.Icon.Default.prototype._getIconUrl;
TainacanLeaflet.Icon.Default.mergeOptions({
    iconRetinaUrl: iconRetinaUrl,
    iconUrl: iconUrl,
    shadowUrl: shadowUrl
});

const mapObserverOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const fitGeoJsonMap = (element) => {
    if (!element || !element.id || !window.tainacan_leaflet_maps || !window.tainacan_leaflet_maps[element.id])
        return;

    const mapEntry = window.tainacan_leaflet_maps[element.id];
    if (!mapEntry || !mapEntry.map || !mapEntry.layer)
        return;

    mapEntry.map.invalidateSize(true);
    const layerBounds = mapEntry.layer.getBounds && mapEntry.layer.getBounds();
    if (layerBounds && layerBounds.isValid()) {
        const maximumZoom = element.hasAttribute('data-maximum_zoom') ? Number(element.getAttribute('data-maximum_zoom')) : 12;
        mapEntry.map.flyToBounds(layerBounds, { maxZoom: maximumZoom, animate: false });
    }
};

const mapObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting)
            fitGeoJsonMap(entry.target);
    });
}, mapObserverOptions);

const parseGeoJsonAttribute = (element) => {
    if (!element || !element.hasAttribute('data-geojson'))
        return null;

    try {
        const parsed = JSON.parse(element.getAttribute('data-geojson'));
        if (!parsed || !parsed.type)
            return null;

        if (parsed.type === 'FeatureCollection' && Array.isArray(parsed.features))
            return parsed;

        if (parsed.type === 'Feature')
            return { type: 'FeatureCollection', features: [parsed] };
    } catch (error) {
        return null;
    }

    return null;
};

export default (element) => {
    if (!element || !element.id)
        return;

    const geojson = parseGeoJsonAttribute(element);
    if (!geojson || !geojson.features || !geojson.features.length)
        return;

    element.classList.add('tainacan-leaflet-map-container');
    element.classList.add('tainacan-geojson-map');
    element.style.setProperty('height', '320px');
    element.style.setProperty('width', '100%');
    element.style.setProperty('display', 'block');
    element.style.setProperty('z-index', '0');

    const initialLatitude = element.hasAttribute('data-initial_latitude') ? Number(element.getAttribute('data-initial_latitude')) : -14.4086569;
    const initialLongitude = element.hasAttribute('data-initial_longitude') ? Number(element.getAttribute('data-initial_longitude')) : -51.31668;
    const initialZoom = element.hasAttribute('data-initial_zoom') ? Number(element.getAttribute('data-initial_zoom')) : 5;
    const maximumZoom = element.hasAttribute('data-maximum_zoom') ? Number(element.getAttribute('data-maximum_zoom')) : 12;
    const mapProvider = element.hasAttribute('data-map_provider') ? element.getAttribute('data-map_provider') : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    const attribution = element.hasAttribute('data-attribution') ? element.getAttribute('data-attribution') : '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors';

    const tainacanMap = TainacanLeaflet.map(element.id).setView([initialLatitude, initialLongitude], initialZoom);

    TainacanLeaflet.tileLayer(mapProvider, {
        attribution: attribution,
        zoom: initialZoom,
        maxZoom: maximumZoom
    }).addTo(tainacanMap);

    const layer = TainacanLeaflet.geoJSON(geojson).addTo(tainacanMap);
    const layerBounds = layer.getBounds && layer.getBounds();
    if (layerBounds && layerBounds.isValid())
        tainacanMap.flyToBounds(layerBounds, { maxZoom: maximumZoom });

    mapObserver.observe(element);

    window.tainacan_leaflet_maps = typeof window.tainacan_leaflet_maps !== 'undefined' ? window.tainacan_leaflet_maps : {};
    window.tainacan_leaflet_maps[element.id] = {
        map: tainacanMap,
        layer: layer
    };
};
