<template>
    <form class="tainacan-modal-content tainacan-form">
        <div class="tainacan-modal-title">
            <h2 id="item-document-url-modal-title">
                {{ $i18n.get('instruction_insert_url') }}
            </h2>
            <hr>
        </div>
        <b-input 
                ref="item-document-url-input"
                v-model="localUrlLink"
                aria-labelledby="item-document-url-modal-title" />
        <br>
        <b-field
                :addons="false"
                :label="$i18n.get('label_document_option_forced_iframe')">
                &nbsp;
            <b-switch
                    v-model="localUrlForcedIframe" 
                    size="is-small" />
            <help-button
                    :title="$i18n.get('label_document_option_forced_iframe')"
                    :message="$i18n.get('info_document_option_forced_iframe')" />
        </b-field>
        <b-field 
                v-if="localUrlForcedIframe"
                grouped
                group-multiline>
            <b-field :label="$i18n.get('label_document_option_iframe_width')">
                <b-numberinput
                        v-model="localUrlIframeWidth"
                        :aria-minus-label="$i18n.get('label_decrease')"
                        :aria-plus-label="$i18n.get('label_increase')" 
                        min="1"
                        step="1" />
            </b-field>
            <b-field :label="$i18n.get('label_document_option_iframe_height')">
                <b-numberinput
                        v-model="localUrlIframeHeight"
                        :aria-minus-label="$i18n.get('label_decrease')"
                        :aria-plus-label="$i18n.get('label_increase')" 
                        min="1"
                        step="1" />
            </b-field>
        </b-field>
        <br v-if="localUrlForcedIframe">
        <p 
                v-if="localUrlForcedIframe"
                class="help">
            {{ $i18n.get('info_iframe_dimensions') }}
        </p>
        <br v-if="localUrlForcedIframe">
        <b-field
                v-if="localUrlForcedIframe"
                :addons="false"
                :label="$i18n.get('label_document_option_is_image')">
                &nbsp;
            <b-switch
                    v-model="localUrlIsImage" 
                    size="is-small" />
            <help-button
                    :title="$i18n.get('label_document_option_is_image')"
                    :message="$i18n.get('info_document_option_is_image')" />
        </b-field>

        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-cancel-url-link-selection"
                        class="button is-outlined"
                        type="button"
                        @click="$emit('close')">
                    {{ $i18n.get('cancel') }}</button>
            </div>
            <div class="control">
                <button
                        id="button-submit-url-link-selection"
                        class="button is-success"
                        @click.prevent="confirmURLSelection();$emit('close');">
                    {{ $i18n.get('save') }}</button>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    props: {
        urlLink: '',
        urlForcedIframe: false,
        urlIframeWidth: 600,
        urlIframeHeight: 450,
        urlIsImage: false,
    },
    emits: [
        'confirmURLSelection',
        'close'
    ],
    data(){
        return {
            localUrlLink: '',
            localUrlForcedIframe: false,
            localUrlIframeWidth: 600,
            localUrlIframeHeight: 450,
            localUrlIsImage: false,
        }
    },
    mounted() {
        this.localUrlLink = this.urlLink;
        this.localUrlForcedIframe = this.urlForcedIframe;
        this.localUrlIframeWidth = this.urlIframeWidth;
        this.localUrlIframeHeight = this.urlIframeHeight;
        this.localUrlIsImage = this.urlIsImage;
        if (
            this.$refs && 
            this.$refs['item-document-url-input'] &&
            this.$refs['item-document-url-input']['$el'] &&
            this.$refs['item-document-url-input']['$el'].children &&
            this.$refs['item-document-url-input']['$el'].children[0]
        ) {
            this.$refs['item-document-url-input']['$el'].children[0].focus();
            this.$refs['item-document-url-input']['$el'].children[0].click();
        }
    },
    methods: {
        confirmURLSelection() {
            this.$emit('confirmURLSelection',
                { 
                    urlLink: this.localUrlLink,
                    urlForcedIframe: this.localUrlForcedIframe,
                    urlIframeWidth: this.localUrlIframeWidth,
                    urlIframeHeight: this.localUrlIframeHeight,
                    urlIsImage: this.localUrlIsImage
                });
        }
    }
}
</script>