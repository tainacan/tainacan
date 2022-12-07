<template>
    <div 
            :id="itemMetadatumIdentifier"
            :class="{ 'is-flex is-flex-wrap-wrap': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-taginput
                expanded
                :disabled="disabled"
                :id="'taginput--' + itemMetadatumIdentifier"
                size="is-small"
                icon="magnify"
                @add="() => $emit('input', selected)"
                @remove="() => $emit('input', selected)"
                v-model="selected"
                :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? (maxMultipleValues !== undefined ? maxMultipleValues : null) : '1')"
                :remove-on-keys="[]"
                :dropdown-position="isLastMetadatum ? 'top' :'auto'"
                attached
                :aria-close-label="$i18n.get('remove_value')"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_geocoordinate')"
                :class="{ 'has-selected': selected != undefined && selected != [] }"
                :has-counter="false"
                :confirm-keys="['Tab', 'Enter']"
                :on-paste-separators="['|', ';']"
                :before-adding="onAddValueFromTaginput" />
        <l-map 
                :id="'map--' + itemMetadatumIdentifier"
                :ref="'map--' + itemMetadatumIdentifier"
                style="height: 300px; width:100%; border: 1px solid var(--tainacan-input-border-color);"
                :zoom="5"
                :center="[-14.4086569, -51.31668]"
                :zoom-animation="true"
                @click="onMarkerAdd">
            <l-tile-layer 
                    :url="url" 
                    :attribution="attribution" />
            <l-marker 
                    v-for="(markerLatLng, index) of selectedLatLng"
                    :key="index"
                    :draggable="true"
                    :lat-lng="markerLatLng"
                    @dragend="($event) => onDragMarker($event, index)"
                    @click="($event) => onMarkerRemove($event, index)" />
        </l-map>
    </div>
</template>

<script>
    import { LMap, LTileLayer, LMarker } from 'vue2-leaflet';
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
            maxMultipleValues() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.cardinality &&
                    !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                    this.itemMetadatum.metadatum.cardinality > 1
                ) ? this.itemMetadatum.metadatum.cardinality : undefined;
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
            onAddValueFromTaginput(textualValue) {
                return textualValue.indexOf(',') && textualValue.split(',').length == 2
            },
            onDragMarker($event, index) {
                if ( $event.target && $event.target['_latlng'] ) {
                    this.selected.splice(index, 1, $event.target['_latlng']['lat'] + ',' + $event.target['_latlng']['lng']);
                    this.$emit('input', this.selected);
                }
            },
            onMarkerAdd($event) {
                // MaxTags value may come from a preset prop (bulk adition, for example) or from the actual maxMultipleValues setting.
                const hasMaxTagsValue = ( this.maxtags != undefined ? this.maxtags : (this.itemMetadatum.metadatum.multiple == 'yes' || this.allowNew === true ? (this.maxMultipleValues !== undefined ? this.maxMultipleValues : null) : '1') );
                // For multivalued metadata without maxMultipleValues, the limit is infinet, so we should let add anyway.
                const shouldAddMore = hasMaxTagsValue !== null ? (this.selected.length < hasMaxTagsValue) : true;
                
                if ($event.latlng && shouldAddMore) {
                    const newLocationValue = $event.latlng.lat + ',' + $event.latlng.lng;
                    const existintSelectedValue = this.selected.indexOf((aSelected) => aSelected == newLocationValue);
                    if (existintSelectedValue < 0) {
                        this.selected.push(newLocationValue);
                        this.$emit('input', this.selected);
                    }
                }
            },
            onMarkerRemove($event, index) {
                console.log($event)
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