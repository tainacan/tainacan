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
        <div class="label">{{ $i18n.get('label_display') }}</div>
        <div class="value">
            <template v-if="metadatum.display === 'yes'">{{ $i18n.get('label_display_default') }}</template>
            <template v-if="metadatum.display === 'no'">{{ $i18n.get('label_not_display') }}</template>
            <template v-if="metadatum.display === 'never'">{{ $i18n.get('label_display_never') }}</template>
        </div>
    </div>
    <div 
            v-if="insertOptions != ''"
            class="field">
        <div class="label">{{ $i18n.get('label_insert_options') }}</div>
        <div class="value">
            {{ insertOptions }}
            <span 
                    v-if="metadatum.multiple === 'yes' &&
                        metadatum.cardinality != undefined &&
                        metadatum.cardinality != 0 &&
                        metadatum.cardinality != 1 &&
                        metadatum.cardinality != ''">
                &nbsp;({{ $i18n.getWithVariables('label_maximum_of_%s_values', [ metadatum.cardinality ]) }})
            </span>
        </div>
    </div>
    <div
            v-if="metadatum.options_as_html"
            v-html="metadatum.options_as_html" />
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

            if (this.metadatum.required === 'yes')
                enableInsertOptions.push(this.$i18n.getHelperTitle('metadata', 'required'));
            if (this.metadatum.multiple === 'yes')
                enableInsertOptions.push(this.$i18n.getHelperTitle('metadata', 'multiple'));
            if (this.metadatum.collection_key === 'yes')
                enableInsertOptions.push(this.$i18n.getHelperTitle('metadata', 'collection_key'));

            return enableInsertOptions.join(', ');
        }
    }
}
</script>

<style lang="scss" scoped>
.metadatum-details {
    padding: 0.75em 1.5em 0.75em 3.5em;
    -moz-column-count: 3;
    -moz-column-gap: 0;
    -moz-column-rule: none;
    -webkit-column-count: 3;
    -webkit-column-gap: 0;
    -webkit-column-rule: none;
    column-count: 3;
    column-gap: 4em;
    column-rule: none;

    @media screen and (max-width: 1024px) {
        -moz-column-count: 2;
        -webkit-column-count: 2;
        column-count: 2;
    }

    @media screen and (max-width: 768px) {
        -moz-column-count: 1;
        -webkit-column-count: 1;
        column-count: 1;
    }

    /deep/ .field {
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        break-inside: avoid;
        margin-bottom: 1em;

        & > .field:not(:last-child) {
            margin-bottom: 0em;
        }
        .label {
            white-space: normal;
        }
        .value {
            font-size: 0.9em;
        }
        &:only-child {
            column-span: all;
        }
    }
}
</style>