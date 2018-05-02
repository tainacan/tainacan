<template>
    <div
            id="tainacan-header" 
            class="level" 
            :class="{'menu-compressed': isMenuCompressed}">
        <div class="level-left">
            <div class="level-item">
                <router-link 
                        tag="a" 
                        to="/">
                    <img 
                            class="tainacan-logo" 
                            alt="Tainacan Logo" 
                            :src="logoHeader">
                </router-link>
            </div>
        </div>
        <div class="level-right">
            <a      
                    v-show="!showSearch"
                    @click="showSearch = true;"
                    class="level-item">
                <b-icon icon="magnify"/>  
            </a>
            <span
                    @mouseleave="searchTerm.length <= 0 ? showSearch = false : showSearch = true" 
                    v-show="showSearch"
                    class="search-area">
                <b-input 
                    :placeholder="$i18n.get('instruction_search_repository')"
                    type="search"
                    size="is-small"
                    v-model="searchTerm"
                    icon="magnify" />
                <a href="">{{ $i18n.get('advanced_search') }}</a>
            </span>
            <a 
                    class="level-item" 
                    :href="wordpressAdmin">
                <b-icon icon="wordpress"/>
            </a>
        </div>
    </div>
</template>

<script>

export default {
    name: 'TainacanHeader',
    data(){
        return {
            logoHeader: tainacan_plugin.base_url + '/admin/images/tainacan_logo_header.png',
            wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
            showSearch: false,
            searchTerm: ''
        }
    },
    props: {
        isMenuCompressed: false
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    
    // Tainacan Header
    #tainacan-header {
        background-color: $secondary;
        height: $header-height;
        max-height: $header-height;
        width: 100%;
        padding: 12px;
        vertical-align: middle; 
        left: 0;
        right: 0;
        position: absolute;
        z-index: 999;
        color: white;

        .level-left {
            margin-left: -12px; 

            .level-item{
                height: 52px;
                width: 180px;
                transition: margin 0.15s linear;
                -webkit-transition: margin 0.15s linear;
                margin-left: 0px;
                cursor: pointer;

                &:hover{
                    background-color: #257887;
                }
                &:focus {
                    box-shadow: none;
                }
                .tainacan-logo {
                    max-height: 22px;
                    padding: 0px 28px;   
                }
            }   
        }
        .level-right {
            padding-right: 12px;
            a{ 
                color: white;
            }
            a:hover {
                color: $tertiary;
            }
            .search-area {
                display: flex;
                align-items: center;

                input {
                    margin: 0px 12px;
                }
                .icon {
                    color: $tertiary;
                }
                a {
                    margin: 0px 12px;
                    font-size: 12px;
                }
            }
        }
        &.menu-compressed {
            .level-left .level-item {
                margin-left: 44px;
                width: 160px;

                .tainacan-logo {
                    max-height: 22px;
                    padding: 0px 16px;   
                }
            }
            
        }

        @media screen and (max-width: 769px) {
            .level-left {
                margin-left: 0px !important;
                .level-item {
                    margin-left: 30px;
                }
            }
            .level-right {
                display: none;
            }

            top: 206px;
            &.menu-compressed {
                top: 237px !important;  
            }
            margin-bottom: 0px !important;
        }

    }
</style>


