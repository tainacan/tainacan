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
                        @input="emitValues()"
                        placeholder="https://tile.openstreetmap.org/{z}/{x}/{y}.png" />
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
                        @input="emitValues()"
                        placeholder="© OpenStreetMap contributors" />

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
                <b-numberinput
                        name="initialZoom"
                        v-model="initialZoom"
                        @input="emitValues()"
                        :step="1"
                        :max="19"
                        :min="1" />
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
                <b-numberinput
                        name="maximumZoom"
                        v-model="maximumZoom"
                        @input="emitValues()"
                        :step="1"
                        :max="19"
                        :min="1" />
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
                this.mapProvider = this.value.map_provider || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
                this.attribution = this.value.attribution || '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors';
                this.initialZoom = Number(this.value.initial_zoom) || 5;
                this.maximumZoom = Number(this.value.maximum_zoom) || 12;
            }
        },
        methods: {
            emitValues(){
                this.$emit('input',{
                    map_provider: this.mapProvider,
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