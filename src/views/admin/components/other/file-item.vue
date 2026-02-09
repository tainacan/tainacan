<template>
    <div>
        <figure 
                class="file-item"
                :class="{'shows-modal-on-click' : modalOnClick}"
                :role="modalOnClick ? 'button' : undefined"
                :tabindex="modalOnClick ? 0 : undefined"
                @click="modalOnClick ? openPreviewModal() : null"
                @keydown.enter.prevent="modalOnClick ? openPreviewModal() : null"
                @keydown.space.prevent="modalOnClick ? openPreviewModal() : null">
            <figcaption 
                    v-if="showName && file.title != undefined"
                    v-tooltip="{
                        content: file.title,
                        popperClass: [ 'tainacan-tooltip', 'tooltip']
                    }"
                    :style="{ 'max-width': size != undefined ? size + 'px' : '94px' }">
                {{ file.title }}
            </figcaption>
            <div 
                    :style="{ 'width': size != undefined ? size + 'px' : '94px', 'height': size != undefined ? size + 'px' : '94px' }"
                    class="image-wrapper">
                <div
                        v-if="(file.thumbnails['tainacan-medium'] && file.thumbnails['tainacan-medium'][0]) || (file.thumbnails['medium'] && file.thumbnails['medium'][0])" 
                        class="image"
                        :style="{ 'background-image': 'url(' + (file.thumbnails['tainacan-medium'] ? file.thumbnails['tainacan-medium'][0] : file.thumbnails['medium'][0]) + ')' }" />
                <div
                        v-else
                        :style="{ 'background-color': 'var(--tainacan-gray1)' }" 
                        class="file-placeholder">
                    <span class="icon is-large">
                        <i 
                                :class="'tainacan-icon-' + getIconForMimeType(file.mime_type)"
                                class="has-text-dark tainacan-icon tainacan-icon-36px" />
                    </span>
                </div>
            </div>
        </figure>
    </div>
</template>

<script>
import FilePreviewModal from '../modals/file-preview-modal.vue';

export default {
    name: 'FileItem',
    props: {
        file: Object,
        size: 94,
        showName: false,
        isSelected: false,
        modalOnClick: true
    },
    methods: {
        getIconForMimeType(mimeType) {
            
            if (mimeType) {
                const type = mimeType.split('/');
                if (type[0] == 'application' && type[1] != undefined){
                    switch (type[1]) {
                        case 'pdf':
                            return 'pdf';
                        default:
                            return 'attachments';
                    }
                } else {
                    switch (type[0]) {
                        case 'video':
                            return 'video';
                        case 'audio':
                            return 'audio';
                        case 'text':
                            return 'text';
                        default:
                            return 'attachments';
                    }
                }
            } else {
                return 'attatchments'
            }
            
        },
        openPreviewModal() {
            const modalTrigger = this.$modalFocusA11y.captureTrigger();
            this.$buefy.modal.open({
                component: FilePreviewModal,
                props: {
                    file: this.file
                },
                ariaModal: true,
                width: 1024,
                scroll: 'keep',
                trapFocus: true,
                ariaRole: 'dialog',
                customClass: 'tainacan-modal',
                canCancel: ['escape', 'outside'],
                events: {
                    beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                }
            });
        }
    }
}
</script>

<style lang="scss">

    .file-item {
        display: inline-block;
        outline: none;

        &:hover,
        &.shows-modal-on-click:focus,
        &.shows-modal-on-click:focus-within,
        &.shows-modal-on-click:focus-visible {
            cursor: pointer;
            .image, .file-placeholder {
                transform: scale(1.05);
            }
        }
        &.shows-modal-on-click:focus-visible {
            outline-width: 2px;
            outline-offset: -1px;
            outline-color: var(--tainacan-secondary);
            outline-color: color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
            outline-style: solid;
            box-shadow: none;
        }
        &:hover,
        &:focus,
        &:focus-within,
        &:focus-visible {
            figcaption {
                background-color: var(--tainacan-gray1);
            }
        }
        .image-wrapper {
            overflow: hidden;
            position: relative;
            display: inline-block;

            .image {
                height: 100%;
                width: 100%;
                border-radius: var(--tainacan-item-border-radius, 3px);
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                -webkit-transition: all .3s;
                -moz-transition: all .3s;
                -o-transition: all .3s;
                transition: all .3s;
            }

            .file-placeholder {
                height: 100%;
                width: 100%;
                border-radius: var(--tainacan-item-border-radius, 3px);
                text-align: center;
                display: flex;
                justify-content: center;
                align-items: center;
                -webkit-transition: all .3s;
                -moz-transition: all .3s;
                -o-transition: all .3s;
                transition: all .3s;
            }
        }

        figcaption {
            background-color: var(--tainacan-white);
            padding: 8px 15px;
            font-size: 0.75em;
            width: 100%;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            text-align: center;
            border-top-left-radius: var(--tainacan-item-border-radius, 3px);
            border-top-right-radius: var(--tainacan-item-border-radius, 3px);
        }
    }
    .tainacan-modal-title {
        text-align: start;
    }
    .rendered-content {
        justify-content: center !important;
        flex-direction: column;
        align-items: center;

        p.attachment>a {
            display: block;
        }
    }
    
</style>

