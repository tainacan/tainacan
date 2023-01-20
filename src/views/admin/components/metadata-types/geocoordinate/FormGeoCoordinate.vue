<template>
    <section> 
        <b-field :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'map_provider') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'map_provider')"
                            :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'map_provider')" />
                </label>
                <b-input
                        name="mapProvider"
                        v-model="mapProvider"
                        @input="emitValues()" />
        </b-field>

        <b-field :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'extra_tile_layers') }}
                    <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'extra_tile_layers')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'extra_tile_layers')" />
                </label>
                <b-input
                        name="extraTileLayer"
                        v-model="extraTileLayer"
                        @input="emitValues()" />
        </b-field>

        <b-field
                :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'attribution') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'attribution')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'attribution')" />
                </label>
                <b-input
                        name="attribution"
                        v-model="attribution"
                        @input="emitValues()" />

        </b-field>

        <b-field
                :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'initial_zoom') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'initial_zoom')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'initial_zoom')" />
                </label>
                <b-input
                        name="initialZoom"
                        v-model="initialZoom"
                        @input="emitValues()"
                        type="number"
                        step="1" />
        </b-field>

        <b-field
                :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-geocoordinate', 'maximum_zoom') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                        :title="$i18n.getHelperTitle('tainacan-geocoordinate', 'maximum_zoom')"
                        :message="$i18n.getHelperMessage('tainacan-geocoordinate', 'maximum_zoom')" />
                </label>
                <b-input
                        name="maximumZoom"
                        v-model="maximumZoom"
                        @input="emitValues()"
                        type="number"
                        step="1" />
        </b-field>

    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ]
        },
        data() {
            return {
                mapProvider: String,
                extraTileLayer: [],
                attribution: String,
                initialZoom: Number,
                maximumZoom: Number,
            }
        },
        created() {
            if (this.value) {
                this.mapProvider = this.value.map_provider || 'http://?';
                this.extraTileLayer = this.value.extra_tile_layers || [];
                this.attribution = this.value.attribution || '';
                this.initialZoom = this.value.initial_zoom || 5;
                this.maximumZoom = this.value.maximum_zoom || 12;
            }
        },
        methods: {
            emitValues(){
                this.$emit('input',{
                    map_provider: this.mapProvider,
                    extra_tile_layers: this.extraTileLayer,
                    attribution: this.attribution,
                    initial_zoom: this.initialZoom,
                    maximum_zoom: this.maximumZoom,
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