<template>
    <section> 
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'map_provider') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'map_provider')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'map_provider')" />
            </label>
            <b-input
                    v-model="mapProvider"
                    name="mapProvider"
                    placeholder="https://tile.openstreetmap.org/{z}/{x}/{y}.png"
                    @update:model-value="emitValues()" />
        </b-field>

        <b-field
                :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'attribution') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'attribution')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'attribution')" />
            </label>
            <b-input
                    v-model="attribution"
                    name="attribution"
                    placeholder="© OpenStreetMap contributors"
                    @update:model-value="emitValues()" />

        </b-field>

        <b-field
                :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'initial_zoom') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'initial_zoom')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'initial_zoom')" />
            </label>
            <b-numberinput
                    v-model="initialZoom"
                    name="initialZoom"
                    :step="1"
                    :max="19"
                    :min="1"
                    @update:model-value="emitValues()" />
        </b-field>

        <b-field
                :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'maximum_zoom') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'maximum_zoom')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'maximum_zoom')" />
            </label>
            <b-numberinput
                    v-model="maximumZoom"
                    name="maximumZoom"
                    :step="1"
                    :max="19"
                    :min="1"
                    @update:model-value="emitValues()" />
        </b-field>

        <b-field
                :addons="false"
                :listen="setError"
                :type="initialPositionType"
                :message="initialPositionMessage">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'initial_position') }}
                <span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'initial_position')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'initial_position')" />
            </label>
            <b-field grouped>
                <b-input
                        v-model="initialLatitude"
                        :placeholder="-14.408656999999"
                        name="initialLatitude"
                        expanded
                        type="text"
                        :step="0.000000000001"
                        @update:model-value="emitValues()"
                        @focus="clear()" />
                <b-input
                        v-model="initialLongitude"
                        :placeholder="-51.316689999999"
                        name="initialLongitude"
                        expanded
                        type="text"
                        :step="0.000000000001"
                        @update:model-value="emitValues()"
                        @focus="clear()" />
            </b-field>
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ],
            errors: [ String, Object, Array ]
        },
        emits: ['update:value'],
        data() {
            return {
                mapProvider: String,
                extraTileLayer: [],
                attribution: String,
                initialZoom: Number,
                maximumZoom: Number,
                initialLatitude: Number,
                initialLongitude: Number,
            }
        },
        computed: {
            setError(){
                if ( this.errors && this.errors.initial_position !== '' )
                    this.setErrorsAttributes( 'is-danger', this.errors.initial_position );
                else
                    this.setErrorsAttributes( '', '' );
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
            }
        },
        methods: {
            setErrorsAttributes( type, message ){
                this.initialPositionType = type;
                this.initialPositionMessage = message;
            },
            clear(){
                this.initialPositionType = '';
                this.initialPositionMessage = '';
            },
            emitValues(){
                this.$emit('update:value',{
                    map_provider: this.mapProvider,
                    attribution: this.attribution,
                    initial_zoom: this.initialZoom,
                    maximum_zoom: this.maximumZoom,
                    initial_latitude: this.initialLatitude,
                    initial_longitude: this.initialLongitude,
                })
            },
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
    .tainacan-help-tooltip-trigger {
        font-size: 1em;
    }
</style>