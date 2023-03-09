// TAINACAN LEAFLET MAP COMPONENT --------------------------------------------------------
//
// Counts on some HMTL markup to instantiate some leaflet maps 
import * as TainacanLeaflet from "leaflet";
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

        var tainacanMap = TainacanLeaflet.map(element.id).setView([-14.4086569, -51.31668], 5);
        
        const map_provider = element.hasAttribute('data-map_provider') ? element.getAttribute('data-map_provider') : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
        const attribution = element.hasAttribute('data-attribution') ? element.getAttribute('data-attribution') : '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors';
        const initial_zoom = element.hasAttribute('data-initial_zoom') ? element.getAttribute('data-initial_zoom') : 5;
        const maximum_zoom = element.hasAttribute('data-maximum_zoom') ? element.getAttribute('data-maximum_zoom') : 12;
 
        TainacanLeaflet.tileLayer(map_provider, {
            attribution: attribution,
            zoom: initial_zoom,
            maxZoom: maximum_zoom
        })
        .addTo(tainacanMap);

        coordinates.forEach(coordinate => {
            TainacanLeaflet.marker(coordinate).addTo(tainacanMap);
        });

        tainacanMap.flyToBounds(coordinates, { maxZoom: maximum_zoom });
    }
};