<template>
    <div v-if="itemMetadatum">
        <b-input 
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :disabled="disabled"
                :model-value="value"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : '[link](https://url.com)'"
                @update:model-value="($event) => onInput($event)"
                @blur="onBlur" />
        <div
                v-if="itemMetadatum.item.id"
                class="add-new-term">
            <a
                    v-if="value"
                    class="add-link"
                    @click="previewHtml">
                <span class="icon">
                    <i class="tainacan-icon has-text-secondary tainacan-icon-see" />
                </span>
                <span style="font-size: 0.75em">&nbsp;{{ $i18n.get('label_preview', 'tainacan') }}</span>
            </a>
        </div>
        <transition name="filter-item">
            <div
                    v-if="isPreviewingHtml"
                    v-html="singleHTMLPreview" />
        </transition>
    </div>
</template>

<script>

export default {
    name: 'TainacanMetadataTypeURL',
    props: {
        itemMetadatum: Object,
        value: [String, Number, Array],
        disabled: false
    },
    emits: [ 'update:value', 'blur' ],
    data() {
        return {
            isPreviewingHtml: false,
            singleHTMLPreview: ''
        }
    },
    methods: {
        onInput(value) {
            this.isPreviewingHtml = false;
            this.singleHTMLPreview = '';
            this.$emit('update:value', value);
        },
        onBlur() {
            this.$emit('blur');
        },
        createElementFromHTML(htmlString) {
            let div = document.createElement('div');
            div.innerHTML = htmlString.trim();
            return div;
        },
        previewHtml() {
            // If we are going to display preview, renders it
            if (!this.isPreviewingHtml) {

                // Multivalued metadata need to be split as the values_as_html shows every value
                if (this.itemMetadatum.metadatum.multiple == 'yes') {

                    const valuesAsHtml = this.createElementFromHTML(this.itemMetadatum.value_as_html);
                    const valuesAsArray = Object.values(valuesAsHtml.children).filter((aValue) => aValue.outerHTML != '<span class="multivalue-separator"> | </span>');

                    const singleValueIndex = this.itemMetadatum.value.findIndex((aValue) => aValue == this.value);

                    if (singleValueIndex >= 0)
                    this.singleHTMLPreview = valuesAsArray[singleValueIndex].outerHTML;

                } else {
                    this.singleHTMLPreview = this.itemMetadatum.value_as_html;
                }

            }

            // Toggle Preview view
            this.isPreviewingHtml = !this.isPreviewingHtml;
        }
    },
};
</script>