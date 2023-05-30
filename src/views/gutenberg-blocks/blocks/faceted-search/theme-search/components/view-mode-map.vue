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
                            role="listitem"
                            :aria-setsize="totalItems"
                            :aria-posinset="getPosInSet(index)"
                            :data-tainacan-item-id="item.id"
                            @mouseenter="hoveredMapCardItemId = item.id"
                            @mouseleave="hoveredMapCardItemId = false"
                            :key="item.id"
                            v-for="(item, index) of items">
                        <div 
                                @click.prevent.stop="showLocationsByItem(item)"
                                :class="{
                                    'clicked-map-card': mapSelectedItemId == item.id,
                                    'non-located-item': !itemsLocations.some(anItemLocation => anItemLocation.item.id == item.id)
                                }"
                                class="tainacan-map-card">

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
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 300,
                                            },
                                            content: item.metadata != undefined ? renderMetadata(item, column) : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'top-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                        v-html="item.metadata != undefined && collectionId && renderMetadata(item, column) ? renderMetadata(item, column) : (item.title ? item.title :`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />                 
                                <div class="tainacan-map-card-thumbnail">
                                    <blur-hash-image
                                            v-if="item.thumbnail != undefined"
                                            class="tainacan-map-card-item-thumbnail"
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-small', 40)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-small', 40)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-small')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)"
                                            :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-small', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            :transition-duration="500"
                                        />
                                </div>
                                <span 
                                        v-if="isSlideshowViewModeEnabled"
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 100,
                                            },
                                            content: $i18n.get('label_see_on_fullscreen'),
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"          
                                        @click.prevent="starSlideshowFromHere(index)"
                                        class="icon slideshow-icon">
                                    <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"/>
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
                        @click="clearSelectedMarkers()"
                        @ready="onMapReady()"
                        :options="{
                            name: 'tainacan-view-mode-map',
                            zoomControl: false
                        }">
                    <l-tile-layer 
                            :url="mapTileUrl" 
                            :attribution="mapTileAttribution" />
                    <l-marker 
                            v-for="(itemLocation, index) of itemsLocations"
                            :key="index"
                            :lat-lng="itemLocation.location"
                            :opacity="selectedMarkerIndexes.length > 0 && !selectedMarkerIndexes.includes(index) ? 0.35 : 1.0"
                            @click="showItemByLocation(index)">
                        <l-icon 
                                :icon-retina-url="mapIconRetinaUrl"
                                :icon-url="mapIconUrl"
                                :shadow-url="mapIconShadowUrl"
                                :icon-size="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [25, 41] : [16, 28]"
                                :shadow-size="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [41, 41] : [28, 28]"
                                :icon-anchor="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [12, 41] : [8, 28]"
                                :tooltip-anchor="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [16, -28] : [8, -21]"
                                :popup-anchor="(itemLocation.item.id == hoveredMapCardItemId || itemLocation.item.id == mapSelectedItemId) ? [1, -34] : [1, -25]" />
                        <l-tooltip>
                            <div
                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                    :key="metadatumIndex">
                                <div 
                                        v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                        style="font-weight: bold;"
                                        v-html="(itemLocation.item.metadata != undefined && collectionId ? renderMetadata(itemLocation.item, column) : (itemLocation.item.title ? itemLocation.item.title :`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)) + getMultivalueIndicator(itemLocation)" />
                                <div 
                                        v-if="column.display && column.metadata_type == 'Tainacan\\Metadata_Types\\Compound' && selectedGeocoordinateMetadatum.parent == column.id"
                                        v-html="itemLocation.item.metadata != undefined ? renderMetadata(itemLocation.item, column, itemLocation.multivalueIndex) : ''" />
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
                                <b-select
                                        :placeholder="$i18n.get('instruction_select_geocoordinate_metadatum')"
                                        id="tainacan-select-geocoordinate-metatum"
                                        v-model="selectedGeocoordinateMetadatumId">
                                    <option
                                            v-for="(geocoordinateMetadatum, geocoordinateMetadatumId) in geocoordinateMetadata"
                                            :key="geocoordinateMetadatum.id"
                                            role="button"
                                            :class="{ 'is-active': selectedGeocoordinateMetadatumId == geocoordinateMetadatumId }"
                                            :value="geocoordinateMetadatumId"
                                            @click="onChangeSelectedGeocoordinateMetadatum(geocoordinateMetadatumId)">
                                        {{ geocoordinateMetadatum.name }}
                                    </option>
                                </b-select>
                            </div>
                            <section 
                                    v-else
                                    class="section">
                                <div class="content has-text-grey has-text-centered">
                                    <p style="margin-bottom: 0px">
                                        <span class="icon is-large">
                                            <i>
                                                <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="var(--tainacan-info-color, #555758)"
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
                            :disable-scroll-propagation="true"
                            :disable-click-propagation="true"
                            v-if="selectedMarkerIndexes.length || mapSelectedItemId"
                            position="topleft"
                            class="tainacan-records-container tainacan-records-container--map">
                        <button 
                                :aria-label="$i18n.get('label_clean')"
                                class="tainacan-records-close-button"
                                @click="clearSelectedMarkers()"
                                v-tooltip="{
                                    content: $i18n.get('label_clean'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }">
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-close" />
                            </span>
                        </button>
                        <transition-group
                                    tag="ul"
                                    name="appear">
                            <li
                                    :aria-setsize="totalItems"
                                    :aria-posinset="getPosInSet(index)"
                                    :data-tainacan-item-id="item.id"
                                    :key="item.id"
                                    v-for="(item, index) of items.filter(anItem => mapSelectedItemId == anItem.id)">
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
                                                v-tooltip="{
                                                    content: $i18n.get('label_show_item_location_on_map'),
                                                    autoHide: true,
                                                    placement: 'auto',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"
                                                id="button-show-location"
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
                                                v-tooltip="{
                                                    delay: {
                                                        shown: 500,
                                                        hide: 300,
                                                    },
                                                    content: item.metadata != undefined ? renderMetadata(item, column) : '',
                                                    html: true,
                                                    autoHide: false,
                                                    placement: 'auto-start',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"
                                                v-for="(column, metadatumIndex) in displayedMetadata"
                                                :key="metadatumIndex"
                                                v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                                v-html="item.metadata != undefined && collectionId ? renderMetadata(item, column) : (item.title ? item.title :`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />                 
                                        <span 
                                                v-if="isSlideshowViewModeEnabled"
                                                v-tooltip="{
                                                    delay: {
                                                        shown: 500,
                                                        hide: 100,
                                                    },
                                                    content: $i18n.get('label_see_on_fullscreen'),
                                                    placement: 'auto-start',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"          
                                                @click.prevent="starSlideshowFromHere(index)"
                                                class="icon slideshow-icon">
                                            <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"/>
                                        </span> 
                                    </div>

                                    <!-- Remaining metadata -->  
                                    <div class="media">
                                        <div class="list-metadata media-body">
                                            <div 
                                                    class="tainacan-record-thumbnail"
                                                    v-if="item.thumbnail != undefined">
                                                <blur-hash-image
                                                        @click.left="onClickItem($event, item)"
                                                        @click.right="onRightClickItem($event, item)"
                                                        v-if="item.thumbnail != undefined"
                                                        class="tainacan-record-item-thumbnail"
                                                        :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                                        :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                                        :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                                        :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                                        :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                                        :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                                        :transition-duration="500"
                                                    />
                                                <div 
                                                        :style="{ 
                                                            minHeight: getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px',
                                                            marginTop: '-' + getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px'
                                                        }" />
                                            </div>
                                            <span 
                                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                                    :key="metadatumIndex"
                                                    :class="{ 'metadata-type-textarea': column.metadata_type_object.component == 'tainacan-textarea' }"
                                                    v-if="renderMetadata(item, column) != '' &&
                                                        column.display && column.slug != 'thumbnail' &&
                                                        column.metadata_type_object != undefined &&
                                                        (column.metadata_type_object.related_mapped_prop != 'title') &&
                                                        (column.metadata_type != 'Tainacan\\Metadata_Types\\GeoCoordinate') ">
                                                <h3 class="metadata-label">{{ column.name }}</h3>
                                                <p      
                                                        v-html="renderMetadata(item, column)"
                                                        class="metadata-value"/>
                                            </span>
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
import { LMap, LIcon, LTooltip, LTileLayer, LMarker, LControl, LControlZoom } from 'vue2-leaflet';
import 'leaflet/dist/leaflet.css';
import { latLng } from 'leaflet';
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
        LControlZoom
    },
    mixins: [
        viewModesMixin
    ],
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
                    if (
                        selectedItemMetadatum &&
                        Array.isArray(selectedItemMetadatum.value) 
                    ) {
                        for (let i = 0; i < selectedItemMetadatum.value.length; i++) {
                            if (selectedItemMetadatum.value[i].split(',').length == 2) {
                                locations.push({
                                    item: item,
                                    multivalueIndex: i,
                                    multivalueTotal: selectedItemMetadatum.value.length,
                                    location: latLng(selectedItemMetadatum.value[i].split(','))
                                });
                            }
                        }
                    } else if (
                        selectedItemMetadatum &&
                        typeof selectedItemMetadatum.value.split == 'function' &&
                        selectedItemMetadatum.value.split(',').length == 2
                    ) {
                        locations.push({
                            item: item,
                            location: latLng(selectedItemMetadatum.value.split(','))
                        });
                    }
                    
                }   
            }
            return locations;
        },
        geocoordinateMetadata() {
            let geoMetadata = {};

            this.displayedMetadata.forEach((aMetadatum) => {

                if ( aMetadatum['display'] && aMetadatum['metadata_type'] == 'Tainacan\\Metadata_Types\\GeoCoordinate' )
                    geoMetadata[aMetadatum.id] = aMetadatum;
                
                if ( aMetadatum['display'] && aMetadatum['metadata_type'] == 'Tainacan\\Metadata_Types\\Compound' &&
                    aMetadatum['metadata_type_options']['children_objects'] && aMetadatum['metadata_type_options']['children_objects'].length
                ) {
                    for ( let i = 0; i < aMetadatum['metadata_type_options']['children_objects'].length; i++ )
                        if ( aMetadatum['metadata_type_options']['children_objects'][i]['metadata_type'] == 'Tainacan\\Metadata_Types\\GeoCoordinate' ) {
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
        itemsLocations() {
            setTimeout(() => {
                if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].mapObject ) {
                    if (this.itemsLocations.length == 1)
                        this.$refs['tainacan-view-mode-map'].mapObject.panInsideBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                    else
                        this.$refs['tainacan-view-mode-map'].mapObject.flyToBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                }
            }, 500)
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
                    !this.$userPrefs.get(prefsGeocoordinateMetadatum) ||
                    !this.geocoordinateMetadata[this.$userPrefs.get(prefsGeocoordinateMetadatum)]
                )
                    this.selectedGeocoordinateMetadatumId = geocoordinateMetadataIds.length ? geocoordinateMetadataIds[0] : false;
                else 
                    this.selectedGeocoordinateMetadatumId = this.$userPrefs.get(prefsGeocoordinateMetadatum);
            },
            immediate: true
        }
    },
    methods: {
        onChangeSelectedGeocoordinateMetadatum(id) {
            // Setting default geocoordinate metadatum for map view mode
            const prefsGeocoordinateMetadatum = !this.isRepositoryLevel ? 'map_view_mode_selected_geocoordinate_metadatum_' + this.collectionId : 'map_view_mode_selected_geocoordinate_metadatum';
            this.$userPrefs.set(prefsGeocoordinateMetadatum, id);
        },
        onMapReady() {
            if ( LeafletActiveArea && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].mapObject )
                this.$refs['tainacan-view-mode-map'].mapObject.setActiveArea('leaflet-active-area');
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
            if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].mapObject ) {
                if (this.itemsLocations.length == 1)
                    this.$refs['tainacan-view-mode-map'].mapObject.panInsideBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                else
                    this.$refs['tainacan-view-mode-map'].mapObject.flyToBounds(this.itemsLocations.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
            }
        },
        showItemByLocation(index) {
            this.mapSelectedItemId = this.itemsLocations[index].item.id;
            this.selectedMarkerIndexes = [];
            this.selectedMarkerIndexes.push(index);
            if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].mapObject )
                this.$refs['tainacan-view-mode-map'].mapObject.panInsideBounds( [ this.itemsLocations[index].location ],  { animate: true, maxZoom: 16 });
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
                if ( this.itemsLocations.length && this.$refs['tainacan-view-mode-map'] && this.$refs['tainacan-view-mode-map'].mapObject ) {
                    if (selectedLocationsByItem.length > 1)
                        this.$refs['tainacan-view-mode-map'].mapObject.flyToBounds( selectedLocationsByItem.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
                    else
                        this.$refs['tainacan-view-mode-map'].mapObject.panInsideBounds( selectedLocationsByItem.map((anItemLocation) => anItemLocation.location),  { animate: true, maxZoom: 16 });
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

    @import "../../../../../admin/scss/_view-mode-records.scss";
    @import "../../../../../admin/scss/_view-mode-map.scss";

    .tainacan-records-container--map .tainacan-record .metadata-title {
        padding: 0.6em 0.875em;
    }
</style>


