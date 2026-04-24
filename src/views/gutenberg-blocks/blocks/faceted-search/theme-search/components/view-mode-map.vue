<template>
    <div class="table-container">
        <div class="table-wrapper">

            <!-- Empty result placeholder, rendered in the parent component -->
            <slot />
            
            <!-- MAP VIEW MODE -->
            <div 
                    class="tainacan-leaflet-map-container"
                    :class="{ 'has-selected-item': mapSelectedItemId }">
                <ul class="tainacan-map-cards-container">
                    <li
                            v-for="(item, index) of items"
                            :key="item.id"
                            :aria-setsize="totalItems"
                            :aria-posinset="getPosInSet(index)"
                            :data-tainacan-item-id="item.id"
                            @mouseenter="hoveredMapCardItemId = item.id"
                            @mouseleave="hoveredMapCardItemId = false">
                        <div 
                                :class="{
                                    'clicked-map-card': mapSelectedItemId == item.id,
                                    'non-located-item': !itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id)
                                }"
                                class="tainacan-map-card"
                                @click.prevent.stop="showLocationsByItem(item)">

                            <!-- JS-side hook for extra content -->
                            <div 
                                    v-if="hasBeforeHook()"
                                    class="faceted-search-hook faceted-search-hook-item-before"
                                    v-html="getBeforeHook(item)" />
                                
                            <!-- Title -->
                            <div 
                                    class="metadata-title"
                                    :style="{
                                        'cursor': !itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id) ? 'auto' : 'pointer',
                                    }">
                                <p 
                                        v-if="collectionId && titleItemMetadatum"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.metadata != undefined ? renderMetadata(item, titleItemMetadatum) : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'top-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"
                                        v-html="item.metadata != undefined ? renderMetadata(item, titleItemMetadatum) : ''" />                 
                                <p 
                                        v-if="!collectionId && titleItemMetadatum"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.title != undefined ? item.title : (`<span class='has-text-grey is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                            html: true,
                                            autoHide: false,
                                            placement: 'top-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"
                                        v-html="item.title != undefined ? item.title : (`<span class='has-text-grey is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />                             
                                <div class="tainacan-map-card-thumbnail">
                                    <blur-hash-image
                                            v-if="item.thumbnail != undefined"
                                            class="tainacan-map-card-item-thumbnail"
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-small', 40)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-small', 40)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-small')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)"
                                            :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-small', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : ''"
                                            :transition-duration="500"
                                        />
                                </div>
                                <span 
                                        v-if="isSlideshowViewModeEnabled"
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 100,
                                            },
                                            content: $i18n.get('label_see_on_fullscreen'),
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"
                                        role="button"
                                        tabindex="0"
                                        :aria-label="$i18n.get('label_see_on_fullscreen')"
                                        class="icon slideshow-icon"
                                        @click.prevent="starSlideshowFromHere(index)"
                                        @keydown.enter.prevent="starSlideshowFromHere(index)"
                                        @keydown.space.prevent="starSlideshowFromHere(index)">
                                    <i
                                            class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"
                                            aria-hidden="true" />
                                </span> 
                            </div>

                            <!-- JS-side hook for extra content -->
                            <div 
                                    v-if="hasAfterHook()"
                                    class="faceted-search-hook faceted-search-hook-item-after"
                                    v-html="getAfterHook(item)" />
                        </div>
                    </li>
                </ul>
                <l-map 
                        :id="'tainacan-view-mode-map'"
                        :ref="'tainacan-view-mode-map'"
                        style="height: 60vh; width: 100%;"
                        :zoom="5"
                        :center="[-14.4086569, -51.31668]"
                        :zoom-animation="true"
                        :options="{
                            name: 'tainacan-view-mode-map',
                            zoomControl: false
                        }"
                        @click="clearSelectedMarkers()"
                        @ready="onMapReady()">
                    <l-tile-layer 
                            :url="mapTileUrl" 
                            :attribution="mapTileAttribution" />
                    <l-geo-json
                            :key="'non-points-' + mapSelectedItemId + '-' + selectedMarkerIndexes.join('-')"
                            :geojson="nonPointGeoJsonCollection"
                            :options-style="nonPointGeoJsonStyle"
                            :options="nonPointGeoJsonOptions" />
                    <l-marker 
                            v-for="pointLocation of pointLocations"
                            :key="pointLocation.locationIndex"
                            :lat-lng="pointLocation.location"
                            :opacity="selectedMarkerIndexes.length > 0 && !selectedMarkerIndexes.includes(pointLocation.locationIndex) ? 0.35 : 1.0"
                            @click="showItemByLocation(pointLocation.locationIndex)">
                        <l-icon 
                                :icon-retina-url="mapIconRetinaUrl"
                                :icon-url="mapIconUrl"
                                :shadow-url="mapIconShadowUrl"
                                :icon-size="(pointLocation.item.id == hoveredMapCardItemId || pointLocation.item.id == mapSelectedItemId) ? [25, 41] : [16, 28]"
                                :shadow-size="(pointLocation.item.id == hoveredMapCardItemId || pointLocation.item.id == mapSelectedItemId) ? [41, 41] : [28, 28]"
                                :icon-anchor="(pointLocation.item.id == hoveredMapCardItemId || pointLocation.item.id == mapSelectedItemId) ? [12, 41] : [8, 28]"
                                :tooltip-anchor="(pointLocation.item.id == hoveredMapCardItemId || pointLocation.item.id == mapSelectedItemId) ? [16, -28] : [8, -21]"
                                :popup-anchor="(pointLocation.item.id == hoveredMapCardItemId || pointLocation.item.id == mapSelectedItemId) ? [1, -34] : [1, -25]" />
                        <l-tooltip>
                            <div
                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                    :key="metadatumIndex">
                                <div 
                                        v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                        style="font-weight: bold;"
                                        v-html="(pointLocation.item.metadata != undefined && collectionId ? renderMetadata(pointLocation.item, column) : (pointLocation.item.title ? pointLocation.item.title :`<span class='has-text-grey is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)) + getMultivalueIndicator(pointLocation)" />
                                <div 
                                        v-if="column.display && column.metadata_type == 'Tainacan\\Metadata_Types\\Compound' && selectedGeocoordinateMetadatum.parent == column.id"
                                        v-html="pointLocation.item.metadata != undefined ? renderMetadata(pointLocation.item, column, pointLocation.multivalueIndex) : ''" />
                            </div>
                        </l-tooltip>
                    </l-marker>
                    <l-control-zoom position="bottomright" />
                    <l-control 
                            :disable-scroll-propagation="true"
                            :disable-click-propagation="true"
                            position="topleft">
                        <div class="geocoordinate-panel">
                            <div 
                                    v-if="Object.keys(geocoordinateMetadata).length"
                                    class="geocoordinate-panel--input">
                                <label>{{ $i18n.get('label_showing_locations_for') }}&nbsp;</label>
                                <div 
                                        id="tainacan-select-geocoordinate-metatum"
                                        class="control tainacan-select-map-metadatum">
                                    <!-- Not using B-Select here to avoid importing Bulme on view modes inside Gutenberg blocks -->
                                    <span class="select">
                                        <select
                                                v-model="selectedGeocoordinateMetadatumId"
                                                :placeholder="$i18n.get('instruction_select_geocoordinate_metadatum')">
                                            <option
                                                    v-for="(geocoordinateMetadatum, geocoordinateMetadatumId) in geocoordinateMetadata"
                                                    :key="geocoordinateMetadatum.id"
                                                    role="button"
                                                    :class="{ 'is-active': selectedGeocoordinateMetadatumId == geocoordinateMetadatumId }"
                                                    :value="geocoordinateMetadatumId"
                                                    @click="onChangeSelectedGeocoordinateMetadatum(geocoordinateMetadatumId)">
                                                {{ geocoordinateMetadatum.name }}
                                            </option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <section 
                                    v-else
                                    class="section">
                                <div class="content has-text-dark has-text-centered">
                                    <p style="margin-bottom: 0px">
                                        <span 
                                                aria-hidden="true"
                                                class="icon is-large">
                                            <i>
                                                <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="var(--tainacan-info-color, #505253)"
                                                        width="2.875em"
                                                        height="2.875em">
                                                    <path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.34,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.66,20.97L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z" />
                                                </svg>
                                            </i>
                                        </span>
                                    </p>
                                    <p>{{ $i18n.get('info_empty_geocoordinate_metadata_list') }}</p>
                                </div>
                            </section>
                        </div>
                    </l-control>
                    <l-control
                            v-if="selectedMarkerIndexes.length || mapSelectedItemId"
                            :disable-scroll-propagation="true"
                            :disable-click-propagation="true"
                            position="topleft"
                            class="tainacan-records-container tainacan-records-container--map">
                        <button 
                                v-tooltip="{
                                    content: $i18n.get('label_clean'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                type="button"
                                :aria-label="$i18n.get('label_clean')"
                                class="tainacan-records-close-button"
                                @click="clearSelectedMarkers()">
                            <span
                                    aria-hidden="true"
                                    class="icon">
                                <i
                                        class="tainacan-icon tainacan-icon-close"
                                        aria-hidden="true" />
                            </span>
                        </button>
                        <transition-group
                                tag="ul"
                                name="appear">
                            <li
                                    v-for="(item, index) of items.filter(anItem => mapSelectedItemId == anItem.id)"
                                    :key="item.id"
                                    :aria-setsize="totalItems"
                                    :aria-posinset="getPosInSet(index)"
                                    :data-tainacan-item-id="item.id">
                                <a 
                                        :href="getItemLink(item.url, index)"
                                        :class="{
                                            'non-located-item': !itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id)
                                        }"
                                        class="tainacan-record">

                                    <!-- JS-side hook for extra content -->
                                    <div 
                                            v-if="hasBeforeHook()"
                                            class="faceted-search-hook faceted-search-hook-item-before"
                                            v-html="getBeforeHook(item)" />
                                        
                                    <!-- Title -->
                                    <div class="metadata-title">
                                        <span
                                                v-if="itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id) && selectedGeocoordinateMetadatum.slug"
                                                id="button-show-location"
                                                v-tooltip="{
                                                    content: $i18n.get('label_show_item_location_on_map'),
                                                    autoHide: true,
                                                    placement: 'auto',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"
                                                class="icon"
                                                style="margin: -1px 4px 0px 0px;"
                                                :aria-label="$i18n.get('label_show_item_location_on_map')" 
                                                @click.prevent.stop="showLocationsByItem(item)">
                                            <svg
                                                    style="width: 1.125em;height: 1.125em;"
                                                    viewBox="0 0 24 24">
                                                <path
                                                        fill="currentColor"
                                                        d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M3.05,13H1V11H3.05C3.5,6.83 6.83,3.5 11,3.05V1H13V3.05C17.17,3.5 20.5,6.83 20.95,11H23V13H20.95C20.5,17.17 17.17,20.5 13,20.95V23H11V20.95C6.83,20.5 3.5,17.17 3.05,13M12,5A7,7 0 0,0 5,12A7,7 0 0,0 12,19A7,7 0 0,0 19,12A7,7 0 0,0 12,5Z" />
                                            </svg>
                                        </span>
                                        <p 
                                                v-if="collectionId && titleItemMetadatum"
                                                v-tooltip="{
                                                    delay: {
                                                        show: 500,
                                                        hide: 300,
                                                    },
                                                    content: item.metadata != undefined ? renderMetadata(item, titleItemMetadatum) : '',
                                                    html: true,
                                                    autoHide: false,
                                                    placement: 'auto-start',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"
                                                v-html="item.metadata != undefined ? renderMetadata(item, titleItemMetadatum) : ''" />                 
                                        <p 
                                                v-if="!collectionId && titleItemMetadatum"
                                                v-tooltip="{
                                                    delay: {
                                                        show: 500,
                                                        hide: 300,
                                                    },
                                                    content: item.title != undefined ? item.title : (`<span class='has-text-grey is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                                    html: true,
                                                    autoHide: false,
                                                    placement: 'auto-start',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"
                                                v-html="item.title != undefined ? item.title : (`<span class='has-text-grey is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />            
                                        <span 
                                                v-if="isSlideshowViewModeEnabled"
                                                v-tooltip="{
                                                    delay: {
                                                        show: 500,
                                                        hide: 100,
                                                    },
                                                    content: $i18n.get('label_see_on_fullscreen'),
                                                    placement: 'auto-start',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"
                                                role="button"
                                                tabindex="0"
                                                :aria-label="$i18n.get('label_see_on_fullscreen')"
                                                class="icon slideshow-icon"
                                                @click.prevent="starSlideshowFromHere(index)"
                                                @keydown.enter.prevent="starSlideshowFromHere(index)"
                                                @keydown.space.prevent="starSlideshowFromHere(index)">
                                            <i
                                                    class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"
                                                    aria-hidden="true" />
                                        </span> 
                                    </div>

                                    <!-- Remaining metadata -->  
                                    <div class="media">
                                        <div class="list-metadata media-body">
                                            <div 
                                                    v-if="item.thumbnail != undefined"
                                                    class="tainacan-record-thumbnail">
                                                <blur-hash-image
                                                        v-if="item.thumbnail != undefined"
                                                        class="tainacan-record-item-thumbnail"
                                                        :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                                        :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                                        :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                                        :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                                        :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                                        :alt="item.thumbnail_alt ? item.thumbnail_alt : ''"
                                                        :transition-duration="500"
                                                        @click.left="onClickItem($event, item)"
                                                    />
                                                <div 
                                                        :style="{ 
                                                            minHeight: getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px',
                                                            marginTop: '-' + getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px'
                                                        }" />
                                            </div>
                                            <template 
                                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                                    :key="metadatumIndex">
                                                <span 
                                                        v-if="renderMetadata(item, column) != '' &&
                                                            column.display && column.slug != 'thumbnail' &&
                                                            column.metadata_type_object != undefined &&
                                                            (column.metadata_type_object.related_mapped_prop != 'title') &&
                                                            (column.metadata_type != 'Tainacan\\Metadata_Types\\GeoCoordinate') &&
                                                            (column.metadata_type != 'Tainacan\\Metadata_Types\\GeoJSON') "
                                                        :class="{ 'metadata-type-textarea': column.metadata_type_object.component == 'tainacan-textarea' }">
                                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                                    <p      
                                                            class="metadata-value"
                                                            v-html="renderMetadata(item, column)" />
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- JS-side hook for extra content -->
                                    <div 
                                            v-if="hasAfterHook()"
                                            class="faceted-search-hook faceted-search-hook-item-after"
                                            v-html="getAfterHook(item)" />
                                </a>
                            </li>
                        </transition-group>
                    </l-control>
                </l-map>
            </div>
        </div> 
    </div>
</template>

<script>
import { viewModesMixin } from '../js/view-modes-mixin.js';
import { LMap, LIcon, LTooltip, LTileLayer, LMarker, LControl, LControlZoom, LGeoJson } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';
import { latLng, geoJSON } from 'leaflet';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';
import * as LeafletActiveArea from 'leaflet-active-area';

export default {
    name: 'ViewModeMap',
    components: {
        LMap,
        LIcon,
        LTooltip,
        LTileLayer,
        LMarker,
        LControl,
        LControlZoom,
        LGeoJson
    },
    mixins: [
        viewModesMixin
    ],
    props: {
        isRepositoryLevel: {
            type: Boolean,
            default: false
        }
    },
    data () {
        return {
            selectedGeocoordinateMetadatumId: false,
            latitude: -14.4086569,
            longitude: -51.31668,
            mapTileUrl: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            mapTileAttribution: '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors',
            selectedMarkerIndexes: [],
            hoveredMapCardItemId: false,
            mapSelectedItemId: false,
            mapIconRetinaUrl: iconRetinaUrl,
            mapIconUrl: iconUrl,
            mapIconShadowUrl: shadowUrl
        }
    },
    computed: {
        amountOfDisplayedMetadata() {
            return this.displayedMetadata.filter((metadata) => metadata.display).length;
        },
        itemsLocations() {
            let locations = [];
            
            if ( this.selectedGeocoordinateMetadatum.slug && this.items ) {
                for (let item of this.items) {
                    
                    if ( !item.metadata )
                        continue;
                    
                    let selectedItemMetadatum = item.metadata[this.selectedGeocoordinateMetadatum.slug];

                    // Handle compound metadata child first, as they will not appear in this list by default (they are inside their parents value)
                    if (!selectedItemMetadatum && this.selectedGeocoordinateMetadatum['parent']) {

                        const parentSlug = Object.keys(item.metadata).find(aMetadatumSlug => item.metadata[aMetadatumSlug].id == this.selectedGeocoordinateMetadatum['parent']);
                        if (parentSlug) {
                            item.metadata[parentSlug].value.forEach(aCompoundValue => {

                                const compoundValues = Array.isArray(aCompoundValue) ? aCompoundValue : [aCompoundValue];
                                compoundValues.forEach(aValue => {
                                    if ( aValue['metadatum_id'] == this.selectedGeocoordinateMetadatum['id'] ) {
                                        selectedItemMetadatum = {
                                            'metadatum_id': aValue['metadatum_id'],
                                            'parent_meta_id': aValue['parent_meta_id'],
                                            'value': selectedItemMetadatum && selectedItemMetadatum['value'] ? selectedItemMetadatum['value'] : [],
                                            'value_as_string': selectedItemMetadatum && selectedItemMetadatum['value_as_string'] ? selectedItemMetadatum['value_as_string'] : [],
                                            'value_as_html': selectedItemMetadatum && selectedItemMetadatum['value_as_html'] ? selectedItemMetadatum['value_as_html'] : []
                                        }
                                        selectedItemMetadatum['value'].push(aValue['value']);
                                        selectedItemMetadatum['value_as_string'].push(aValue['value_as_string']);
                                        selectedItemMetadatum['value_as_html'].push(aValue['value_as_html']);
                                    }
                                });
                            });
                        }
                    }

                    // Then check if has a single or multi value
                    const selectedType = this.selectedGeocoordinateMetadatum['metadata_type'];
                    if (
                        selectedItemMetadatum &&
                        Array.isArray(selectedItemMetadatum.value) 
                    ) {
                        for (let i = 0; i < selectedItemMetadatum.value.length; i++) {
                            if (selectedType == 'Tainacan\\Metadata_Types\\GeoCoordinate' && selectedItemMetadatum.value[i].split(',').length == 2) {
                                locations.push({
                                    item: item,
                                    multivalueIndex: i,
                                    multivalueTotal: selectedItemMetadatum.value.length,
                                    location: latLng(selectedItemMetadatum.value[i].split(',')),
                                    isPoint: true
                                });
                            } else if (selectedType == 'Tainacan\\Metadata_Types\\GeoJSON') {
                                this.parseGeoJsonValue(selectedItemMetadatum.value[i]).forEach((feature) => {
                                    const bounds = this.getFeatureBounds(feature);
                                    if (!bounds)
                                        return;
                                    const center = bounds.getCenter();
                                    locations.push({
                                        item: item,
                                        multivalueIndex: i,
                                        multivalueTotal: selectedItemMetadatum.value.length,
                                        feature,
                                        bounds,
                                        location: latLng(center.lat, center.lng),
                                        isPoint: ['Point', 'MultiPoint'].includes(feature?.geometry?.type)
                                    });
                                });
                            }
                        }
                    } else if (
                        selectedItemMetadatum &&
                        typeof selectedItemMetadatum.value.split == 'function' &&
                        selectedType == 'Tainacan\\Metadata_Types\\GeoCoordinate' &&
                        selectedItemMetadatum.value.split(',').length == 2
                    ) {
                        locations.push({
                            item: item,
                            location: latLng(selectedItemMetadatum.value.split(',')),
                            isPoint: true
                        });
                    } else if (
                        selectedItemMetadatum &&
                        typeof selectedItemMetadatum.value == 'string' &&
                        selectedType == 'Tainacan\\Metadata_Types\\GeoJSON'
                    ) {
                        this.parseGeoJsonValue(selectedItemMetadatum.value).forEach((feature) => {
                            const bounds = this.getFeatureBounds(feature);
                            if (!bounds)
                                return;
                            const center = bounds.getCenter();
                            locations.push({
                                item: item,
                                feature,
                                bounds,
                                location: latLng(center.lat, center.lng),
                                isPoint: ['Point', 'MultiPoint'].includes(feature?.geometry?.type)
                            });
                        });
                    }
                    
                }   
            }
            return locations.map((location, index) => ({ ...location, locationIndex: index }));
        },
        pointLocations() {
            return this.itemsLocations.filter((location) => location.isPoint);
        },
        nonPointGeoJsonCollection() {
            return {
                type: 'FeatureCollection',
                features: this.itemsLocations
                    .filter((location) => !location.isPoint && location.feature)
                    .map((location) => ({
                        ...location.feature,
                        properties: {
                            ...(location.feature.properties || {}),
                            __tainacan_location_index: location.locationIndex
                        }
                    }))
            };
        },
        nonPointGeoJsonOptions() {
            return {
                bubblingMouseEvents: false,
                onEachFeature: (feature, layer) => {
                    const locationIndex = Number(feature.properties?.__tainacan_location_index);
                    if (!isNaN(locationIndex)) {
                        layer.on('click', (event) => {
                            event?.originalEvent?.preventDefault?.();
                            event?.originalEvent?.stopPropagation?.();
                            this.showItemByLocation(locationIndex);
                        });
                    }
                }
            };
        },
        nonPointGeoJsonStyle() {
            return (feature) => {
                const locationIndex = Number(feature.properties?.__tainacan_location_index);
                const selected = this.selectedMarkerIndexes.includes(locationIndex);
                const hasSelection = this.selectedMarkerIndexes.length > 0;
                const location = this.itemsLocations[locationIndex];
                return {
                    color: selected ? '#5f3dc4' : '#3273dc',
                    weight: selected ? 4 : 3,
                    fillOpacity: selected ? 0.2 : 0.12,
                    opacity: hasSelection && !selected ? 0.35 : 1.0,
                    dashArray: location?.feature?.geometry?.type?.includes('Line') ? '6 4' : null
                };
            };
        },
        geocoordinateMetadata() {
            let geoMetadata = {};

            this.displayedMetadata.forEach((aMetadatum) => {

                if ( aMetadatum['display'] &&
                    (aMetadatum['metadata_type'] == 'Tainacan\\Metadata_Types\\GeoCoordinate' || aMetadatum['metadata_type'] == 'Tainacan\\Metadata_Types\\GeoJSON')
                )
                    geoMetadata[aMetadatum.id] = aMetadatum;
                
                if ( aMetadatum['display'] && aMetadatum['metadata_type'] == 'Tainacan\\Metadata_Types\\Compound' &&
                    aMetadatum['metadata_type_options']['children_objects'] && aMetadatum['metadata_type_options']['children_objects'].length
                ) {
                    for ( let i = 0; i < aMetadatum['metadata_type_options']['children_objects'].length; i++ )
                        if ( ['Tainacan\\Metadata_Types\\GeoCoordinate', 'Tainacan\\Metadata_Types\\GeoJSON'].includes(aMetadatum['metadata_type_options']['children_objects'][i]['metadata_type']) ) {
                            let childMetadatum = JSON.parse(JSON.stringify(aMetadatum['metadata_type_options']['children_objects'][i]));
                            childMetadatum.name = childMetadatum.name + ' (' + aMetadatum.name + ')';
                            geoMetadata[aMetadatum.id] = childMetadatum;
                        }
                }
            });
            return geoMetadata;
        },
        selectedGeocoordinateMetadatum() {
            if (
                !Object.keys(this.geocoordinateMetadata).length ||
                !this.geocoordinateMetadata[this.selectedGeocoordinateMetadatumId]
            ) 
                return false;
            else 
                return this.geocoordinateMetadata[this.selectedGeocoordinateMetadatumId];
        }
    },
    watch: {
        itemsLocations: { 
            handler() {
                setTimeout(() => {
                    if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].leafletObject ) {
                        if (this.itemsLocations.length == 1)
                            this.$refs['tainacan-view-mode-map'].leafletObject.panInsideBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                        else
                            this.$refs['tainacan-view-mode-map'].leafletObject.flyToBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                    }
                }, 500)
            },
            deep: true
        },
        selectedGeocoordinateMetadatum() {
            this.clearSelectedMarkers();
        },
        geocoordinateMetadata: {
            handler () {
                // Setting default geocoordinate metadatum for map view mode
                const prefsGeocoordinateMetadatum = !this.isRepositoryLevel ? 'map_view_mode_selected_geocoordinate_metadatum_' + this.collectionId : 'map_view_mode_selected_geocoordinate_metadatum';
                const geocoordinateMetadataIds = Object.keys(this.geocoordinateMetadata);
                if (
                    !geocoordinateMetadataIds.length ||
                    !this.$userPrefs ||
                    !this.$userPrefs.get(prefsGeocoordinateMetadatum) ||
                    !this.geocoordinateMetadata[this.$userPrefs.get(prefsGeocoordinateMetadatum)]
                )
                    this.selectedGeocoordinateMetadatumId = geocoordinateMetadataIds.length ? geocoordinateMetadataIds[0] : false;
                else 
                    this.selectedGeocoordinateMetadatumId = this.$userPrefs.get(prefsGeocoordinateMetadatum);
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        parseGeoJsonValue(rawValue) {
            try {
                const parsed = JSON.parse(rawValue);
                if (parsed?.type == 'FeatureCollection' && Array.isArray(parsed.features))
                    return parsed.features.filter((feature) => feature?.type == 'Feature' && feature.geometry);
                if (parsed?.type == 'Feature' && parsed.geometry)
                    return [parsed];
                if (parsed?.type)
                    return [{ type: 'Feature', geometry: parsed, properties: {} }];
            } catch (error) {
                return [];
            }
            return [];
        },
        getFeatureBounds(feature) {
            try {
                const layer = geoJSON(feature);
                const bounds = layer.getBounds();
                return bounds?.isValid() ? bounds : null;
            } catch (error) {
                return null;
            }
        },
        onChangeSelectedGeocoordinateMetadatum(id) {
            // Setting default geocoordinate metadatum for map view mode
            const prefsGeocoordinateMetadatum = !this.isRepositoryLevel ? 'map_view_mode_selected_geocoordinate_metadatum_' + this.collectionId : 'map_view_mode_selected_geocoordinate_metadatum';
            if ( this.$userPrefs )
                this.$userPrefs.set(prefsGeocoordinateMetadatum, id);
        },
        onMapReady() {
            if ( LeafletActiveArea && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].leafletObject )
                this.$refs['tainacan-view-mode-map'].leafletObject.setActiveArea('leaflet-active-area');
        },
        getItemImageHeight(imageWidth, imageHeight) {  
            let itemWidth = 120;
            return (imageHeight*itemWidth)/imageWidth;
        },
        getMultivalueIndicator(itemLocation) {
            if ( itemLocation.multivalueTotal > 1 )
                return ' <em>(' + (itemLocation.multivalueIndex + 1) + ' of ' + itemLocation.multivalueTotal + ')</em>';
            else 
                return '';
        },
        clearSelectedMarkers() {
            this.mapSelectedItemId = false;
            this.selectedMarkerIndexes = [];
            if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].leafletObject ) {
                if (this.itemsLocations.length == 1)
                    this.$refs['tainacan-view-mode-map'].leafletObject.panInsideBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                else
                    this.$refs['tainacan-view-mode-map'].leafletObject.flyToBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
            }
        },
        showItemByLocation(index) {
            this.mapSelectedItemId = this.itemsLocations[index].item.id;
            this.selectedMarkerIndexes = [];
            this.selectedMarkerIndexes.push(index);
            if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].leafletObject )
                this.$refs['tainacan-view-mode-map'].leafletObject.panInsideBounds( [ this.itemsLocations[index].location ],  { animate: true, maxZoom: 16 });
        },
        showLocationsByItem(item) {
            this.mapSelectedItemId = item.id;
            this.selectedMarkerIndexes = [];

            const selectedLocationsByItem = this.itemsLocations.filter((anItemLocation, index) => {
                if (anItemLocation.item.id == item.id)
                    this.selectedMarkerIndexes.push(index);
                return anItemLocation.item.id == item.id;
            })

            if ( selectedLocationsByItem.length) {
                if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].leafletObject ) {
                    if (selectedLocationsByItem.length > 1)
                        this.$refs['tainacan-view-mode-map'].leafletObject.flyToBounds( selectedLocationsByItem.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                    else
                        this.$refs['tainacan-view-mode-map'].leafletObject.panInsideBounds( selectedLocationsByItem.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                }
            } else {
                this.$buefy.snackbar.open({
                    message: this.$i18n.get('info_non_located_item'),
                    type: 'is-warning',
                    duration: 3000
                });
            }
        }
    }
}
</script>

<style  lang="scss" scoped>

    @use "../../../../../admin/scss/_view-mode-records.scss";
    @use "../../../../../admin/scss/_view-mode-map.scss";
    @use "../../../../../admin/scss/_selects.scss" as _selects;

    // Include selects mixin
    @include _selects.tainacan-selects;

    .tainacan-records-container--map .tainacan-record .metadata-title {
        padding: 0.6em 0.875em;
    }
    .tainacan-records-close-button .icon {
        align-items: center;
        display: inline-flex;
        justify-content: center;
        height: 1.5rem;
        width: 1.5rem;
    }
</style>


