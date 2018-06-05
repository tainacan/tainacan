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
            <span class="search-area">
                <div class="control has-icons-right is-small is-clearfix">
                    <input 
                            autocomplete="on" 
                            :placeholder="$i18n.get('instruction_search')"
                            class="input is-small" 
                            type="search" 
                            v-model="searchTerm">
                    <span class="icon is-right">
                        <i class="mdi mdi-magnify" />
                    </span>
                </div>
                <a @click.prevent="openAdvancedSearchComponent">{{ $i18n.get('advanced_search') }}</a>
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
            searchTerm: ''
        }
    },
    methods: {
        openAdvancedSearchComponent(){
            console.log('Called here', this.$route.meta);
            this.$set(this.$route.meta, 'openAdvancedSearch', !this.$route.meta.openAdvancedSearch);
            console.log('Called here', this.$route.meta);
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
                height: $header-height;
                width: 180px;
                transition: width 0.15s, background-color 0.2s;
                -webkit-transition: width 0.15s background-color 0.2s;   
                cursor: pointer;
                background-color: #257787;
                
                &:focus {
                    box-shadow: none;
                }
                .tainacan-logo {
                    max-height: 22px;
                    padding: 0px 24px;
                    transition: padding 0.15s;
                    -webkit-transition: padding linear 0.15s;   
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
                margin-right: 36px;

                .control {
                    input {
                        height: 27px;
                        font-size: 11px;
                        color: $gray-light;
                        transition: width linear 0.15s;
                        -webkit-transition: width linear 0.15s;
                        width: 160px;
                    }
                    input:focus, input:active {
                        width: 220px !important;
                    }
                    .icon {
                        color: $tertiary;
                        height: 27px;
                        font-size: 18px;
                    }
                }
               
                a {
                    margin: 0px 12px;
                    font-size: 12px;
                }
            }
        }
        &.menu-compressed {
            .level-left .level-item {
                width: 220px;
                background-color: $secondary;
                .tainacan-logo {
                    padding: 0px 42px;   
                }
            }
            
        }

        @media screen and (max-width: 769px) {
            padding: 0px;
            display: flex;
            .level-left {
                display: inline-block;
                margin-left: 0px !important;
                .level-item {
                    margin-left: 0px;
                }
            }
            .level-right {
                margin-top: 0;
                display: inline-block;
            }

            top: 0px;
            margin-bottom: 0px !important;
        }

    }
</style>


