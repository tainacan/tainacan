<template>
    <div>
        <figure 
                class="file-item"
                :class="{'shows-modal-on-click' : modalOnClick}"
                @click="modalOnClick? openPreviewModal() : null">
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
                        v-if="file.media_type == 'image'" 
                        class="image"
                        :style="{ 'background-image': 'url(' + (file.thumbnails['tainacan-medium'] ? file.thumbnails['tainacan-medium'][0] : file.thumbnails['medium'][0]) + ')' }"/>
                <div
                        :style="{ 'background-color': 'var(--tainacan-gray1)' }"
                        v-else 
                        class="file-placeholder">
                    <span class="icon is-large">
                        <i 
                                :class="'tainacan-icon-' + getIconForMimeType(file.mime_type)"
                                class="has-text-gray tainacan-icon tainacan-icon-36px"/>
                    </span>
                </div>
            </div>
        </figure>
    </div>
</template>

<script>
import FilePreview from './file-preview.vue';

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
            this.$buefy.modal.open({
                parent: this,
                component: FilePreview,
                props: {
                    file: this.file
                },
                ariaModal: true,
                width: 1024,
                scroll: 'keep',
                trapFocus: true,
                ariaRole: 'dialog',
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        }
    }
}
</script>

<style lang="scss">

    .file-item {
        display: inline-block;

        &.shows-modal-on-click:hover {
            cursor: pointer;
            .image, .file-placeholder {
                transform: scale(1.05);
            }
        }
        &:hover {
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
        }
    }
    .tainacan-modal-title {
        text-align: left;
    }
    .rendered-content {
        justify-content: center !important;
        flex-direction: column;
        align-items: center;
    }
    
</style>

