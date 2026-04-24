<template>
    <div
            :id="itemMetadatumIdentifier"
            class="tainacan-leaflet-map-container">

        <div
                v-show="!manualJsonEditorOpen"
                class="geojson-map-slot">
        <l-map
                :id="'map--' + itemMetadatumIdentifier"
                :ref="'map--' + itemMetadatumIdentifier"
                style="height: 320px; width:100%; min-width: 320px;"
                :zoom="initialZoom"
                :max-zoom="maxZoom"
                :center="[initialLatitude, initialLongitude]"
                :zoom-animation="true"
                :options="{ name: 'map--' + itemMetadatumIdentifier, trackResize: false, worldCopyJump: true }"
                @ready="onMapReady"
                @click="onMapClick">
            <l-tile-layer :url="mapProvider" :attribution="attribution" />

            <l-geo-json :key="geoJsonLayerKey" :geojson="renderedGeoJson" :options="geoJsonOptions" :options-style="geoJsonStyle" />

            <l-polyline
                    v-if="mode === 'creating-line' && draftCoordinates.length > 1"
                    :lat-lngs="draftLatLngs"
                    :color="'#5f3dc4'"
                    :weight="3"
                    :dash-array="'6 6'" />
            <l-polyline
                    v-if="mode === 'creating-polygon' && draftCoordinates.length > 1"
                    :lat-lngs="draftLatLngs"
                    :color="'#5f3dc4'"
                    :weight="3"
                    :dash-array="'6 6'" />
            <l-polygon
                    v-if="mode === 'creating-polygon' && draftCoordinates.length > 2"
                    :lat-lngs="draftPolygonLatLngs"
                    :color="'#5f3dc4'"
                    :weight="3"
                    :fill-opacity="0.12"
                    :dash-array="'6 6'" />

            <l-marker
                    v-for="(draftVertex, index) of draftLatLngs"
                    :key="'geojson-draft-' + mode + '-' + index"
                    :lat-lng="draftVertex"
                    :icon="draftVertexIcon" />

            <l-marker
                    v-if="isEditingPoint && selectedPointLatLng"
                    :key="'geojson-point-' + selectedFeatureIndex"
                    :lat-lng="selectedPointLatLng"
                    :draggable="true"
                    @click="onPointMarkerClick"
                    @dragend="onPointDragEnd" />

            <l-marker
                    v-for="(vertex, index) of editableRealVertices"
                    :key="'geojson-real-' + selectedFeatureIndex + '-' + index"
                    :lat-lng="[vertex[1], vertex[0]]"
                    :icon="editableVertexIcon"
                    :draggable="true"
                    @click="($event) => onRealVertexClick($event, index)"
                    @drag="($event) => onRealVertexDragMove($event, index)"
                    @dragend="($event) => onRealVertexDrag($event, index)" />

            <l-marker
                    v-for="placeholder of editablePlaceholderVertices"
                    :key="'geojson-ph-' + selectedFeatureIndex + '-' + placeholder.insertAfterIndex"
                    :lat-lng="[placeholder.coordinate[1], placeholder.coordinate[0]]"
                    :icon="placeholderVertexIcon"
                    :opacity="0.5"
                    @click="($event) => onPlaceholderClick($event, placeholder.insertAfterIndex)" />

            <l-control
                    v-if="isEditingFeature"
                    position="topright"
                    class="leaflet-bar">
                <div class="geojson-editing-controls map-panel">
                    <div
                            v-if="isEditingPoint"
                            class="geojson-point-inputs">
                        <b-input expanded :placeholder="-14.408656999999" type="text" :step="0.000000000001" :model-value="pointLatitudeInput" @update:model-value="onUpdateFromLatitudeInput" />
                        <b-input expanded :placeholder="-51.316689999999" type="text" :step="0.000000000001" :model-value="pointLongitudeInput" @update:model-value="onUpdateFromLongitudeInput" />
                    </div>
                    <a
                            class="remove-feature-button"
                            role="button"
                            tabindex="0"
                            outlined
                            :title="removeSelectedFeatureLabel"
                            :aria-label="removeSelectedFeatureLabel"
                            @click.prevent.stop="removeSelectedFeature">
                        <span class="icon is-small"><i class="tainacan-icon tainacan-icon-remove" /></span>
                        <span>{{ removeSelectedFeatureLabel }}</span>
                    </a>
                </div>
            </l-control>
        </l-map>
        </div>

        <div
                v-show="manualJsonEditorOpen"
                class="geojson-manual-editor">
            <b-field
                    :message="manualGeoJsonError || manualGeoJsonWarning"
                    :type="manualGeoJsonError ? 'is-danger' : (manualGeoJsonWarning ? 'is-warning' : '')">
                <b-input
                        type="textarea"
                        class="geojson-manual-editor__textarea"
                        rows="14"
                        :disabled="disabled"
                        :model-value="manualGeoJsonText"
                        @update:model-value="onManualGeoJsonTextInput"
                        @blur="onManualGeoJsonBlur" />
            </b-field>
        </div>

        <div class="geojson-toolbar">
            <div
                    v-if="showAddButtons && !manualJsonEditorOpen"
                    class="geojson-external-controls">
                <a class="add-link" :class="{ 'is-active-mode': mode === 'creating-point' }" role="button" tabindex="0" @click.prevent="startPointCreation" @keydown.enter.prevent="startPointCreation" @keydown.space.prevent="startPointCreation">
                    <span class="icon is-small"><i class="tainacan-icon has-text-secondary tainacan-icon-add" /></span>
                    &nbsp;{{ $i18n.get('label_add_point') }}
                </a>
                <a class="add-link" :class="{ 'is-active-mode': mode === 'creating-line' }" role="button" tabindex="0" @click.prevent="startLineCreation" @keydown.enter.prevent="startLineCreation" @keydown.space.prevent="startLineCreation">
                    <span class="icon is-small"><i class="tainacan-icon has-text-secondary tainacan-icon-add" /></span>
                    &nbsp;{{ $i18n.get('label_add_line') }}
                </a>
                <a class="add-link" :class="{ 'is-active-mode': mode === 'creating-polygon' }" role="button" tabindex="0" @click.prevent="startPolygonCreation" @keydown.enter.prevent="startPolygonCreation" @keydown.space.prevent="startPolygonCreation">
                    <span class="icon is-small"><i class="tainacan-icon has-text-secondary tainacan-icon-add" /></span>
                    &nbsp;{{ $i18n.get('label_add_polygon') }}
                </a>
            </div>
            <a
                    v-if="!disabled"
                    class="add-link geojson-toolbar__toggle"
                    role="button"
                    tabindex="0"
                    @click.prevent="toggleManualGeoJsonEditor"
                    @keydown.enter.prevent="toggleManualGeoJsonEditor"
                    @keydown.space.prevent="toggleManualGeoJsonEditor">
                <span class="icon">
                    <i 
                            v-if="!manualJsonEditorOpen"
                            class="tainacan-icon has-text-secondary tainacan-icon-edit tainacan-icon-1-125em" />
                    <i
                            v-else
                            class="tainacan-icon tainacan-icon-svg"
                            style="display: flex;">
                        <svg
                            xmlns="http://www.w3.org/2000/svg" 
                            viewBox="0 0 24 24">
                        <path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.34,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.66,20.97L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z" />
                    </svg>
                </i>
                </span>
                &nbsp;{{ manualJsonEditorOpen ? $i18n.get('label_geojson_back_to_map') : $i18n.get('label_geojson_edit_as_text') }}
            </a>
        </div>
    </div>
</template>

<script>
    import { nextTick } from 'vue';
    import { LMap, LTileLayer, LControl, LGeoJson, LMarker, LPolyline, LPolygon } from '@vue-leaflet/vue-leaflet';
    import 'leaflet/dist/leaflet.css';
    import * as Leaflet from 'leaflet';
    import iconUrl from 'leaflet/dist/images/marker-icon.png';
    import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
    import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

    delete Leaflet.Icon.Default.prototype._getIconUrl;
    Leaflet.Icon.Default.mergeOptions({ iconRetinaUrl, iconUrl, shadowUrl });

    export default {
        components: { LMap, LTileLayer, LControl, LGeoJson, LMarker, LPolyline, LPolygon },
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
                mapResizeObserver: null,
                mapObject: null,
                geoJsonLayerKey: 0,
                mode: 'select',
                selectedFeatureIndex: -1,
                draftCoordinates: [],
                pointLatitudeInput: -14.4086569,
                pointLongitudeInput: -51.31668,
                internalFeatureCollection: {
                    type: 'FeatureCollection',
                    features: []
                },
                suppressEditingExitUntil: 0,
                internalGeometryRevision: 0,
                manualJsonEditorOpen: false,
                manualGeoJsonText: '',
                manualGeoJsonError: '',
                manualGeoJsonWarning: '',
                manualGeoJsonValidationLimitBytes: 1048576
            };
        },
        computed: {
            mapProvider() {
                return this.itemMetadatum?.metadatum?.metadata_type_options?.map_provider || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
            },
            initialZoom() {
                return Number(this.itemMetadatum?.metadatum?.metadata_type_options?.initial_zoom || 5);
            },
            maxZoom() {
                return Number(this.itemMetadatum?.metadatum?.metadata_type_options?.maximum_zoom || 12);
            },
            initialLatitude() {
                return Number(this.itemMetadatum?.metadatum?.metadata_type_options?.initial_latitude || -14.4086569);
            },
            initialLongitude() {
                return Number(this.itemMetadatum?.metadatum?.metadata_type_options?.initial_longitude || -51.31668);
            },
            attribution() {
                return this.itemMetadatum?.metadatum?.metadata_type_options?.attribution || '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors';
            },
            itemMetadatumIdentifier() {
                return 'tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '');
            },
            isMultipleMetadata() {
                return this.itemMetadatum?.metadatum?.multiple === 'yes';
            },
            canAddFeature() {
                return this.isMultipleMetadata || this.internalFeatureCollection.features.length === 0;
            },
            showAddButtons() {
                return this.canAddFeature;
            },
            renderedGeoJson() {
                void this.internalGeometryRevision;
                const hideSelectedPointFromGeoJson = this.mode === 'editing'
                    && this.selectedFeatureIndex >= 0
                    && this.selectedGeometry?.type === 'Point';
                const features = this.internalFeatureCollection.features
                    .map((feature, index) => ({
                        ...feature,
                        properties: { ...(feature.properties || {}), __tainacan_index: index }
                    }))
                    .filter((_, index) => !(hideSelectedPointFromGeoJson && index === this.selectedFeatureIndex));
                return {
                    type: 'FeatureCollection',
                    features
                };
            },
            geoJsonOptions() {
                return {
                    bubblingMouseEvents: false,
                    pointToLayer: (feature, latlng) => Leaflet.marker(latlng),
                    onEachFeature: (feature, layer) => {
                        layer.on('click', (event) => {
                            if (event?.originalEvent) {
                                event.originalEvent.preventDefault();
                                event.originalEvent.stopPropagation();
                                event.originalEvent._stopped = true;
                            }
                            const index = Number(feature.properties?.__tainacan_index);
                            if (!isNaN(index))
                                this.enterEditingMode(index);
                        });
                    }
                };
            },
            geoJsonStyle() {
                return (feature) => {
                    const index = Number(feature.properties?.__tainacan_index);
                    const isSelected = this.isEditingFeature && this.selectedFeatureIndex === index;
                    return {
                        color: isSelected ? '#5f3dc4' : '#3273dc',
                        weight: isSelected ? 4 : 3,
                        fillOpacity: isSelected ? 0.2 : 0.12
                    };
                };
            },
            selectedFeature() {
                if (this.selectedFeatureIndex < 0)
                    return null;
                return this.internalFeatureCollection.features[this.selectedFeatureIndex] || null;
            },
            selectedGeometry() {
                return this.selectedFeature?.geometry || null;
            },
            isEditingFeature() {
                return this.mode === 'editing' && this.selectedFeatureIndex >= 0;
            },
            isEditingPoint() {
                return this.isEditingFeature && this.selectedGeometry?.type === 'Point';
            },
            isEditingLineOrPolygon() {
                return this.isEditingFeature && ['LineString', 'Polygon'].includes(this.selectedGeometry?.type);
            },
            selectedPointLatLng() {
                if (!this.isEditingPoint)
                    return null;
                return [this.selectedGeometry.coordinates[1], this.selectedGeometry.coordinates[0]];
            },
            editableRealVertices() {
                if (!this.isEditingLineOrPolygon)
                    return [];
                if (this.selectedGeometry.type === 'LineString')
                    return this.selectedGeometry.coordinates;
                return this.selectedGeometry.coordinates[0].slice(0, -1);
            },
            editablePlaceholderVertices() {
                if (!this.isEditingLineOrPolygon)
                    return [];
                const vertices = this.editableRealVertices;
                if (vertices.length < 2)
                    return [];

                const placeholders = [];
                const isPolygon = this.selectedGeometry.type === 'Polygon';
                const segmentCount = isPolygon ? vertices.length : vertices.length - 1;
                for (let index = 0; index < segmentCount; index++) {
                    const start = vertices[index];
                    const end = isPolygon ? vertices[(index + 1) % vertices.length] : vertices[index + 1];
                    placeholders.push({
                        coordinate: [(start[0] + end[0]) / 2, (start[1] + end[1]) / 2],
                        insertAfterIndex: index
                    });
                }
                return placeholders;
            },
            draftLatLngs() {
                return this.draftCoordinates.map((coordinate) => [coordinate[1], coordinate[0]]);
            },
            draftPolygonLatLngs() {
                if (this.draftCoordinates.length < 3)
                    return [];
                return [this.draftCoordinates.map((coordinate) => [coordinate[1], coordinate[0]])];
            },
            draftVertexIcon() {
                return Leaflet.divIcon({ className: 'tainacan-geojson-vertex-icon tainacan-geojson-vertex-icon--preview', iconSize: [14, 14], iconAnchor: [7, 7] });
            },
            editableVertexIcon() {
                return Leaflet.divIcon({ className: 'tainacan-geojson-vertex-icon tainacan-geojson-vertex-icon--editable', iconSize: [14, 14], iconAnchor: [7, 7] });
            },
            placeholderVertexIcon() {
                return Leaflet.divIcon({ className: 'tainacan-geojson-vertex-icon tainacan-geojson-vertex-icon--placeholder', iconSize: [14, 14], iconAnchor: [7, 7] });
            },
            removeSelectedFeatureLabel() {
                switch (this.selectedGeometry?.type) {
                    case 'Point':
                    case 'MultiPoint':
                        return this.$i18n.get('label_remove_point');
                    case 'LineString':
                    case 'MultiLineString':
                        return this.$i18n.get('label_remove_line');
                    case 'Polygon':
                    case 'MultiPolygon':
                        return this.$i18n.get('label_remove_polygon');
                    default:
                        return this.$i18n.get('label_remove_value');
                }
            }
        },
        watch: {
            value: {
                handler(newValue) {
                    this.internalFeatureCollection = this.parseValueToFeatureCollection(newValue);
                    if (!this.internalFeatureCollection.features.length) {
                        this.mode = 'select';
                        this.selectedFeatureIndex = -1;
                    } else if (this.selectedFeatureIndex >= this.internalFeatureCollection.features.length) {
                        this.leaveEditingMode();
                    }
                    this.geoJsonLayerKey++;
                    if (this.manualJsonEditorOpen)
                        this.manualGeoJsonText = this.serializeFeatureCollectionForEditor();
                },
                deep: true
            }
        },
        created() {
            this.internalFeatureCollection = this.parseValueToFeatureCollection(this.value);
            this.pointLatitudeInput = this.initialLatitude;
            this.pointLongitudeInput = this.initialLongitude;
            this.throttledVertexDragMove = _.throttle((vertexIndex, lng, lat) => {
                this.applyRealVertexCoordinate(vertexIndex, lng, lat);
            }, 32);
            this.validateManualGeoJsonDebounced = _.debounce(() => {
                this.validateManualGeoJsonInput();
            }, 750);
        },
        mounted() {
            nextTick(() => {
                const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                this.handleWindowResize(mapComponentRef);

                if (this.$refs[mapComponentRef]?.$el) {
                    this.mapResizeObserver = new ResizeObserver((entries) => {
                        entries.forEach((entry) => {
                            const { width, height } = entry.contentRect;
                            if (width > 0 && height > 0)
                                this.handleWindowResize(mapComponentRef);
                        });
                    });
                    this.mapResizeObserver.observe(this.$refs[mapComponentRef].$el);
                }
            });
        },
        beforeUnmount() {
            if (this.mapResizeObserver) {
                this.mapResizeObserver.disconnect();
                this.mapResizeObserver = null;
            }
            if (this.throttledVertexDragMove?.cancel)
                this.throttledVertexDragMove.cancel();
            if (this.validateManualGeoJsonDebounced?.cancel)
                this.validateManualGeoJsonDebounced.cancel();
        },
        methods: {
            serializeFeatureCollectionForEditor() {
                return JSON.stringify(this.internalFeatureCollection, null, 2);
            },
            getTextSizeInBytes(text) {
                if (typeof text !== 'string')
                    return 0;
                if (typeof TextEncoder !== 'undefined')
                    return new TextEncoder().encode(text).length;
                return text.length;
            },
            cloneFeatureForStorage(feature) {
                if (!feature || feature.type !== 'Feature')
                    return null;
                const properties = { ...(feature.properties || {}) };
                delete properties.__tainacan_index;
                return {
                    type: 'Feature',
                    geometry: JSON.parse(JSON.stringify(feature.geometry)),
                    properties: Object.keys(properties).length ? properties : {}
                };
            },
            parseStrictFeatureCollectionFromString(text) {
                let parsed;
                try {
                    parsed = JSON.parse(text);
                } catch (error) {
                    return { ok: false };
                }
                if (!parsed || typeof parsed !== 'object')
                    return { ok: false };
                const fc = { type: 'FeatureCollection', features: [] };
                if (parsed.type === 'FeatureCollection') {
                    if (!Array.isArray(parsed.features))
                        return { ok: false };
                    for (const feature of parsed.features) {
                        if (!feature || feature.type !== 'Feature' || !feature.geometry || typeof feature.geometry !== 'object')
                            return { ok: false };
                        if (!this.isAllowedGeometryType(feature.geometry.type))
                            return { ok: false };
                        const clone = this.cloneFeatureForStorage(feature);
                        if (!clone)
                            return { ok: false };
                        fc.features.push(clone);
                    }
                    return { ok: true, fc };
                }
                if (parsed.type === 'Feature') {
                    if (!parsed.geometry || !this.isAllowedGeometryType(parsed.geometry.type))
                        return { ok: false };
                    const clone = this.cloneFeatureForStorage(parsed);
                    if (!clone)
                        return { ok: false };
                    fc.features.push(clone);
                    return { ok: true, fc };
                }
                if (this.isAllowedGeometryType(parsed.type)) {
                    fc.features.push({
                        type: 'Feature',
                        geometry: JSON.parse(JSON.stringify(parsed)),
                        properties: {}
                    });
                    return { ok: true, fc };
                }
                return { ok: false };
            },
            validateManualGeoJsonInput(showToast = false) {
                const manualSize = this.getTextSizeInBytes(this.manualGeoJsonText);
                if (manualSize > this.manualGeoJsonValidationLimitBytes) {
                    const message = this.$i18n.get('info_warning_geojson_validation_skipped_1mb');
                    this.manualGeoJsonError = '';
                    this.manualGeoJsonWarning = message;
                    if (showToast) {
                        this.$buefy.toast.open({
                            duration: 4000,
                            message,
                            position: 'is-bottom',
                            type: 'is-warning'
                        });
                    }
                    return { ok: true, skippedValidation: true };
                }
                const result = this.parseStrictFeatureCollectionFromString(this.manualGeoJsonText);
                if (result.ok) {
                    this.manualGeoJsonError = '';
                    this.manualGeoJsonWarning = '';
                    return result;
                }
                const message = this.$i18n.get('info_error_invalid_geojson');
                this.manualGeoJsonError = message;
                this.manualGeoJsonWarning = '';
                if (showToast) {
                    this.$buefy.toast.open({
                        duration: 4000,
                        message,
                        position: 'is-bottom',
                        type: 'is-danger'
                    });
                }
                return result;
            },
            onManualGeoJsonTextInput(value) {
                this.manualGeoJsonText = value;
                this.manualGeoJsonError = '';
                this.manualGeoJsonWarning = '';
                if (this.validateManualGeoJsonDebounced)
                    this.validateManualGeoJsonDebounced();
            },
            onManualGeoJsonBlur() {
                this.validateManualGeoJsonInput();
            },
            toggleManualGeoJsonEditor() {
                if (this.disabled)
                    return;
                if (this.manualJsonEditorOpen) {
                    if (this.validateManualGeoJsonDebounced?.cancel)
                        this.validateManualGeoJsonDebounced.cancel();
                    const result = this.validateManualGeoJsonInput(true);
                    if (!result.ok) {
                        return;
                    }
                    if (result.skippedValidation) {
                        this.leaveEditingMode();
                        this.manualGeoJsonError = '';
                        this.manualJsonEditorOpen = false;
                        this.$emit('update:value', [this.manualGeoJsonText]);
                        nextTick(() => {
                            const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                            this.handleWindowResize(mapComponentRef);
                        });
                        return;
                    }
                    this.internalFeatureCollection = result.fc;
                    this.leaveEditingMode();
                    this.internalGeometryRevision++;
                    this.manualGeoJsonError = '';
                    this.manualJsonEditorOpen = false;
                    this.emitCurrentValue();
                    nextTick(() => {
                        const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;
                        this.handleWindowResize(mapComponentRef);
                    });
                    return;
                }
                this.leaveEditingMode();
                this.manualGeoJsonText = this.serializeFeatureCollectionForEditor();
                this.manualGeoJsonError = '';
                this.manualGeoJsonWarning = '';
                this.manualJsonEditorOpen = true;
            },
            parseValueToFeatureCollection(value) {
                const featureCollection = { type: 'FeatureCollection', features: [] };
                const values = Array.isArray(value) ? value : [value];

                values.forEach((singleValue) => {
                    if (typeof singleValue !== 'string' || singleValue.trim() === '')
                        return;
                    try {
                        const parsed = JSON.parse(singleValue);
                        if (!parsed?.type)
                            return;
                        if (parsed.type === 'FeatureCollection' && Array.isArray(parsed.features))
                            parsed.features.forEach((feature) => this.tryPushFeature(featureCollection, feature));
                        else if (parsed.type === 'Feature')
                            this.tryPushFeature(featureCollection, parsed);
                        else if (this.isAllowedGeometryType(parsed.type))
                            this.tryPushFeature(featureCollection, { type: 'Feature', geometry: parsed, properties: {} });
                    } catch (error) {
                        return;
                    }
                });
                return featureCollection;
            },
            tryPushFeature(featureCollection, feature) {
                if (!feature || feature.type !== 'Feature' || !feature.geometry || !this.isAllowedGeometryType(feature.geometry.type))
                    return;
                featureCollection.features.push(feature);
            },
            isAllowedGeometryType(type) {
                return ['Point', 'LineString', 'Polygon', 'MultiPoint', 'MultiLineString', 'MultiPolygon'].includes(type);
            },
            onMapReady(leafletMap) {
                this.mapObject = leafletMap;
                if (this.internalFeatureCollection.features.length)
                    nextTick(() => this.fitMapToLayers());
            },
            getCurrentMapCenterCoordinate() {
                if (this.mapObject) {
                    const center = this.mapObject.getCenter();
                    return [center.lng, center.lat];
                }
                return [this.initialLongitude, this.initialLatitude];
            },
            startPointCreation() {
                if (!this.canAddFeature)
                    return;
                const centerCoordinate = this.getCurrentMapCenterCoordinate();
                this.internalFeatureCollection.features.push({
                    type: 'Feature',
                    geometry: { type: 'Point', coordinates: centerCoordinate },
                    properties: {}
                });
                this.enterEditingMode(this.internalFeatureCollection.features.length - 1);
                this.emitCurrentValue();
            },
            startLineCreation() {
                if (!this.canAddFeature)
                    return;
                this.selectedFeatureIndex = -1;
                this.mode = 'creating-line';
                this.draftCoordinates = [this.getCurrentMapCenterCoordinate()];
            },
            startPolygonCreation() {
                if (!this.canAddFeature)
                    return;
                this.selectedFeatureIndex = -1;
                this.mode = 'creating-polygon';
                this.draftCoordinates = [this.getCurrentMapCenterCoordinate()];
            },
            enterEditingMode(featureIndex) {
                this.mode = 'editing';
                this.selectedFeatureIndex = featureIndex;
                this.draftCoordinates = [];
                this.syncPointInputsFromSelection();
            },
            leaveEditingMode() {
                this.mode = 'select';
                this.selectedFeatureIndex = -1;
                this.draftCoordinates = [];
            },
            onMapClick(event) {
                if (!event?.latlng)
                    return;

                if (this.mode === 'creating-line' || this.mode === 'creating-polygon') {
                    this.draftCoordinates.push([event.latlng.lng, event.latlng.lat]);
                    this.finishCreationIfValid();
                    return;
                }

                if (this.mode === 'editing') {
                    if (Date.now() < this.suppressEditingExitUntil)
                        return;
                    this.leaveEditingMode();
                    this.fitMapToLayers();
                }
            },
            finishCreationIfValid() {
                if (this.mode === 'creating-line' && this.draftCoordinates.length >= 2) {
                    this.internalFeatureCollection.features.push({
                        type: 'Feature',
                        geometry: { type: 'LineString', coordinates: this.draftCoordinates.slice() },
                        properties: {}
                    });
                } else if (this.mode === 'creating-polygon' && this.draftCoordinates.length >= 3) {
                    const ring = this.draftCoordinates.slice();
                    ring.push(this.draftCoordinates[0]);
                    this.internalFeatureCollection.features.push({
                        type: 'Feature',
                        geometry: { type: 'Polygon', coordinates: [ring] },
                        properties: {}
                    });
                } else {
                    return;
                }

                this.enterEditingMode(this.internalFeatureCollection.features.length - 1);
                this.emitCurrentValue();
            },
            removeSelectedFeature() {
                if (!this.isEditingFeature)
                    return;
                this.internalFeatureCollection.features.splice(this.selectedFeatureIndex, 1);
                this.leaveEditingMode();
                this.emitCurrentValue();
            },
            onPointMarkerClick(event) {
                if (event?.originalEvent) {
                    event.originalEvent.preventDefault();
                    event.originalEvent.stopPropagation();
                }
            },
            onPointDragEnd(event) {
                if (!this.isEditingPoint || !event?.target?._latlng)
                    return;
                this.selectedGeometry.coordinates = [event.target._latlng.lng, event.target._latlng.lat];
                this.syncPointInputsFromSelection();
                this.suppressEditingExitUntil = Date.now() + 400;
                this.emitCurrentValue();
            },
            onUpdateFromLatitudeInput: _.debounce(function(value) {
                if (!this.isEditingPoint)
                    return;
                const parsed = Number(value);
                if (isNaN(parsed))
                    return;
                this.pointLatitudeInput = parsed;
                this.selectedGeometry.coordinates = [Number(this.pointLongitudeInput), parsed];
                this.emitCurrentValue();
            }, 250),
            onUpdateFromLongitudeInput: _.debounce(function(value) {
                if (!this.isEditingPoint)
                    return;
                const parsed = Number(value);
                if (isNaN(parsed))
                    return;
                this.pointLongitudeInput = parsed;
                this.selectedGeometry.coordinates = [parsed, Number(this.pointLatitudeInput)];
                this.emitCurrentValue();
            }, 250),
            syncPointInputsFromSelection() {
                if (!this.isEditingPoint)
                    return;
                this.pointLatitudeInput = this.selectedGeometry.coordinates[1];
                this.pointLongitudeInput = this.selectedGeometry.coordinates[0];
            },
            onRealVertexClick(event, vertexIndex) {
                if (event?.originalEvent) {
                    event.originalEvent.preventDefault();
                    event.originalEvent.stopPropagation();
                }
                this.removeRealVertex(vertexIndex);
            },
            applyRealVertexCoordinate(vertexIndex, lng, lat) {
                if (!this.isEditingLineOrPolygon)
                    return;
                const updatedCoordinate = [lng, lat];
                if (this.selectedGeometry.type === 'LineString') {
                    this.selectedGeometry.coordinates.splice(vertexIndex, 1, updatedCoordinate);
                } else {
                    const ring = this.selectedGeometry.coordinates[0];
                    ring.splice(vertexIndex, 1, updatedCoordinate);
                    ring[ring.length - 1] = ring[0];
                }
                this.internalGeometryRevision++;
            },
            onRealVertexDragMove(event, vertexIndex) {
                const latlng = event?.target?.getLatLng?.() ?? event?.target?._latlng;
                if (!latlng)
                    return;
                this.throttledVertexDragMove(vertexIndex, latlng.lng, latlng.lat);
            },
            onRealVertexDrag(event, vertexIndex) {
                if (this.throttledVertexDragMove?.cancel)
                    this.throttledVertexDragMove.cancel();
                const latlng = event?.target?.getLatLng?.() ?? event?.target?._latlng;
                if (!this.isEditingLineOrPolygon || !latlng)
                    return;
                this.applyRealVertexCoordinate(vertexIndex, latlng.lng, latlng.lat);
                this.suppressEditingExitUntil = Date.now() + 400;
                this.emitCurrentValue();
            },
            removeRealVertex(vertexIndex) {
                if (!this.isEditingLineOrPolygon)
                    return;
                if (this.selectedGeometry.type === 'LineString') {
                    if (this.selectedGeometry.coordinates.length <= 2)
                        return;
                    this.selectedGeometry.coordinates.splice(vertexIndex, 1);
                } else {
                    const ring = this.selectedGeometry.coordinates[0];
                    if (ring.length <= 4)
                        return;
                    ring.splice(vertexIndex, 1);
                    ring[ring.length - 1] = ring[0];
                }
                this.internalGeometryRevision++;
                this.emitCurrentValue();
            },
            onPlaceholderClick(event, insertAfterIndex) {
                if (event?.originalEvent) {
                    event.originalEvent.preventDefault();
                    event.originalEvent.stopPropagation();
                }
                if (!this.isEditingLineOrPolygon)
                    return;
                if (this.selectedGeometry.type === 'LineString') {
                    const coords = this.selectedGeometry.coordinates;
                    const start = coords[insertAfterIndex];
                    const end = coords[insertAfterIndex + 1];
                    coords.splice(insertAfterIndex + 1, 0, [(start[0] + end[0]) / 2, (start[1] + end[1]) / 2]);
                } else {
                    const ring = this.selectedGeometry.coordinates[0];
                    const editableRing = ring.slice(0, -1);
                    const nextIndex = (insertAfterIndex + 1) % editableRing.length;
                    const start = editableRing[insertAfterIndex];
                    const end = editableRing[nextIndex];
                    editableRing.splice(insertAfterIndex + 1, 0, [(start[0] + end[0]) / 2, (start[1] + end[1]) / 2]);
                    editableRing.push(editableRing[0]);
                    this.selectedGeometry.coordinates[0] = editableRing;
                }
                this.internalGeometryRevision++;
                this.emitCurrentValue();
            },
            emitCurrentValue() {
                this.geoJsonLayerKey++;
                if (!this.internalFeatureCollection.features.length) {
                    this.$emit('update:value', []);
                    this.resetMapToInitialView();
                    return;
                }
                this.$emit('update:value', [JSON.stringify(this.internalFeatureCollection)]);
            },
            resetMapToInitialView() {
                if (!this.mapObject)
                    return;
                this.mapObject.setView([this.initialLatitude, this.initialLongitude], this.initialZoom);
            },
            fitMapToLayers() {
                if (!this.mapObject)
                    return;
                if (!this.internalFeatureCollection.features.length) {
                    this.mapObject.setView([this.initialLatitude, this.initialLongitude], this.initialZoom);
                    return;
                }
                const tempLayer = Leaflet.geoJSON(this.internalFeatureCollection);
                const bounds = tempLayer.getBounds();
                if (bounds?.isValid())
                    this.mapObject.flyToBounds(bounds, { animate: true, maxZoom: this.maxZoom });
            },
            handleWindowResize(mapComponentRef) {
                setTimeout(() => {
                    if (this.$refs[mapComponentRef]?.leafletObject)
                        this.$refs[mapComponentRef].leafletObject.invalidateSize(true);
                }, 300);
            }
        }
    };
</script>

<style lang="scss" scoped>
.tainacan-leaflet-map-container {
    .geojson-map-slot {
        display: block;
    }

    .geojson-manual-editor {
        width: 100%;
        min-width: 320px;
        border: 1px solid var(--tainacan-input-border-color);
        border-radius: var(--tainacan-input-border-radius, 3px) var(--tainacan-input-border-radius, 3px) 0 0;
        border-bottom: 0;
        padding: 0.5rem;
        background: var(--tainacan-input-background-color, #fff);
        box-sizing: border-box;

        .field {
            padding: 0 !important;
            margin: 0 !important;
        }

        :deep(.geojson-manual-editor__textarea textarea) {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
            font-size: 0.8125rem;
            min-height: calc(320px - 1rem);
        }
    }

    .geojson-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 1rem;
        border: 1px solid var(--tainacan-input-border-color);
        border-top: 0;
        border-bottom-left-radius: var(--tainacan-input-border-radius, 3px);
        border-bottom-right-radius: var(--tainacan-input-border-radius, 3px);
        padding: 0.5rem 0.75rem;
        background: var(--tainacan-input-background-color, #fff);
    }

    .geojson-external-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1rem;
        flex: 1 1 auto;
        align-items: center;
        min-width: 0;
    }

    .geojson-toolbar__toggle {
        flex: 0 0 auto;
        margin-inline-start: auto;
    }

    .add-link {
        font-size: 0.8125rem;
        line-height: 1.25rem;
        display: inline-flex;
        align-items: center;
    }

    .add-link.is-active-mode {
        color: var(--tainacan-secondary);
        font-weight: 600;
    }
}

.tainacan-leaflet-map-container .leaflet-container {
    border: 1px solid var(--tainacan-input-border-color);
    border-top-left-radius: var(--tainacan-input-border-radius, 3px);
    border-top-right-radius: var(--tainacan-input-border-radius, 3px);
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    z-index: 0;

    .leaflet-marker-pane {
        filter: hue-rotate(-22deg);
    }

    .geojson-editing-controls {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        background: rgba(0,0,0,0.2);
        font-size: 1rem;
        min-width: auto;

        .geojson-point-inputs {
            display: flex;
            flex: 1 1 12rem;
            min-width: 0;

            :deep(input) {
                font-size: 0.75em;
                min-height: 32px;
                border-radius: 0 !important;
            }
        }

        .button:not(.is-small):not(.is-medium):not(.is-large) {
            color: var(--tainacan-secondary);
            border-radius: 0 !important;
            line-height: 1.7rem;
            background-color: var(--tainacan-input-background-color, #fff) !important;
        }

        .remove-feature-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            align-self: center;
            gap: 0.375rem;
            padding: 0 0.625rem;
            white-space: nowrap;
            width: auto;
            font-size: 0.813em;
            font-family: var(--tainacan-font-family);
        }
    }

    .map-panel {
        display: flex;
        gap: 0.125rem;
    }
}

.tainacan-geojson-vertex-icon {
    width: 14px;
    height: 14px;
    border-radius: 999px;
    box-sizing: border-box;
    background: var(--tainacan-input-background-color, #fff);
    border: 2px solid var(--tainacan-secondary);
}

.tainacan-geojson-vertex-icon--preview {
    opacity: 0.85;
}

.tainacan-geojson-vertex-icon--editable {
    box-shadow: 0 0 0 1px var(--tainacan-input-background-color, #fff);
    transition: border 0.1s ease-in-out, box-shadow 0.1s ease-in-out;
}

.tainacan-geojson-vertex-icon--editable:hover {
    border: 3px solid var(--tainacan-secondary);
    box-shadow: 0 0 0 3px var(--tainacan-input-background-color, #fff);
}

.tainacan-geojson-vertex-icon--placeholder {
    border-style: dashed;
    opacity: 0.5;
    transition: opacity 0.1s ease-in-out;
}
.tainacan-geojson-vertex-icon--placeholder:hover {
    opacity: 1.0;
}
</style>
