<template>
    <section> 
        <b-field
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-text', 'display_suggestions')">
                &nbsp;
            <b-switch
                    size="is-small" 
                    :value="displaySuggestions"
                    @input="onUpdateDisplaySuggestions"
                    :true-value="'yes'"
                    :false-value="'no'" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-text', 'display_suggestions')"
                    :message="$i18n.getHelperMessage('tainacan-text', 'display_suggestions')"/>
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
                displaySuggestions: String
            }
        },
        created() {
            this.displaySuggestions = this.value && this.value.display_suggestions ? this.value.display_suggestions : 'no';
        },
        methods: {
            onUpdateDisplaySuggestions(value) {
                this.displaySuggestions = value;
                this.$emit('input', { display_suggestions: value });
            }
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