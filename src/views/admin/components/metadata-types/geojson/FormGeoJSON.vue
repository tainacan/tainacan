<template>
    <section>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geojson', 'map_provider') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geojson', 'map_provider')"
                        :message="$i18n.getHelperMessage('tainacan-geojson', 'map_provider')" />
            </label>
            <b-input
                    v-model="mapProvider"
                    name="mapProvider"
                    placeholder="https://tile.openstreetmap.org/{z}/{x}/{y}.png"
                    @update:model-value="emitValues()" />
        </b-field>

        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geojson', 'attribution') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geojson', 'attribution')"
                        :message="$i18n.getHelperMessage('tainacan-geojson', 'attribution')" />
            </label>
            <b-input
                    v-model="attribution"
                    name="attribution"
                    placeholder="© OpenStreetMap contributors"
                    @update:model-value="emitValues()" />
        </b-field>

        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geojson', 'initial_zoom') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geojson', 'initial_zoom')"
                        :message="$i18n.getHelperMessage('tainacan-geojson', 'initial_zoom')" />
            </label>
            <b-numberinput
                    v-model="initialZoom"
                    name="initialZoom"
                    :step="1"
                    :max="19"
                    :min="1"
                    controls-position="compact"
                    controls-alignment="right"
                    expanded
                    @update:model-value="emitValues()" />
        </b-field>

        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geojson', 'maximum_zoom') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geojson', 'maximum_zoom')"
                        :message="$i18n.getHelperMessage('tainacan-geojson', 'maximum_zoom')" />
            </label>
            <b-numberinput
                    v-model="maximumZoom"
                    name="maximumZoom"
                    :step="1"
                    :max="19"
                    :min="1"
                    controls-position="compact"
                    controls-alignment="right"
                    expanded
                    @update:model-value="emitValues()" />
        </b-field>

        <b-field
                :addons="false"
                :listen="setError"
                :type="initialPositionType"
                :message="initialPositionMessage">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geojson', 'initial_position') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geojson', 'initial_position')"
                        :message="$i18n.getHelperMessage('tainacan-geojson', 'initial_position')" />
            </label>
            <b-field grouped>
                <b-input
                        v-model="initialLatitude"
                        :placeholder="-14.408656999999"
                        name="initialLatitude"
                        expanded
                        type="number"
                        :min="-90"
                        :max="90"
                        :step="0.000000000001"
                        @update:model-value="emitValues()"
                        @focus="clear()" />
                <b-input
                        v-model="initialLongitude"
                        :placeholder="-51.316689999999"
                        name="initialLongitude"
                        expanded
                        type="number"
                        :min="-180"
                        :max="180"
                        :step="0.000000000001"
                        @update:model-value="emitValues()"
                        @focus="clear()" />
            </b-field>
        </b-field>

        <b-field
                :addons="false"
                :message="errors && errors.allowed_geometry_types"
                :type="errors && errors.allowed_geometry_types ? 'is-danger' : ''">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geojson', 'allowed_geometry_types') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geojson', 'allowed_geometry_types')"
                        :message="$i18n.getHelperMessage('tainacan-geojson', 'allowed_geometry_types')" />
            </label>
            <div class="geojson-form-allowed-geometry-types">
                <b-checkbox
                        v-model="allowPoint"
                        @update:model-value="emitValues()">
                    <span>
                        {{ $i18n.getHelperTitle('tainacan-geojson', 'allow_point') }}
                        <help-button
                                class="is-inline is-small"
                                :title="$i18n.getHelperTitle('tainacan-geojson', 'allow_point')"
                                :message="$i18n.getHelperMessage('tainacan-geojson', 'allow_point')" />
                    </span>
                </b-checkbox>
                <b-checkbox
                        v-model="allowLineString"
                        @update:model-value="emitValues()">
                    <span>
                        {{ $i18n.getHelperTitle('tainacan-geojson', 'allow_linestring') }}
                        <help-button
                                class="is-inline is-small"
                                :title="$i18n.getHelperTitle('tainacan-geojson', 'allow_linestring')"
                                :message="$i18n.getHelperMessage('tainacan-geojson', 'allow_linestring')" />
                    </span>
                </b-checkbox>
                <b-checkbox
                        v-model="allowPolygon"
                        @update:model-value="emitValues()">
                    <span>
                        {{ $i18n.getHelperTitle('tainacan-geojson', 'allow_polygon') }}
                        <help-button
                                class="is-inline is-small"
                                :title="$i18n.getHelperTitle('tainacan-geojson', 'allow_polygon')"
                                :message="$i18n.getHelperMessage('tainacan-geojson', 'allow_polygon')" />
                    </span>
                </b-checkbox>
            </div>
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [String, Object, Array],
            errors: [String, Object, Array]
        },
        emits: ['update:value'],
        data() {
            return {
                mapProvider: String,
                attribution: String,
                initialZoom: Number,
                maximumZoom: Number,
                initialLatitude: Number,
                initialLongitude: Number,
                allowPoint: true,
                allowLineString: true,
                allowPolygon: true
            };
        },
        computed: {
            setError() {
                if (this.errors && this.errors.initial_position !== '')
                    this.setErrorsAttributes('is-danger', this.errors.initial_position);
                else
                    this.setErrorsAttributes('', '');
                return true;
            }
        },
        created() {
            if (this.value) {
                this.mapProvider = this.value.map_provider || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
                this.attribution = this.value.attribution || '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors';
                this.initialZoom = Number(this.value.initial_zoom) || 5;
                this.maximumZoom = Number(this.value.maximum_zoom) || 12;
                this.initialLatitude = Number(this.value.initial_latitude) || -14.4086569;
                this.initialLongitude = Number(this.value.initial_longitude) || -51.31668;
                this.allowPoint = this.value.allow_point !== false;
                this.allowLineString = this.value.allow_linestring !== false;
                this.allowPolygon = this.value.allow_polygon !== false;
            }
        },
        methods: {
            setErrorsAttributes(type, message) {
                this.initialPositionType = type;
                this.initialPositionMessage = message;
            },
            clear() {
                this.initialPositionType = '';
                this.initialPositionMessage = '';
            },
            emitValues() {
                this.$emit('update:value', {
                    map_provider: this.mapProvider,
                    attribution: this.attribution,
                    initial_zoom: this.initialZoom,
                    maximum_zoom: this.maximumZoom,
                    initial_latitude: this.initialLatitude,
                    initial_longitude: this.initialLongitude,
                    allow_point: this.allowPoint,
                    allow_linestring: this.allowLineString,
                    allow_polygon: this.allowPolygon
                });
            }
        }
    };
</script>

<style scoped>
section {
    margin-bottom: 10px;
}
.tainacan-help-tooltip-trigger {
    font-size: 1em;
}
.geojson-form-allowed-geometry-types {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem 1rem;
}
</style>
