<template>
    <div 
            :id="itemMetadatumIdentifier"
            :class="{ 'is-flex is-flex-wrap-wrap': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <div class="geocoordinate-input-panel">
            <b-input
                    expanded 
                    type="number"
                    :step="0.000000001"
                    v-model="latitude" />
            <b-input 
                    expanded
                    type="number"
                    :step="0.000000001"
                    v-model="longitude" />
            <transition name="filter-item">
                <b-button
                        outlined
                        v-if="editingMarkerIndex < 0"
                        :disabled="!shouldAddMore"
                        @click="addLocation(latitude + ',' + longitude)">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span>
                    &nbsp;{{ $i18n.get('add') }}
                </b-button>
                <b-button
                        v-else
                        outlined
                        @click="onMarkerRemove(editingMarkerIndex)">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-remove"/>
                    </span>
                    &nbsp;{{ $i18n.get('remove_value') }}
                </b-button>
            </transition>
        </div>
        <l-map 
                :id="'map--' + itemMetadatumIdentifier"
                :ref="'map--' + itemMetadatumIdentifier"
                style="height: 320px; width:100%; border: 1px solid var(--tainacan-input-border-color);"
                :zoom="5"
                :center="[-14.4086569, -51.31668]"
                :zoom-animation="true"
                @click="onMarkerAdd"
                :options="{
                    trackResize: false // We handle this manually in the component
                }">
            <l-tile-layer 
                    :url="url" 
                    :attribution="attribution" />
            <l-marker 
                    v-for="(markerLatLng, index) of selectedLatLng"
                    :key="index"
                    :draggable="true"
                    :lat-lng="markerLatLng"
                    @dragend="($event) => onDragMarker($event, index)"
                    @click="($event) => onMarkerEditingClick($event)">
                <l-icon class-name="tainacan-location-marker">
                    <!-- <span class="icon">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span> -->
                    <div class="tainacan-location-marker--inner"/>
                </l-icon>
            </l-marker>
            <l-marker 
                    v-if="editingMarkerIndex < 0"
                    :draggable="true"
                    :lat-lng="editingLatLng"
                    @dragend="($event) => onDragEditingMarker($event)"
                    @click="addLocation(latitude + ',' + longitude)">
                <l-icon class-name="tainacan-location-marker-add">
                    <span class="icon">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span>
                </l-icon>
            </l-marker>
        </l-map>
    </div>
</template>

<script>
    import { LMap, LIcon, LTileLayer, LMarker } from 'vue2-leaflet';
    import 'leaflet/dist/leaflet.css';
    import { Icon, latLng } from 'leaflet';
    import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png'
    import iconUrl from 'leaflet/dist/images/marker-icon.png'
    import shadowUrl from 'leaflet/dist/images/marker-shadow.png'

    delete Icon.Default.prototype._getIconUrl;
    Icon.Default.mergeOptions({
        iconRetinaUrl: iconRetinaUrl,
        iconUrl: iconUrl,
        shadowUrl: shadowUrl,
    });

    export default {
        components: {
            LMap,
            LIcon,
            LTileLayer,
            LMarker,
        },
        props: {
            itemMetadatum: Object,
            value: [String, Array],
            disabled: false,
            maxtags: '',
            isLastMetadatum: false
        },
        data() {
            return {
                editingMarkerIndex: -1,
                latitude: -14.4086569,
                longitude: -51.31668,
                selected: [],
                url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                attribution: '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors',
            }
        },
        computed: {
            itemMetadatumIdentifier() {
                return 'tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '');
            },
            selectedLatLng() {
                if ( this.selected && Array.isArray(this.selected) ) {
                    return this.selected.map((aSelected) => {
                        const coordinates = aSelected.indexOf(',') && aSelected.split(',').length == 2 ? aSelected.split(',') : [-14.4086569, -51.31668];
                        return latLng(Number(coordinates[0]), Number(coordinates[1]));
                    }); 
                }
                return [];
            },
            editingLatLng() {
                return latLng(Number(this.latitude), Number(this.longitude));
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
                const hasMaxTagsValue = ( this.maxtags != undefined ? this.maxtags : (this.itemMetadatum.metadatum.multiple == 'yes' || this.allowNew === true ? (this.maxMultipleValues !== undefined ? this.maxMultipleValues : null) : '1') );
                // For multivalued metadata without maxMultipleValues, the limit is infinet, so we should let add anyway.
                return (hasMaxTagsValue !== null ? (this.selected.length < hasMaxTagsValue) : true);
            }
        },
        watch: {
            selectedLatLng() {
                this.$nextTick(() => {
                    const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                    if ( this.selectedLatLng.length && this.$refs[mapComponentRef] && this.$refs[mapComponentRef].mapObject )
                        this.$refs[mapComponentRef].mapObject.panInsideBounds(this.selectedLatLng,  { animate: true });
                });
            }
        },
        created() {
            if (this.value && this.value != "")
                this.selected = Array.isArray(this.value) ? (this.value.length == 1 && this.value[0] == "" ? [] : this.value) : [this.value];
            
            // Listens to window resize event to update map bounds
            this.handleWindowResize();
            window.addEventListener('resize', this.handleWindowResize);
        },
        beforeDestroy() {
            window.removeEventListener('resize', this.handleWindowResize);
        },
        methods: {
            onDragMarker($event, index) {
                if ( $event.target && $event.target['_latlng'] ) {
                    this.selected.splice(index, 1, $event.target['_latlng']['lat'] + ',' + $event.target['_latlng']['lng']);
                    this.$emit('input', this.selected);
                }
            },
            onDragEditingMarker($event) {
                if ( $event.target && $event.target['_latlng'] ) {
                    this.latitude = $event.target['_latlng']['lat'];
                    this.longitude = $event.target['_latlng']['lng'];
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
                    this.$emit('input', this.selected);
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
                    if ( this.$refs[mapComponentRef] && this.$refs[mapComponentRef].mapObject )
                        this.$refs[mapComponentRef].mapObject.panInsideBounds([ this.selectedLatLng[existingSelectedIndex] ],  { animate: true });
                }
            },
            onMarkerRemove(index) {
                this.editingMarkerIndex = -1;
                console.log(this.selectedLatLng[index])
                this.latitude = this.selectedLatLng[index].lat;
                this.longitude = this.selectedLatLng[index].lng;

                this.selected.splice(index, 1);
                this.$emit('input', this.selected);
            },
            handleWindowResize: _.debounce( function() {
                this.$nextTick(() => {
                    const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                    if ( this.$refs[mapComponentRef] && this.$refs[mapComponentRef].mapObject )
                        this.$refs[mapComponentRef].mapObject.invalidateSize(true);
                });
            }, 500),
        }
    }
</script>

<style lang="scss">
    .tainacan-location-marker .tainacan-location-marker--inner {
        transform: perspective(2rem) rotateX(20deg) rotateZ(-45deg);
        transform-origin: 50% 50%;
        border-radius: 50% 50% 50% 0;
        padding: 0 3px 3px 0;
        width: 2rem;
        height: 2rem;
        background: var(--tainacan-secondary, #298596);
        position: absolute;
        left: 50%;
        top: 50%;
        margin: -2.2em 0 0 -1.3em;
        margin-left: -2.8125rem !important;
        margin-top: -2.8125rem !important;
        -webkit-box-shadow: -1px 1px 4px rgba(0, 0, 0, .5);
        -moz-box-shadow: -1px 1px 4px rgba(0, 0, 0, .5);
        box-shadow: -1px 1px 4px rgba(0, 0, 0, .5);

        &::after {
            content: '';
            width: 1em;
            height: 1em;
            margin: 1em 0 0 .7em;
            background: #ffffff;
            position: absolute;
            border-radius: 50%;
                -moz-box-shadow: 0 0 10px rgba(0, 0, 0, .5);
            -webkit-box-shadow: 0 0 10px rgba(0, 0, 0, .5);
            box-shadow: 0 0 10px rgba(0, 0, 0, .5);
            -moz-box-shadow: inset -2px 2px 4px hsla(0, 0, 0, .5);
            -webkit-box-shadow: inset -2px 2px 4px hsla(0, 0, 0, .5);
            box-shadow: inset -2px 2px 4px hsla(0, 0, 0, .5);
        }
    }
    .tainacan-location-marker-add {
        padding: 1.25rem;
        background: rgba(255,255,255,0.75);
        border: 2px solid var(--tainacan-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 100%;
        font-size: 1.125rem;
        margin-left: -1.375rem !important;
        margin-top: -1.375rem !important;
    }
    .geocoordinate-input-panel {
        padding: 0px;
        margin: 0px;
        display: flex;
        align-items: stretch;

        &>.field,
        &>.field>.field-body {
            padding: 0px;
            margin: 0px;
        }

        .control {
            width: 100%;
        }
        .button:not(.is-small):not(.is-medium):not(.is-large) {
            border-radius: 0px !important;
            height: 100% !important;
            line-height: 1.7rem;
        }
    }
</style>