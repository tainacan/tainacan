<template>
    <section>
        <b-field
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-url', 'link-as-button')">
            &nbsp;
            <b-switch
                    v-model="linkAsButton"
                    true-value="yes"
                    false-value="no"
                    size="is-small"
                    @update:model-value="onUpdateLinkAsButton" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-url', 'link-as-button')"
                    :message="$i18n.getHelperMessage('tainacan-url', 'link-as-button')" />
        </b-field>

        <b-field
                v-if="linkAsButton == 'no'"
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-url', 'force-iframe')">
            &nbsp;
            <b-switch
                    v-model="forceIframe"    
                    size="is-small"
                    true-value="yes"
                    false-value="no"
                    @update:model-value="onUpdateForceIframe" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-url', 'force-iframe')"
                    :message="$i18n.getHelperMessage('tainacan-url', 'force-iframe')" />
        </b-field>

        <transition name="filter-item">
            <div 
                    v-if="forceIframe == 'yes'"
                    style="break-inside: avoid;"> 
                <b-field
                        :addons="false">
                    <label class="label is-inline-block">
                        {{ $i18n.getHelperTitle('tainacan-url', 'iframe-min-height') }}
                        <help-button
                                :title="$i18n.getHelperTitle('tainacan-url', 'iframe-min-height')"
                                :message="$i18n.getHelperMessage('tainacan-url', 'iframe-min-height')" />
                    </label>
                    <b-numberinput
                            :model-value="iframeMinimumHeight === '' ? 0 : iframeMinimumHeight"
                            size="is-small"
                            step="1"
                            @update:model-value="onUpdateIframeMinimumHeight" />
                </b-field>

                <b-field 
                        v-if="forceIframe == 'yes'"
                        :addons="false"
                        :label="$i18n.getHelperTitle('tainacan-url', 'iframe-allowfullscreen')">
                    &nbsp;
                    <b-switch
                            v-model="iframeAllowfullscreen"
                            size="is-small"
                            true-value="yes"
                            false-value="no"
                            @update:model-value="onUpdateIframeAllowfullscreen" />
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-url', 'iframe-allowfullscreen')"
                            :message="$i18n.getHelperMessage('tainacan-url', 'iframe-allowfullscreen')" />
                </b-field>

                <b-field
                        :addons="false"
                        :label="$i18n.getHelperTitle('tainacan-url', 'is-image')">
                    &nbsp;
                    <b-switch
                            v-model="isImage"      
                            size="is-small" 
                            true-value="yes"
                            false-value="no"
                            @update:model-value="onUpdateIsImage" />
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-url', 'is-image')"
                            :message="$i18n.getHelperMessage('tainacan-url', 'is-image')" />
                </b-field>
            </div>
        </transition>

    </section>
</template>

<script>
export default {
    name: "TainacanMetadataFormTypeURL",
    props: {
        value: [String, Object, Array],
    },
    emits: [ 'update:value' ],
    data() {
        return {
            linkAsButton: String,
            forceIframe: String,
            iframeMinimumHeight: [Number, String],
            iframeAllowfullscreen: String,
            isImage: String
        };
    },
    created() {
        this.linkAsButton = this.value && this.value['link-as-button'] ? this.value['link-as-button'] : 'no';
        this.forceIframe = this.value && this.value['force-iframe'] ? this.value['force-iframe'] : 'no';
        this.iframeMinimumHeight = this.value && this.value['iframe-min-height'] ? this.value['iframe-min-height'] : '';
        this.iframeAllowfullscreen = this.value && this.value['iframe-allowfullscreen'] ? this.value['iframe-allowfullscreen'] : 'no';
        this.isImage = this.value && this.value['is-image'] ? this.value['is-image'] : 'no';
    },
    methods: {
        onUpdateLinkAsButton(value) {
            this.$emit('update:value', {
                'link-as-button': value,
                'force-iframe': this.forceIframe,
                'iframe-min-height': this.iframeMinimumHeight,
                'iframe-allowfullscreen': this.iframeAllowfullscreen,
                'is-image': this.isImage,
            });
        },
        onUpdateForceIframe(value) {
            this.$emit('update:value', {
                'link-as-button': this.linkAsButton,
                'force-iframe': value,
                'iframe-min-height': this.iframeMinimumHeight,
                'iframe-allowfullscreen': this.iframeAllowfullscreen,
                'is-image': this.isImage,
            });
        },
        onUpdateIframeMinimumHeight(value) {
            this.$emit('update:value', {
                'link-as-button': this.linkAsButton,
                'force-iframe': this.forceIframe,
                'iframe-min-height': value,
                'iframe-allowfullscreen': this.iframeAllowfullscreen,
                'is-image': this.isImage,
            });
        },
        onUpdateIframeAllowfullscreen(value) {
            this.$emit('update:value', {
                'link-as-button': this.linkAsButton,
                'force-iframe': this.forceIframe,
                'iframe-min-height': this.iframeMinimumHeight,
                'iframe-allowfullscreen': value,
                'is-image': this.isImage,
            });
        },
        onUpdateIsImage(value) {
            this.$emit('update:value', {
                'link-as-button': this.linkAsButton,
                'force-iframe': this.forceIframe,
                'iframe-min-height': this.iframeMinimumHeight,
                'iframe-allowfullscreen': this.iframeAllowfullscreen,
                'is-image': value,
            });
        },
    },
};
</script>
