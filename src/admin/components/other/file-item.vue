<template>
    <div>
        <figure 
                class="file-item"
                :class="{'shows-modal-on-click' : modalOnClick}"
                @click="modalOnClick? isPreviewModalActive = true : null">
            <figcaption 
                    :style="{ 'max-width': size != undefined ? size + 'px' : '112px' }"
                    v-if="showName && file.title != undefined">{{ file.title.rendered }}</figcaption>
            <div 
                    :style="{ 'width': size != undefined ? size + 'px' : '112px', 'height': size != undefined ? size + 'px' : '112px' }"
                    class="image-wrapper">
                <div
                        v-if="file.media_type == 'image'" 
                        class="image"
                        :style="{ 'background-image': 'url(' + file.guid.rendered + ')' }"/>
                <div
                        :style="{ 'background-color': '#f2f2f2' }"
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
    
        <!-- Preview Modal ----------------- -->
        <template v-if="modalOnClick">
            <b-modal
                    :active.sync="isPreviewModalActive"
                    :width="1024"
                    scroll="keep">
                <div class="tainacan-modal-content">
                    <div class="tainacan-modal-title">
                        <h2 v-if="file.title != undefined">{{ file.title.rendered }}</h2>
                    </div>
                    <div    
                            class="is-flex rendered-content"
                            v-html="file.description.rendered" />
                    <iframe
                            style="width: 100%; min-height: 50vh;"    
                            v-if="file.guid != undefined && file.guid.rendered != undefined && file.mime_type != undefined && file.mime_type == 'application/pdf'"
                            :src="file.guid.rendered" />
                    <div class="field is-grouped form-submit">
                        <div class="control">
                            <button
                                    id="button-cancel-importer-edition"
                                    class="button is-outlined"
                                    type="button"
                                    @click="isPreviewModalActive = false">
                                {{ $i18n.get('exit') }}</button>
                        </div>
                    </div>
                </div>
            </b-modal>
        </template>
    </div>
</template>

<script>
export default {
    name: 'FileItem',
    data() {
        return {
            isPreviewModalActive: false
        }
    },
    props: {
        file: Object,
        size: 112,
        showName: false,
        isSelected: false,
        modalOnClick: true
    },
    methods: {
        getIconForMimeType(mimeType) {

            let type = mimeType.split('/');
            
            if (type[0] == 'application' && type[1] != undefined){
                switch (type[1]) {
                    case 'pdf':
                        return 'pdf';
                    default:
                        return '';
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

        &.shows-modal-on-click:hover {
            cursor: pointer;
            .image, .file-placeholder {
                transform: scale(1.05);
            }
        }
        &:hover {
            figcaption {
                background-color: $gray1;
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
            background-color: white;
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
        flex-direction: column;
        align-items: center;
    }
    
</style>

