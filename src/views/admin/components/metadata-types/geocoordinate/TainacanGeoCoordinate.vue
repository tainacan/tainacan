<template>
    <div
            :id="itemMetadatumIdentifier"
            class="tainacan-leaflet-map-container">
        <l-map 
                :id="'map--' + itemMetadatumIdentifier"
                :ref="'map--' + itemMetadatumIdentifier"
                style="height: 320px; width:100%;"
                :zoom="initialZoom"
                :max-zoom="maxZoom"
                :center="[initialLatitude, initialLongitude]"
                :zoom-animation="true"
                :options="{
                    name: 'map--' + itemMetadatumIdentifier,
                    trackResize: false, // We handle this manually in the component
                    worldCopyJump: true
                }"
                @click="onMapClick">
            <l-tile-layer 
                    :url="mapProvider" 
                    :attribution="attribution" />
            <l-control position="topright">
                <div class="geocoordinate-input-panel">
                    <b-input
                            v-if="editingMarkerIndex >= 0"
                            expanded 
                            :placeholder="-14.408656999999"
                            type="text"
                            :step="0.000000000001"
                            :model-value="latitude"
                            @update:model-value="onUpdateFromLatitudeInput" />
                    <b-input 
                            v-if="editingMarkerIndex >= 0"
                            expanded
                            :placeholder="-51.316689999999"
                            type="text"
                            :step="0.000000000001"
                            :model-value="longitude"
                            @update:model-value="onUpdateFromLongitudeInput" />
                    <b-button
                            v-if="editingMarkerIndex >= 0"
                            outlined
                            @click="onMarkerRemove(editingMarkerIndex)">
                        <span class="icon is-small">
                            <i class="tainacan-icon has-text-secondary tainacan-icon-remove" />
                        </span>
                        &nbsp;{{ $i18n.get('remove_point') }}
                    </b-button>
                    <b-button
                            v-if="editingMarkerIndex < 0 && shouldAddMore"
                            outlined
                            @click="addLocation(latitude + ',' + longitude)">
                        <span class="icon is-small">
                            <i class="tainacan-icon has-text-secondary tainacan-icon-add" />
                        </span>
                        &nbsp;{{ $i18n.get('add') }}
                    </b-button>
                </div>
            </l-control>
            <l-marker 
                    v-for="(markerLatLng, index) of selectedLatLng"
                    :key="index"
                    :draggable="true"
                    :lat-lng="markerLatLng"
                    :opacity="editingMarkerIndex >= 0 && editingMarkerIndex != index ? 0.35 : 1.0"
                    @dragend="($event) => onDragMarker($event, index)"
                    @click="($event) => onMarkerEditingClick($event)" />
            <l-marker 
                    v-if="editingLatLng && editingMarkerIndex < 0 && shouldAddMore"
                    :draggable="true"
                    :lat-lng="editingLatLng"
                    @dragend="($event) => onDragEditingMarker($event)"
                    @click="addLocation(latitude + ',' + longitude)">
                <l-tooltip :options="{ offset: [16, 0] }">
                    {{ $i18n.get('instruction_click_to_add_a_point') }}
                </l-tooltip>
                <l-icon class-name="tainacan-location-marker-add">
                    <span class="icon">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add" />
                    </span>
                </l-icon>
            </l-marker>
        </l-map>
    </div>
</template>

<script>
    import { nextTick } from 'vue';

    import { LMap, LIcon, LTooltip, LTileLayer, LMarker, LControl } from '@vue-leaflet/vue-leaflet';
    import 'leaflet/dist/leaflet.css';
    import { Icon, latLng } from 'leaflet';
    import iconUrl from 'leaflet/dist/images/marker-icon.png';
    import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
    import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

    delete Icon.Default.prototype._getIconUrl;
    Icon.Default.mergeOptions({
        iconRetinaUrl: iconRetinaUrl,
        iconUrl: iconUrl,
        shadowUrl: shadowUrl
    });

    export default {
        components: {
            LMap,
            LIcon,
            LTooltip,
            LTileLayer,
            LMarker,
            LControl
        },
        props: {
            itemMetadatum: Object,
            value: [String, Array],
            disabled: false,
            maxtags: '',
            isLastMetadatum: false
        },
        emits: ['update:value'],
        data() {
            return {
                editingMarkerIndex: -1,
                latitude: -14.4086569,
                longitude: -51.31668,
                selected: [],
                mapIntersectionObserver: null
            }
        },
        computed: {
            mapProvider() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.map_provider ? this.itemMetadatum.metadatum.metadata_type_options.map_provider : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
            },
            initialZoom() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.initial_zoom ? Number(this.itemMetadatum.metadatum.metadata_type_options.initial_zoom) : 5;
            },
            maxZoom() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.maximum_zoom ? this.itemMetadatum.metadatum.metadata_type_options.maximum_zoom : 12;
            },
            initialLatitude() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.initial_latitude ? Number(this.itemMetadatum.metadatum.metadata_type_options.initial_latitude) : -14.4086569;
            },
            initialLongitude() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.initial_longitude ? Number(this.itemMetadatum.metadatum.metadata_type_options.initial_longitude) : -51.31668;
            },
            attribution() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.attribution ? this.itemMetadatum.metadatum.metadata_type_options.attribution : '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors';
            },
            itemMetadatumIdentifier() {
                return 'tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '');
            },
            selectedLatLng() {
                if ( this.selected && Array.isArray(this.selected) ) {
                    return this.selected.filter((aSelected) => {
                        const coordinates = aSelected.indexOf(',') && aSelected.split(',').length == 2 ? aSelected.split(',') : [this.initialLatitude, this.initialLongitude];
                        return ( !isNaN(Number(coordinates[0])) && !isNaN(Number(coordinates[1])) );
                    }).map((aSelected) => {
                        const coordinates = aSelected.indexOf(',') && aSelected.split(',').length == 2 ? aSelected.split(',') : [this.initialLatitude, this.initialLongitude];
                        return latLng(Number(coordinates[0]), Number(coordinates[1]));
                    }); 
                }
                return [];
            },
            editingLatLng() {
                if ( !isNaN(this.latitude) && !isNaN(this.longitude) )
                    return latLng(Number(this.latitude), Number(this.longitude));
                else 
                    return null;
            },
            maxMultipleValues() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.cardinality &&
                    !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                    this.itemMetadatum.metadatum.cardinality > 1
                ) ? this.itemMetadatum.metadatum.cardinality : undefined;
            },
            shouldAddMore() {
                // MaxTags value may come from a preset prop (bulk adition, for example) or from the actual maxMultipleValues setting.
                const hasMaxTagsValue = ( this.maxtags != undefined ? this.maxtags : (this.itemMetadatum.metadatum.multiple == 'yes' ? (this.maxMultipleValues !== undefined ? this.maxMultipleValues : null) : '1') );
                // For multivalued metadata without maxMultipleValues, the limit is infinet, so we should let add anyway.
                return (hasMaxTagsValue !== null ? (this.selected.length < hasMaxTagsValue) : true);
            }
        },
        watch: {
            selectedLatLng: {
                handler() {
                    const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                    nextTick(() => {
                        if ( this.$refs[mapComponentRef] && this.$refs[mapComponentRef].leafletObject && this.selectedLatLng.length != undefined) {
                            if (this.selectedLatLng.length == 1)
                                this.$refs[mapComponentRef].leafletObject.panInsideBounds(this.selectedLatLng, { animate: true, maxZoom: this.maxZoom });
                            else 
                                this.$refs[mapComponentRef].leafletObject.flyToBounds(this.selectedLatLng, { animate: true, maxZoom: this.maxZoom });
                        }
                    });
                },
                deep: true
            }
        },
        created() {
            if ( this.value && this.value != "" )
                this.selected = Array.isArray(this.value) ? (this.value.length == 1 && this.value[0] == "" ? [] : this.value) : [this.value];
        },
        mounted() {
            nextTick(() => {
                const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                this.handleWindowResize(mapComponentRef);

                if ( !this.selected || this.selected.length === 0 ) {
                    this.latitude = this.initialLatitude;
                    this.longitude = this.initialLongitude;
                }

                // Intersection Observer to handle map resize
                if ( this.$refs[mapComponentRef] && this.$refs[mapComponentRef]['$el'] ) {
                    this.mapIntersectionObserver = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting)
                                this.handleWindowResize(mapComponentRef);
                        });
                    }, { threshold: 0.1 });
                    this.mapIntersectionObserver.observe(this.$refs[mapComponentRef]['$el']);
                }
            });
        },
        methods: {
            onUpdateFromLatitudeInput: _.debounce( function(value) {
                let newLatitude = value;
                if ( !isNaN(newLatitude) ) {
                    this.latitude = newLatitude;
                    this.onUpdateFromLatitudeAndLongitude();
                } else {
                    const splitValue = newLatitude.split(',');
                    if (
                        splitValue && 
                        splitValue.length == 2 &&
                        !isNaN(splitValue[0]) &&
                        !isNaN(splitValue[1])
                    ) {
                        this.latitude = splitValue[0];
                        this.longitude = splitValue[1];
                        this.onUpdateFromLatitudeAndLongitude();
                    }
                }
            }, 250),
            onUpdateFromLongitudeInput: _.debounce( function(value) {
                let newLongitude = value;

                if ( !isNaN(newLongitude) ) {
                    this.longitude = newLongitude;
                    this.onUpdateFromLatitudeAndLongitude();
                } else {
                    const splitValue = newLongitude.split(',');
                    if (
                        splitValue && 
                        splitValue.length == 2 &&
                        !isNaN(splitValue[0]) &&
                        !isNaN(splitValue[1])
                    ) {
                        this.latitude = splitValue[0];
                        this.longitude = splitValue[1];
                        this.onUpdateFromLatitudeAndLongitude();
                    }
                }
            }, 250),
            onUpdateFromLatitudeAndLongitude() {
                if (this.editingMarkerIndex >= 0) {
                    this.selected.splice(this.editingMarkerIndex, 1, this.latitude + ',' + this.longitude);
                    this.$emit('update:value', this.selected);
                }
            },
            onDragMarker($event, index) {
                if ( $event.target && $event.target['_latlng'] ) {
                    if (this.editingMarkerIndex == index) {
                        this.latitude = $event.target['_latlng']['lat'];
                        this.longitude = $event.target['_latlng']['lng'];
                    }
                    this.selected.splice(index, 1, $event.target['_latlng']['lat'] + ',' + $event.target['_latlng']['lng']);
                    this.$emit('update:value', this.selected);
                }
            },
            onDragEditingMarker($event) {
                if ( $event.target && $event.target['_latlng'] ) {
                    this.latitude = $event.target['_latlng']['lat'];
                    this.longitude = $event.target['_latlng']['lng'];
                }
            },
            onMapClick($event) {
                this.editingMarkerIndex = -1;
                if ( $event && $event['latlng'] ) {
                    this.latitude = $event['latlng']['lat'];
                    this.longitude = $event['latlng']['lng'];
                }
            },
            onMarkerAdd($event) {
                this.editingMarkerIndex = -1;
                if ($event.latlng && this.shouldAddMore) {
                    const newLocationValue = $event.latlng.lat + ',' + $event.latlng.lng;
                    this.addLocation(newLocationValue);
                }
            },
            addLocation(newLocationValue) {
                const existintSelectedValue = this.selected.indexOf(newLocationValue);
                if (existintSelectedValue < 0) {
                    this.selected.push(newLocationValue);
                    this.$emit('update:value', this.selected);
                    this.editingMarkerIndex = this.selected.length - 1;
                }
            },
            onMarkerEditingClick($event) {
                if ( $event.target && $event.target['_latlng'] ) {
                    this.latitude = $event.target['_latlng']['lat'];
                    this.longitude = $event.target['_latlng']['lng'];
                    
                    const existingSelectedIndex = this.selected.indexOf(this.latitude + ',' + this.longitude);
                    this.editingMarkerIndex = existingSelectedIndex;
                    const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                    if ( this.$refs[mapComponentRef] && this.$refs[mapComponentRef].leafletObject )
                        this.$refs[mapComponentRef].leafletObject.panInsideBounds([ this.selectedLatLng[existingSelectedIndex] ],  { animate: true, maxZoom: this.maxZoom });
                }
            },
            onMarkerRemove(index) {
                this.editingMarkerIndex = -1;
                
                this.latitude = this.selectedLatLng[index].lat;
                this.longitude = this.selectedLatLng[index].lng;

                this.selected.splice(index, 1);
                this.$emit('update:value', this.selected);
            },
            handleWindowResize(mapComponentRef) {
                setTimeout(() => {
                    if ( this.$refs[mapComponentRef] && this.$refs[mapComponentRef].leafletObject ) {
                        this.$refs[mapComponentRef].leafletObject.invalidateSize(true);

                        if ( this.selectedLatLng.length != undefined) {
                            if (this.selectedLatLng.length == 1)
                                this.$refs[mapComponentRef].leafletObject.panInsideBounds(this.selectedLatLng, { animate: true, maxZoom: this.maxZoom });
                            else 
                                this.$refs[mapComponentRef].leafletObject.flyToBounds(this.selectedLatLng, { animate: true, maxZoom: this.maxZoom }); 
                        }
                    }
                }, 500);
            }
        }
    }
</script>

<style lang="scss">
.tainacan-leaflet-map-container .leaflet-container {
    border: 1px solid var(--tainacan-input-border-color);
    border-radius: 3px;

    .leaflet-marker-pane {
        filter: hue-rotate(-22deg);
    }
    .tainacan-location-marker-add {
        padding: 1.0rem;
        background: rgba(255,255,255,0.7);
        border: 2px solid var(--tainacan-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 100%;
        font-size: 0.875rem;
        margin-left: -1.125rem !important;
        margin-top: -1.125rem !important;
    }
    .geocoordinate-input-panel {
        padding: 0px;
        margin: 0px;
        display: flex;
        align-items: stretch;
        font-size: 1rem;

        &>.field,
        &>.field>.field-body {
            padding: 0px;
            margin: 0px;
        }

        .control {
            width: 100%;
        }
        .button:not(.is-small):not(.is-medium):not(.is-large) {
            color: var(--tainacan-secondary);
            border-radius: 0px !important;
            height: 100% !important;
            line-height: 1.7rem;
            background-color: var(--tainacan-input-background-color, #fff) !important;
        }
    }
}
</style>