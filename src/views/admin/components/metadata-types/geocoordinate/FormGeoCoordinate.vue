<template>
    <section> 
        <b-field :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-text', 'mapProvider') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                            :title="'Titulo do helper'"
                            :message="'message do helper'" />
                </label>
                <b-input
                        name="mapProvider"
                        v-model="mapProvider"
                        @input="emitValues()" />
        </b-field>

        <b-field :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-text', 'extraTileLayer') }}
                    <help-button
                        :title="'Titulo do helper'"
                        :message="'message do helper'" />
                </label>
                <b-input
                        name="extraTileLayer"
                        v-model="extraTileLayer"
                        @input="emitValues()" />
        </b-field>

        <b-field
                :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-text', 'atrribution') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                        :title="'Titulo do helper'"
                        :message="'message do helper'" />
                </label>
                <b-input
                        name="atrribution"
                        v-model="atrribution"
                        @input="emitValues()" />

        </b-field>

        <b-field
                :addons="false" >
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-text', 'initialZoom') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                        :title="'Titulo do helper'"
                        :message="'message do helper'" />
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
                    {{ $i18n.getHelperTitle('tainacan-text', 'maximumZoom') }}
                    <span>&nbsp;*&nbsp;</span>
                    <help-button
                        :title="'Titulo do helper'"
                        :message="'message do helper'" />
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
                atrribution: String,
                initialZoom: Number,
                maximumZoom: Number,
            }
        },
        created() {
            if (this.value) {
                this.mapProvider = this.value.map_provider || 'http://?';
                this.extraTileLayer = this.value.extra_tile_layer || [];
                this.atrribution = this.value.atrribution || '';
                this.initialZoom = this.value.initial_zoom || 5;
                this.maximumZoom = this.value.maximum_zoom || 12;
            }
        },
        methods: {
            emitValues(){
                this.$emit('input',{
                    map_provider: this.mapProvider,
                    extra_tile_layer: this.extraTileLayer,
                    atrribution: this.atrribution,
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