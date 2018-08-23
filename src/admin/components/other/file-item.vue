<template>
    <div>
        <figure 
                class="file-item"
                @click="isPreviewModalActive = true">
            <figcaption 
                    :style="{ 'max-width': size != undefined ? size + 'px' : '112px' }"
                    v-if="showName">{{ file.title.rendered }}</figcaption>
            <div 
                    :class="{ 'rounded': showName }"
                    :style="{ 'width': size != undefined ? size + 'px' : '112px', 'height': size != undefined ? size + 'px' : '112px' }"
                    class="image-wrapper">
                <div
                        v-if="file.media_type == 'image'" 
                        class="image"
                        :style="{ 'background-image': 'url(' + file.guid.rendered + ')' }"/>
                 <div
                        :style="{ 'background-color': '#dbdbdb' }"
                        v-else 
                        class="file-placeholder">
                    <b-icon
                            :icon="getIconForMimeType(file.mime_type)"
                            size="is-large"
                            type="is-gray"/>
                 </div>
            </div>
        </figure> 

        <!-- Preview Modal ----------------- -->
        <b-modal
                :can-cancel="false"
                :active.sync="isPreviewModalActive"
                :width="640"
                scroll="keep">
            <div class="tainacan-modal-content">
                <div class="tainacan-modal-title">
                    <h2>{{ file.title.rendered }}</h2>
                    <a 
                            @click="isPreviewModalActive = false"
                            class="back-link">{{ $i18n.get('exit') }}</a>
                    <hr>
                </div>
                <div    
                        class="is-flex rendered-content"
                        v-html="file.description.rendered" />
            </div>
        </b-modal>
    </div>
</template>

<script>
export default {
    name: 'FileItem',
    props: {
        file: Object,
        size: 112,
        showName: false,
        isSelected: false,
        isPreviewModalActive: false
    },
    methods: {
        getIconForMimeType(mimeType) {

            let type = mimeType.split('/');

            if (type[0] == 'application' && type[1] != undefined){
                switch (type[1]) {
                    case 'pdf':
                        return 'file-pdf';
                    default:
                        return '';
                }
            } else {
                switch (type[0]) {
                    case 'video':
                        return 'video';
                    case 'audio':
                        return 'volume-high';
                    case 'text':
                        return 'format-align-left';
                    default:
                        return '';
                }
            }
        }
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .file-item {
        display: inline-block;

        &:hover {
            cursor: pointer;
            .image, .file-placeholder {
                transform: scale(1.05);
            }
        }
        .image-wrapper {
            overflow: hidden;
            position: relative;
            display: inline-block;

            &.rounded {
                border-bottom-left-radius: 5px;
                border-bottom-right-radius: 5px;
            }

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
            background-color: $gray2;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            padding: 8px 15px;
            font-size: 9px;
            width: 100%;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            text-align: center;
        }
    }
    .rendered-content {
        justify-content: center !important;
    }
    
</style>

