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
        
        TainacanLeaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        })
        .addTo(tainacanMap);

        coordinates.forEach(coordinate => {
            TainacanLeaflet.marker(coordinate).addTo(tainacanMap);
        });

        tainacanMap.panInsideBounds(coordinates);
    }
};