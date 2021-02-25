<template>
<div class="tainacan-form metadatum-details">
    <div 
            v-if="metadatum.description"
            class="field">
        <div class="label">{{ $i18n.getHelperTitle('metadata', 'description') }}</div>
        <div class="value">{{ metadatum.description }}</div>
    </div>
    <div 
            v-if="metadatum.semantic_uri"
            class="field">
        <div class="label">{{ $i18n.getHelperTitle('metadata', 'semantic_uri') }}</div>
        <div class="value">{{ metadatum.semantic_uri }}</div>
    </div>
        <div class="field">
        <div class="label">{{ $i18n.getHelperTitle('metadata', 'status') }}</div>
        <div class="value">
            <template v-if="metadatum.status === 'publish'">{{ $i18n.get('publish_visibility') }}</template>
            <template v-if="metadatum.status === 'private'">{{ $i18n.get('private_visibility') }}</template>
        </div>
    </div>
        <div class="field">
        <div class="label">{{ $i18n.get('label_display') }}</div>
        <div class="value">
            <template v-if="metadatum.display === 'yes'">{{ $i18n.get('label_display_default') }}</template>
            <template v-if="metadatum.display === 'no'">{{ $i18n.get('label_not_display') }}</template>
            <template v-if="metadatum.display === 'never'">{{ $i18n.get('label_display_never') }}</template>
        </div>
    </div>
    <div class="field">
        <div class="label">{{ $i18n.get('label_insert_options') }}</div>
        <div class="value">{{ insertOptions }}</div>
    </div>
    <div
            v-if="metadatum.metadata_type_object && metadatum.metadata_type_object.options_as_html"
            v-html="metadatum.metadata_type_object.options_as_html" />
</div>
</template>

<script>
export default {
    props: {
        metadatum: Object
    },
    computed: {
        insertOptions() {
            const enableInsertOptions = [];

            if (this.metadatum.required)
                enableInsertOptions.push(this.$i18n.getHelperTitle('metadata', 'required'));
            if (this.metadatum.multiple)
                enableInsertOptions.push(this.$i18n.getHelperTitle('metadata', 'multiple'));
            if (this.metadatum.collection_key)
                enableInsertOptions.push(this.$i18n.getHelperTitle('metadata', 'collection_key'));

            return enableInsertOptions.join(', ');
        }
    }
}
</script>

<style lang="scss" scoped>
.metadatum-details {
    padding: 0.75em 1.5em 0.75em 3.5em;
    -moz-column-count: 2;
    -moz-column-gap: 0;
    -moz-column-rule: none;
    -webkit-column-count: 2;
    -webkit-column-gap: 0;
    -webkit-column-rule: none;
    column-count: 2;
    column-gap: 4em;
    column-rule: none;

    &>.field, &>section {
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        break-inside: avoid;
        margin-bottom: 1em;
    }
    .field > .field:not(:last-child) {
        margin-bottom: 0em;
    }
    .field .value {
        font-size: 0.9em;
    }
}
</style>