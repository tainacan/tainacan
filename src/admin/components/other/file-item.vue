<template>
    <div>
        <figure 
                class="file-item"
                @click="isPreviewModalActive = true">
            <div class="image-wrapper">
                <div
                        v-if="file.media_type == 'image'" 
                        class="image"
                        :style="{ 'background-image': 'url(' + file.guid.rendered + ')' }"/>
                 <div
                        v-else 
                        class="file-placeholder">
                    <b-icon
                            :icon="getIconForMimeType(file.mime_type)"
                            size="is-large"
                            type="is-gray"/>
                 </div>
            </div>
            <figcaption v-if="showName">{{ file.title.rendered }}</figcaption>
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
                    <hr>
                </div>
                <div    
                        class="is-flex"
                        v-html="file.description.rendered" />
                <div class="field is-grouped form-submit">
                    <div class="control">
                        <button
                                id="button-cancel-url-link-selection"
                                class="button is-outlined"
                                type="button"
                                @click="isPreviewModalActive = false">
                            {{ $i18n.get('cancel') }}</button>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
export default {
    name: 'FileItem',
    props: {
        file: Object,
        showName: false,
        isSelected: false,
        isPreviewModalActive: false
    },
    methods: {
        getIconForMimeType(mimeType) {
            switch (mimeType) {

                case 'application/pdf':
                    return 'file-pdf';
                case 'text':
                    return 'format-align-left';
                default:
                    return '';
            }
        }
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .file-item {
        
        &:hover {
            cursor: pointer;
            .image, .file-placeholder {
                transform: scale(1.1);
            }
        }
        .image-wrapper {
            height: 112px;
            width: 112px;
            border-radius: 2px;
            overflow: hidden;
            position: relative;
            display: inline-block;
            background-color: $tainacan-input-background; 

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
            background-color: $tainacan-input-background;
            border-bottom-left-radius: 7px;
            border-bottom-right-radius: 7px;
            padding: 5px 15px;
            font-size: 9px;
            margin-top: -4px;
            width: 112px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            text-align: center;
        }
    }
    .is-flex {
        justify-content: center;
    }
    
</style>

