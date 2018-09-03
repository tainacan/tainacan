<template>
    <div>
        <figure 
                class="document-item"
                @click.prevent="isPreviewModalActive = true">
             <div 
                    class="image-wrapper"
                    v-html="documentHtml" />
        </figure> 

        <!-- Preview Modal ----------------- -->
        <b-modal
                :active.sync="isPreviewModalActive"
                scroll="keep">
            <!-- <div class="tainacan-modal-content">
                <div class="tainacan-modal-title">
                    <h2>{{ $i18n.get('label_document') }}</h2>
                    <a 
                            @click="isPreviewModalActive = false"
                            class="back-link">{{ $i18n.get('exit') }}</a>
                    <hr>
                </div> -->
                <div    
                        class="is-flex rendered-content"
                        v-html="documentHtml" />
            <!-- </div> -->
        </b-modal>
    </div>
</template>

<script>
export default {
    name: 'DocumentItem',
    props: {
        documentHtml: String,
        isSelected: false,
        isPreviewModalActive: false
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .document-item {

        &:hover {
            cursor: pointer;
            .image, .document-placeholder {
                transform: scale(1.05);
            }
        }
        .image-wrapper {

            .document-placeholder {
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

