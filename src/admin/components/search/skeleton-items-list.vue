<template>
<div class="table-container">
    <div class="selection-control">
        <div class="field select-all is-pulled-left">
            <span>
                <b-checkbox disabled>{{ $i18n.get('label_select_all_items_page') }}</b-checkbox>
            </span>
        </div>
        <div class="field is-pulled-right">
            <b-dropdown
                    position="is-bottom-left"
                    disabled>
                <button
                        class="button is-white"
                        slot="trigger">
                    <span>{{ $i18n.get('label_bulk_actions') }}</span>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
                    </span>
                </button>
            </b-dropdown>
        </div>
    </div>

    <div class="table-wrapper">
        <table 
                v-if="viewMode == 'table'"
                class="tainacan-table">
            <thead>
                <th class="skeleton column-default-width" />
            </thead>
            <tbody>
                <tr     
                        class="skeleton"
                        :key="item"
                        v-for="item in itemsPerPage">
                    <td class="column-default-width"/>
                </tr>
            </tbody>
        </table>
        <div 
                v-if="viewMode == 'cards' || viewMode == 'grid'"
                :class="{
                    'tainacan-cards-container': viewMode == 'cards',
                    'tainacan-grid-container': viewMode == 'grid'
                }">
            <div 
                    :key="item"
                    v-for="item in itemsPerPage"
                    class="skeleton" 
                    :class="{
                        'tainacan-card': viewMode == 'cards',
                        'tainacan-grid-item': viewMode == 'grid'
                    }"/>
        </div>
        <masonry
                :cols="getMasonryColsSettings()"
                :gutter="viewMode == 'masonry' ? 25 : 30"
                v-if="viewMode == 'masonry' || viewMode == 'records'"
                :class="{
                    'tainacan-masonry-container': viewMode == 'masonry',
                    'tainacan-records-container': viewMode == 'records'
                }">
            <div 
                    :key="item"
                    v-for="item in itemsPerPage"
                    :style="{'min-height': randomHeightForMasonryItem() + 'px' }"
                    class="skeleton" 
                    :class="{
                        'tainacan-masonry-item': viewMode == 'masonry',
                        'tainacan-record': viewMode == 'records'
                    }"/>
        </masonry>
    </div>
</div>  
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'SkeletonItemsList',
    computed: {
        itemsPerPage(){
            return this.getItemsPerPage();
        },
        viewMode() {
            return this.getAdminViewMode();
        },
    },
    methods: {
        ...mapGetters('search', [
            'getItemsPerPage',
            'getAdminViewMode',
        ]),
        getMasonryColsSettings() {
            if (this.viewMode == 'masonry')
                return { default: 7, 1919: 6, 1407: 5, 1215: 4, 1023: 3, 767: 2, 343: 1 } 
            else
                return { default: 4, 1919: 3, 1407: 2, 1215: 2, 1023: 1, 767: 1, 343: 1 }

        },
        randomHeightForMasonryItem() {
            let min = 255;
            let max = 255;

            if (this.viewMode == 'masonry') {
                min = 140;
                max = 420;
            } else if (this.viewMode == 'records') {
                min = 380;
                max = 480;
            }

            return Math.floor(Math.random()*(max-min+1)+min);
        }
    }
}

</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    // Selection Area
    .selection-control {
        
        padding: 6px 0px 0px 12px;
        background: white;
        height: 40px;

        .select-all {
            color: $gray4;
            font-size: 0.875rem;
            &:hover {
                color: $gray4;
            }
        }
    }

    // Cards View Mode
    .tainacan-cards-container {
        min-height: 50vh;
        padding: 0;
        display: -ms-grid;
        display: grid;
        grid-template-columns: repeat(auto-fill, 455px);
        grid-gap: 0px;
        justify-content: space-evenly;
        animation-name: item-appear;
        animation-duration: 0.5s;

        @media screen and (max-width: 480px) {    
            width: 91.666666667%;  
            grid-template-columns: repeat(auto-fill, 100%);
        }

        .tainacan-card {
            padding: 0px;
            flex-basis: 0;
            margin: 15px;
            max-width: 425px;
            min-width: 425px;
            min-height: 210px;
            max-height: 210px;

            @media screen and (max-width: 480px) {
                max-width: 100%;
                min-width: 100%;
                min-height: 171px;
                max-height: 171px;

                img {
                    width: 130px !important;
                    height: 130px !important;
                }
            }
        }
    }

    // Thumbnails (Grid) View Mode
    .tainacan-grid-container {
        min-height: 50vh;
        padding: 0;
        display: -ms-grid;
        display: grid;
        grid-template-columns: repeat(auto-fill, 285px);
        grid-gap: 0px;
        justify-content: space-evenly;
        animation-name: appear;
        animation-duration: 0.5s;

        .tainacan-grid-item {
            max-width: 255px;
            min-height: 300px;
            flex-basis: 0;
            margin: 15px;
            text-align: center;
        }
    }

    // Masonry View Mode
    .tainacan-masonry-container {
        min-height: 50vh;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        flex-grow: 1;
        flex-shrink: 1;
        justify-content: space-evenly;
        animation-name: item-appear;   
        animation-duration: 0.5s;

        .tainacan-masonry-item {
            display: block;
            width: 100%;
            flex-basis: 0;
            margin-bottom: 25px;
        }
    }

    // Records View Mode
    .tainacan-records-container {
        min-height: 50vh;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        flex-grow: 1;
        flex-shrink: 1;
        justify-content: space-evenly;
        animation-name: item-appear;
        animation-duration: 0.5s;

        .tainacan-record {
            background-color: #f6f6f6;
            padding: 0px;
            flex-basis: 0;
            margin: 0 auto 30px auto;
            width: 100%;
            max-width: 425px;
            min-height: 100px;
            display: block;
        }
    }

    // Table View Mode
    table.tainacan-table {
        border-spacing: 6px !important;
        th { height: 38px; }
        td { height: 52px; }
    }

</style>