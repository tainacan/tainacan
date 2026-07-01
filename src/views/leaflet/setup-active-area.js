/**
 * Initializes Leaflet V1 polyfills and the leaflet-active-area plugin for map
 * view modes that offset the visible area to account for sidebar cards.
 */
import L from 'leaflet';
import './leaflet-v1-polyfill.js';

window.L = L;
globalThis.applyAllPolyfills();

import 'leaflet-active-area/src/leaflet.activearea.js';

export default L;
