<template>
    <div
            :id="itemMetadatumIdentifier"
            class="tainacan-leaflet-map-container tainacan-geojson-map-container">
        <l-map
                :id="'map--' + itemMetadatumIdentifier"
                :ref="'map--' + itemMetadatumIdentifier"
                style="height: 320px; width:100%; min-width: 320px;"
                :zoom="initialZoom"
                :max-zoom="maxZoom"
                :center="[initialLatitude, initialLongitude]"
                :zoom-animation="true"
                :options="{
                    name: 'map--' + itemMetadatumIdentifier,
                    trackResize: false,
                    worldCopyJump: true
                }"
                @ready="onMapReady"
                @click="onMapClick">
            <l-tile-layer
                    :url="mapProvider"
                    :attribution="attribution" />
            <l-geo-json
                    :key="geoJsonLayerKey"
                    :geojson="renderedGeoJson"
                    :options="geoJsonOptions"
                    :options-style="geoJsonStyle" />
            <l-polyline
                    v-if="drawingPreviewLineCoordinates.length > 1 && drawingMode === 'LineString'"
                    :lat-lngs="drawingPreviewLineCoordinates"
                    :color="'#5f3dc4'"
                    :weight="3"
                    :dash-array="'6 6'" />
            <l-polyline
                    v-if="drawingPreviewLineCoordinates.length > 1 && drawingMode === 'Polygon'"
                    :lat-lngs="drawingPreviewLineCoordinates"
                    :color="'#5f3dc4'"
                    :weight="3"
                    :dash-array="'6 6'" />
            <l-polygon
                    v-if="drawingPreviewLineCoordinates.length > 2 && drawingMode === 'Polygon'"
                    :lat-lngs="drawingPreviewPolygonCoordinates"
                    :color="'#5f3dc4'"
                    :weight="3"
                    :fill-opacity="0.12"
                    :dash-array="'6 6'" />
            <l-marker
                    v-for="(drawingVertex, index) of drawingPreviewVertexMarkers"
                    :key="'drawing-vertex-' + index"
                    :lat-lng="drawingVertex"
                    :icon="drawingVertexIcon"
                    :opacity="0.85" />
            <l-marker
                    v-for="(vertex, index) of editableVertices"
                    :key="'vertex-' + index"
                    :lat-lng="[vertex[1], vertex[0]]"
                    :icon="editableVertexIcon"
                    :draggable="true"
                    @click="($event) => onEditableVertexClick($event, index)"
                    @dragend="($event) => onVertexDrag($event, index)" />
            <l-control position="bottomleft">
                <div class="geojson-input-panel draw-mode-panel">
                    <b-button
                            :class="['icon-only-button', { 'is-active-drawing-mode': drawingMode === 'Point' }]"
                            outlined
                            :type="drawingMode === 'Point' ? 'is-primary' : ''"
                            :disabled="!canAddFeature"
                            :title="!canAddFeature ? 'This metadata is single-valued and already has one geometry' : 'Point'"
                            aria-label="Point drawing mode"
                            @click.prevent.stop="setDrawingMode('Point')">
                        <svg
                                class="button-icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                            <circle cx="12" cy="12" r="5" />
                        </svg>
                        <span class="screen-reader-only">Point</span>
                    </b-button>
                    <b-button
                            :class="['icon-only-button', { 'is-active-drawing-mode': drawingMode === 'LineString' }]"
                            outlined
                            :type="drawingMode === 'LineString' ? 'is-primary' : ''"
                            :disabled="!canAddFeature"
                            :title="!canAddFeature ? 'This metadata is single-valued and already has one geometry' : 'Line'"
                            aria-label="Line drawing mode"
                            @click.prevent.stop="setDrawingMode('LineString')">
                        <svg
                                class="button-icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                            <line x1="5" y1="17" x2="19" y2="7" />
                            <circle cx="5" cy="17" r="2.2" />
                            <circle cx="19" cy="7" r="2.2" />
                        </svg>
                        <span class="screen-reader-only">Line</span>
                    </b-button>
                    <b-button
                            :class="['icon-only-button', { 'is-active-drawing-mode': drawingMode === 'Polygon' }]"
                            outlined
                            :type="drawingMode === 'Polygon' ? 'is-primary' : ''"
                            :disabled="!canAddFeature"
                            :title="!canAddFeature ? 'This metadata is single-valued and already has one geometry' : 'Polygon'"
                            aria-label="Polygon drawing mode"
                            @click.prevent.stop="setDrawingMode('Polygon')">
                        <svg
                                class="button-icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                            <polygon points="6,16 9,7 18,6 20,13 13,18" />
                        </svg>
                        <span class="screen-reader-only">Polygon</span>
                    </b-button>
                </div>
            </l-control>
            <l-control position="topright">
                <div class="geojson-input-panel actions-panel">
                    <b-button
                            v-if="isDrawingModeRequiringFinish"
                            class="icon-only-button"
                            outlined
                            :disabled="!canFinishCurrentDrawing"
                            :title="$i18n.get('save')"
                            :aria-label="$i18n.get('save')"
                            @click.prevent.stop="finishDrawing">
                        <svg
                                class="button-icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                            <polyline points="4,13 9,18 20,7" />
                        </svg>
                        <span class="screen-reader-only">{{ $i18n.get('save') }}</span>
                    </b-button>
                    <b-button
                            v-if="isDrawingModeRequiringFinish"
                            class="icon-only-button"
                            outlined
                            :disabled="!canUndoDrawingCoordinate"
                            title="Undo last vertex"
                            aria-label="Undo last vertex"
                            @click.prevent.stop="undoDrawingCoordinate">
                        <svg
                                class="button-icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true">
                            <polyline points="9,7 4,12 9,17" />
                            <path d="M20 18c0-4.2-3.4-7.5-7.5-7.5H4" />
                        </svg>
                        <span class="screen-reader-only">Undo last vertex</span>
                    </b-button>
                    <b-button
                            class="icon-only-button"
                            outlined
                            :disabled="selectedFeatureIndex < 0"
                            :type="isEditingVertices ? 'is-primary' : ''"
                            title="Toggle vertex edit mode"
                            aria-label="Toggle vertex edit mode"
                            @click.prevent.stop="toggleVertexEditing">
                        <span class="icon is-small" aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-edit" />
                        </span>
                        <span class="screen-reader-only">Toggle vertex edit mode</span>
                    </b-button>
                    <b-button
                            class="icon-only-button"
                            outlined
                            :disabled="!canRemoveSelectedVertex"
                            title="Remove selected vertex"
                            aria-label="Remove selected vertex"
                            @click.prevent.stop="removeSelectedVertex">
                        <span class="icon is-small" aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-cancel" />
                        </span>
                        <span class="screen-reader-only">Remove selected vertex</span>
                    </b-button>
                    <b-button
                            class="icon-only-button"
                            outlined
                            :disabled="selectedFeatureIndex < 0"
                            :title="$i18n.get('label_remove_value')"
                            :aria-label="$i18n.get('label_remove_value')"
                            @click.prevent.stop="removeSelectedFeature">
                        <span class="icon is-small" aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-delete" />
                        </span>
                        <span class="screen-reader-only">{{ $i18n.get('label_remove_value') }}</span>
                    </b-button>
                    <b-button
                            class="icon-only-button"
                            outlined
                            :title="$i18n.get('label_clean')"
                            :aria-label="$i18n.get('label_clean')"
                            @click.prevent.stop="clearAllFeatures">
                        <span class="icon is-small" aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-close" />
                        </span>
                        <span class="screen-reader-only">{{ $i18n.get('label_clean') }}</span>
                    </b-button>
                </div>
            </l-control>
        </l-map>
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
    Leaflet.Icon.Default.mergeOptions({
        iconRetinaUrl: iconRetinaUrl,
        iconUrl: iconUrl,
        shadowUrl: shadowUrl
    });

    export default {
        components: {
            LMap,
            LTileLayer,
            LControl,
            LGeoJson,
            LMarker,
            LPolyline,
            LPolygon
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
                mapResizeObserver: null,
                mapObject: null,
                geoJsonLayerKey: 0,
                selectedFeatureIndex: -1,
                selectedEditableVertexIndex: -1,
                isEditingVertices: false,
                drawingMode: 'Point',
                drawingCoordinates: [],
                internalFeatureCollection: {
                    type: 'FeatureCollection',
                    features: []
                }
            };
        },
        computed: {
            mapProvider() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.map_provider ? this.itemMetadatum.metadatum.metadata_type_options.map_provider : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
            },
            initialZoom() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.initial_zoom ? Number(this.itemMetadatum.metadatum.metadata_type_options.initial_zoom) : 5;
            },
            maxZoom() {
                return this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.maximum_zoom ? Number(this.itemMetadatum.metadatum.metadata_type_options.maximum_zoom) : 12;
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
            renderedGeoJson() {
                return {
                    type: 'FeatureCollection',
                    features: this.internalFeatureCollection.features.map((feature, index) => ({
                        ...feature,
                        properties: {
                            ...(feature.properties || {}),
                            __tainacan_index: index
                        }
                    }))
                };
            },
            geoJsonOptions() {
                return {
                    pointToLayer: (feature, latlng) => Leaflet.marker(latlng),
                    onEachFeature: (feature, layer) => {
                        layer.on('click', () => {
                            const index = Number(feature.properties && feature.properties.__tainacan_index);
                            if (!isNaN(index)) {
                                this.selectedFeatureIndex = index;
                                this.selectedEditableVertexIndex = -1;
                                this.isEditingVertices = false;
                            }
                        });
                    }
                };
            },
            geoJsonStyle() {
                return (feature) => {
                    const index = Number(feature.properties && feature.properties.__tainacan_index);
                    const isSelected = this.selectedFeatureIndex >= 0 && this.selectedFeatureIndex === index;
                    return {
                        color: isSelected ? '#5f3dc4' : '#3273dc',
                        weight: isSelected ? 4 : 3,
                        fillOpacity: isSelected ? 0.2 : 0.12
                    };
                };
            },
            isDrawingModeRequiringFinish() {
                return this.drawingMode === 'LineString' || this.drawingMode === 'Polygon';
            },
            canFinishCurrentDrawing() {
                if (this.drawingMode === 'LineString')
                    return this.drawingCoordinates.length >= 2;
                if (this.drawingMode === 'Polygon')
                    return this.drawingCoordinates.length >= 3;
                return false;
            },
            canUndoDrawingCoordinate() {
                return this.drawingCoordinates.length > 0;
            },
            isMultipleMetadata() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.multiple === 'yes'
                );
            },
            canAddFeature() {
                return this.isMultipleMetadata || this.internalFeatureCollection.features.length === 0;
            },
            selectedFeatureGeometry() {
                if (this.selectedFeatureIndex < 0 || !this.internalFeatureCollection.features[this.selectedFeatureIndex])
                    return null;
                return this.internalFeatureCollection.features[this.selectedFeatureIndex].geometry || null;
            },
            canRemoveSelectedVertex() {
                if (!this.isEditingVertices || this.selectedEditableVertexIndex < 0 || !this.selectedFeatureGeometry)
                    return false;

                if (this.selectedFeatureGeometry.type === 'LineString')
                    return this.selectedFeatureGeometry.coordinates.length > 2;

                if (this.selectedFeatureGeometry.type === 'Polygon') {
                    const ring = this.selectedFeatureGeometry.coordinates && this.selectedFeatureGeometry.coordinates[0] ? this.selectedFeatureGeometry.coordinates[0] : [];
                    return ring.length > 4;
                }

                return false;
            },
            editableVertices() {
                if (!this.isEditingVertices)
                    return [];

                if (this.selectedFeatureIndex < 0 || !this.internalFeatureCollection.features[this.selectedFeatureIndex])
                    return [];

                const selectedGeometry = this.internalFeatureCollection.features[this.selectedFeatureIndex].geometry;
                if (!selectedGeometry)
                    return [];

                if (selectedGeometry.type === 'Point')
                    return [selectedGeometry.coordinates];

                if (selectedGeometry.type === 'LineString')
                    return selectedGeometry.coordinates;

                if (selectedGeometry.type === 'Polygon' && selectedGeometry.coordinates && selectedGeometry.coordinates[0])
                    return selectedGeometry.coordinates[0].slice(0, selectedGeometry.coordinates[0].length - 1);

                return [];
            },
            drawingPreviewLineCoordinates() {
                return this.drawingCoordinates.map((coordinate) => [coordinate[1], coordinate[0]]);
            },
            drawingPreviewVertexMarkers() {
                return this.drawingCoordinates.map((coordinate) => [coordinate[1], coordinate[0]]);
            },
            drawingPreviewPolygonCoordinates() {
                if (this.drawingCoordinates.length < 3)
                    return [];
                return [this.drawingCoordinates.map((coordinate) => [coordinate[1], coordinate[0]])];
            },
            drawingVertexIcon() {
                return Leaflet.divIcon({
                    className: 'tainacan-geojson-vertex-icon tainacan-geojson-vertex-icon--preview',
                    iconSize: [14, 14],
                    iconAnchor: [7, 7]
                });
            },
            editableVertexIcon() {
                return Leaflet.divIcon({
                    className: 'tainacan-geojson-vertex-icon tainacan-geojson-vertex-icon--editable',
                    iconSize: [14, 14],
                    iconAnchor: [7, 7]
                });
            }
        },
        created() {
            this.internalFeatureCollection = this.parseValueToFeatureCollection(this.value);
            if (this.internalFeatureCollection.features.length)
                this.selectedFeatureIndex = 0;
        },
        mounted() {
            nextTick(() => {
                const mapComponentRef = 'map--' + this.itemMetadatumIdentifier;

                if (this.$refs[mapComponentRef] && this.$refs[mapComponentRef].$el) {
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
        },
        methods: {
            parseValueToFeatureCollection(value) {
                const featureCollection = {
                    type: 'FeatureCollection',
                    features: []
                };

                const values = Array.isArray(value) ? value : [value];
                values.forEach((singleValue) => {
                    if (typeof singleValue !== 'string' || singleValue.trim() === '')
                        return;

                    try {
                        const parsed = JSON.parse(singleValue);
                        if (!parsed || !parsed.type)
                            return;

                        if (parsed.type === 'FeatureCollection' && Array.isArray(parsed.features)) {
                            parsed.features.forEach((feature) => this.tryPushFeature(featureCollection, feature));
                        } else if (parsed.type === 'Feature') {
                            this.tryPushFeature(featureCollection, parsed);
                        } else if (this.isAllowedGeometryType(parsed.type)) {
                            this.tryPushFeature(featureCollection, {
                                type: 'Feature',
                                geometry: parsed,
                                properties: {}
                            });
                        }
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
                this.fitMapToLayers();
            },
            setDrawingMode(mode) {
                this.drawingMode = mode;
                this.drawingCoordinates = [];
                this.selectedFeatureIndex = -1;
                this.selectedEditableVertexIndex = -1;
                this.isEditingVertices = false;
            },
            onMapClick(event) {
                if (!event || !event.latlng)
                    return;

                if (this.isEditingVertices && this.selectedFeatureIndex >= 0) {
                    this.addVertexOnSelectedFeature([event.latlng.lng, event.latlng.lat]);
                    return;
                }

                if (!this.canAddFeature)
                    return;

                const clickedCoordinate = [event.latlng.lng, event.latlng.lat];

                if (this.drawingMode === 'Point') {
                    this.internalFeatureCollection.features.push({
                        type: 'Feature',
                        geometry: {
                            type: 'Point',
                            coordinates: clickedCoordinate
                        },
                        properties: {}
                    });
                    this.selectedFeatureIndex = this.internalFeatureCollection.features.length - 1;
                    this.selectedEditableVertexIndex = -1;
                    this.isEditingVertices = false;
                    this.emitCurrentValue();
                    return;
                }

                this.drawingCoordinates.push(clickedCoordinate);
            },
            finishDrawing() {
                if (!this.canFinishCurrentDrawing)
                    return;
                if (!this.canAddFeature)
                    return;

                if (this.drawingMode === 'LineString') {
                    this.internalFeatureCollection.features.push({
                        type: 'Feature',
                        geometry: {
                            type: 'LineString',
                            coordinates: this.drawingCoordinates.slice(0)
                        },
                        properties: {}
                    });
                }

                if (this.drawingMode === 'Polygon') {
                    const ring = this.drawingCoordinates.slice(0);
                    ring.push(this.drawingCoordinates[0]);
                    this.internalFeatureCollection.features.push({
                        type: 'Feature',
                        geometry: {
                            type: 'Polygon',
                            coordinates: [ring]
                        },
                        properties: {}
                    });
                }

                this.selectedFeatureIndex = this.internalFeatureCollection.features.length - 1;
                this.selectedEditableVertexIndex = -1;
                this.drawingCoordinates = [];
                this.isEditingVertices = false;
                this.emitCurrentValue();
            },
            undoDrawingCoordinate() {
                if (!this.drawingCoordinates.length)
                    return;

                this.drawingCoordinates.pop();
            },
            toggleVertexEditing() {
                if (this.selectedFeatureIndex < 0)
                    return;

                this.isEditingVertices = !this.isEditingVertices;
                this.selectedEditableVertexIndex = -1;
            },
            onEditableVertexClick(event, vertexIndex) {
                if (event && event.originalEvent) {
                    event.originalEvent.preventDefault();
                    event.originalEvent.stopPropagation();
                }
                this.selectedEditableVertexIndex = vertexIndex;
            },
            removeSelectedVertex() {
                if (!this.canRemoveSelectedVertex || !this.selectedFeatureGeometry)
                    return;

                if (this.selectedFeatureGeometry.type === 'LineString') {
                    this.selectedFeatureGeometry.coordinates.splice(this.selectedEditableVertexIndex, 1);
                } else if (this.selectedFeatureGeometry.type === 'Polygon') {
                    const ring = this.selectedFeatureGeometry.coordinates[0];
                    ring.splice(this.selectedEditableVertexIndex, 1);
                    ring[ring.length - 1] = ring[0];
                }

                this.selectedEditableVertexIndex = -1;
                this.emitCurrentValue();
            },
            addVertexOnSelectedFeature(newCoordinate) {
                if (!this.selectedFeatureGeometry)
                    return;

                if (this.selectedFeatureGeometry.type === 'LineString') {
                    const coords = this.selectedFeatureGeometry.coordinates;
                    const insertionIndex = this.findNearestSegmentInsertionIndex(coords, newCoordinate, false);
                    coords.splice(insertionIndex + 1, 0, newCoordinate);
                    this.selectedEditableVertexIndex = insertionIndex + 1;
                    this.emitCurrentValue();
                    return;
                }

                if (this.selectedFeatureGeometry.type === 'Polygon' && this.selectedFeatureGeometry.coordinates && this.selectedFeatureGeometry.coordinates[0]) {
                    const ring = this.selectedFeatureGeometry.coordinates[0];
                    const editableRing = ring.slice(0, ring.length - 1);
                    const insertionIndex = this.findNearestSegmentInsertionIndex(editableRing, newCoordinate, true);
                    editableRing.splice(insertionIndex + 1, 0, newCoordinate);
                    editableRing.push(editableRing[0]);
                    this.selectedFeatureGeometry.coordinates[0] = editableRing;
                    this.selectedEditableVertexIndex = insertionIndex + 1;
                    this.emitCurrentValue();
                }
            },
            findNearestSegmentInsertionIndex(coordinates, targetCoordinate, isClosedRing = false) {
                if (!coordinates || coordinates.length < 2)
                    return coordinates.length - 1;

                let bestSegmentStartIndex = 0;
                let bestDistance = Number.POSITIVE_INFINITY;
                const segmentCount = isClosedRing ? coordinates.length : coordinates.length - 1;

                for (let index = 0; index < segmentCount; index++) {
                    const start = coordinates[index];
                    const end = isClosedRing ? coordinates[(index + 1) % coordinates.length] : coordinates[index + 1];
                    const distance = this.distanceToSegmentSquared(targetCoordinate, start, end);

                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestSegmentStartIndex = index;
                    }
                }

                return bestSegmentStartIndex;
            },
            distanceToSegmentSquared(point, segmentStart, segmentEnd) {
                const px = point[0];
                const py = point[1];
                const x1 = segmentStart[0];
                const y1 = segmentStart[1];
                const x2 = segmentEnd[0];
                const y2 = segmentEnd[1];

                const dx = x2 - x1;
                const dy = y2 - y1;

                if (dx === 0 && dy === 0)
                    return (px - x1) * (px - x1) + (py - y1) * (py - y1);

                const t = Math.max(0, Math.min(1, ((px - x1) * dx + (py - y1) * dy) / (dx * dx + dy * dy)));
                const projectedX = x1 + t * dx;
                const projectedY = y1 + t * dy;

                return (px - projectedX) * (px - projectedX) + (py - projectedY) * (py - projectedY);
            },
            removeSelectedFeature() {
                if (this.selectedFeatureIndex < 0)
                    return;
                this.internalFeatureCollection.features.splice(this.selectedFeatureIndex, 1);
                this.selectedFeatureIndex = this.internalFeatureCollection.features.length ? 0 : -1;
                this.selectedEditableVertexIndex = -1;
                this.isEditingVertices = false;
                this.emitCurrentValue();
            },
            onVertexDrag(event, vertexIndex) {
                if (!event || !event.target || !event.target._latlng || this.selectedFeatureIndex < 0)
                    return;

                const selectedFeature = this.internalFeatureCollection.features[this.selectedFeatureIndex];
                if (!selectedFeature || !selectedFeature.geometry)
                    return;

                const updatedCoordinate = [event.target._latlng.lng, event.target._latlng.lat];
                this.selectedEditableVertexIndex = vertexIndex;

                if (selectedFeature.geometry.type === 'Point') {
                    selectedFeature.geometry.coordinates = updatedCoordinate;
                } else if (selectedFeature.geometry.type === 'LineString') {
                    selectedFeature.geometry.coordinates.splice(vertexIndex, 1, updatedCoordinate);
                } else if (selectedFeature.geometry.type === 'Polygon' && selectedFeature.geometry.coordinates && selectedFeature.geometry.coordinates[0]) {
                    selectedFeature.geometry.coordinates[0].splice(vertexIndex, 1, updatedCoordinate);
                    selectedFeature.geometry.coordinates[0][selectedFeature.geometry.coordinates[0].length - 1] = selectedFeature.geometry.coordinates[0][0];
                }

                this.emitCurrentValue();
            },
            emitCurrentValue() {
                // Force GeoJSON layer recreation to reflect deep coordinate edits (e.g. dragged polygon vertices).
                this.geoJsonLayerKey++;

                if (!this.internalFeatureCollection.features.length) {
                    this.$emit('update:value', []);
                    this.fitMapToLayers();
                    return;
                }

                this.$emit('update:value', [JSON.stringify(this.internalFeatureCollection)]);
                this.fitMapToLayers();
            },
            clearAllFeatures() {
                this.internalFeatureCollection.features = [];
                this.selectedFeatureIndex = -1;
                this.selectedEditableVertexIndex = -1;
                this.isEditingVertices = false;
                this.drawingCoordinates = [];
                this.$emit('update:value', []);
                this.fitMapToLayers();
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
                if (bounds && bounds.isValid())
                    this.mapObject.flyToBounds(bounds, { animate: true, maxZoom: this.maxZoom });
            },
            handleWindowResize(mapComponentRef) {
                setTimeout(() => {
                    if (this.$refs[mapComponentRef] && this.$refs[mapComponentRef].leafletObject) {
                        this.$refs[mapComponentRef].leafletObject.invalidateSize(true);
                        this.fitMapToLayers();
                    }
                }, 300);
            }
        }
    };
</script>

<style lang="scss">
.tainacan-geojson-map-container .leaflet-container {
    border: 1px solid var(--tainacan-input-border-color);
    border-radius: var(--tainacan-input-border-radius, 3px);
    z-index: 0;

    .leaflet-marker-pane {
        filter: hue-rotate(-22deg);
    }

    .geojson-input-panel {
        padding: 0;
        margin: 0;
        display: flex;
        align-items: stretch;
        gap: 0;
        flex-wrap: nowrap;
        font-size: 1rem;

        .button:not(.is-small):not(.is-medium):not(.is-large) {
            color: var(--tainacan-secondary);
            border-radius: 0 !important;
            line-height: 1.7rem;
            background-color: var(--tainacan-input-background-color, #fff) !important;
        }

        .icon-only-button {
            width: 2.5rem;
            min-width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }
        .icon-only-button.is-active-drawing-mode {
            border-color: var(--tainacan-secondary) !important;
            background-color: var(--tainacan-secondary) !important;
            color: var(--tainacan-white) !important;
            box-shadow: 0 0 0 1px var(--tainacan-secondary) inset;
        }
        .icon-only-button.is-active-drawing-mode .button-icon {
            stroke: var(--tainacan-white);
        }

        .button-icon {
            width: 1.1rem;
            height: 1.1rem;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .screen-reader-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
    }

    .actions-panel {
        flex-wrap: wrap;
        max-width: 220px;
    }
}

.tainacan-geojson-vertex-icon {
    width: 14px;
    height: 14px;
    border-radius: 999px;
    box-sizing: border-box;
    background: #fff;
    border: 2px solid #5f3dc4;
}

.tainacan-geojson-vertex-icon--preview {
    opacity: 0.85;
}

.tainacan-geojson-vertex-icon--editable {
    box-shadow: 0 0 0 1px #fff;
}
</style>
