// TAINACAN LEAFLET MAP COMPONENT --------------------------------------------------------
//
// Counts on some HMTL markup to instantiate some leaflet maps
import { Map, TileLayer, Marker, Icon } from 'leaflet';
import 'leaflet/dist/leaflet.css';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

const defaultMarkerIcon = new Icon({
    iconRetinaUrl: iconRetinaUrl,
    iconUrl: iconUrl,
    shadowUrl: shadowUrl,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

// Observes the visibility of the map container to resize the map when it becomes visible
const mapObserverOptions = {
    root: null, // use the viewport
    rootMargin: '0px',
    threshold: 0.1 // 10% of the element is visible
};

// The mapObserver repeats part of the initialization logic to prevent the map from looking broke
// when it becomes visible after being hidden, for example inside section tabs
const mapObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            if (
                entry &&
                entry.target.id &&
                window.tainacan_leaflet_maps &&
                window.tainacan_leaflet_maps[entry.target.id]
            ) {
                const element = entry.target;

                const children = element.children ? element.children : [];
                if ( !children.length )
                    return;

                const coordinates = [];
                for (let i = 0; i < children.length; i++) {
                    if ( children[i].hasAttribute('data-latitude') && children[i].hasAttribute('data-longitude') )
                        coordinates.push([children[i].getAttribute('data-latitude'), children[i].getAttribute('data-longitude')]);
                }

                if ( !coordinates.length )
                    return;

                const maximum_zoom = element.hasAttribute('data-maximum_zoom') ? element.getAttribute('data-maximum_zoom') : 12;

                window.tainacan_leaflet_maps[element.id].invalidateSize(true);
                window.tainacan_leaflet_maps[element.id].flyToBounds(coordinates, { maxZoom: maximum_zoom, animate: false });
            }
        }
    });
}, mapObserverOptions);

/* Loads and instantiates map components passed to data-module="geocoordinate-item-metadatum"*/
export default (element) => {
    if (element && element.id) {

        const children = element.children ? element.children : [];
        if ( !children.length )
            return;

        const coordinates = [];
        for (let i = 0; i < children.length; i++) {
            if ( children[i].hasAttribute('data-latitude') && children[i].hasAttribute('data-longitude') )
                coordinates.push([children[i].getAttribute('data-latitude'), children[i].getAttribute('data-longitude')]);
        }

        if ( !coordinates.length )
            return;

        // Sets basic css that should be here only if this javascript is loaded.
        element.classList.add('tainacan-leaflet-map-container');
        element.style.setProperty('height', '320px');
        element.style.setProperty('width', '100%');
        element.style.setProperty('display', 'block');
        element.style.setProperty('z-index', '0');

        const tainacanMap = new Map(element.id).setView([-14.4086569, -51.31668], 5);

        const map_provider = element.hasAttribute('data-map_provider') ? element.getAttribute('data-map_provider') : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
        const attribution = element.hasAttribute('data-attribution') ? element.getAttribute('data-attribution') : '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors';
        const initial_zoom = element.hasAttribute('data-initial_zoom') ? element.getAttribute('data-initial_zoom') : 5;
        const maximum_zoom = element.hasAttribute('data-maximum_zoom') ? element.getAttribute('data-maximum_zoom') : 12;

        new TileLayer(map_provider, {
            attribution: attribution,
            zoom: initial_zoom,
            maxZoom: maximum_zoom
        })
        .addTo(tainacanMap);

        coordinates.forEach(coordinate => {
            new Marker(coordinate, { icon: defaultMarkerIcon })
                .bindPopup(`${coordinate[0]}, ${coordinate[1]}`)
                .addTo(tainacanMap);
        });

        tainacanMap.flyToBounds(coordinates, { maxZoom: maximum_zoom });

        mapObserver.observe(element);

        // Stores referenced to the leaflet instances to manipulate them via the window object inside the observer
        window.tainacan_leaflet_maps = typeof window.tainacan_leaflet_maps != "undefined" ? window.tainacan_leaflet_maps : {};
        window.tainacan_leaflet_maps[element.id] = tainacanMap;
    }
};
