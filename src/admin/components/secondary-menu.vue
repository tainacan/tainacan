<template>
    <nav id="secondary-menu" role="navigation" aria-label="secondary navigation" class="column is-2 is-sidebar-menu">
        <aside class="menu">
            <ul class="menu-list">
                <li><router-link tag="a" to="">{{ $i18n.get('items')}}</router-link></li>
                <li><router-link tag="a" to="">{{ $i18n.get('edit')}}</router-link></li>
                <li><router-link tag="a" to="">{{ $i18n.get('fields')}}</router-link></li>
                <li><router-link tag="a" to="">{{ $i18n.get('filters')}}</router-link></li>
            </ul>
        </aside>
    </nav>
</template>

<script>
export default {
    name: 'SecondaryMenu',
    data(){
        return {
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../scss/_variables.scss";

    #secondary-menu {
        background-color: $primary;
        padding: 0px;
        -webkit-transition: max-width 0.3s linear; /* Safari */
        transition: max-width 0.3s linear;
        max-width: 180px; 

        li{
            a {
                color: white;
                white-space: nowrap;
                overflow: hidden;
                padding: 1em 1.8em;
                line-height: 1.5em;
                border-radius: 0px;
                -webkit-transition: padding 0.3s linear; /* Safari */
                transition: padding 0.3s linear; 
            }
            a:hover {
                background-color: white;
                color: $secondary
            }
            a.is-active {
                background-color: $primary-light;
                color: $secondary;
            }
            .menu-text {
                padding-left: 0.7em;
                opacity: 1;
                visibility: visible;
                transition: opacity 0.3s linear, visibility 0.3s linear;
                -webkit-transition: opacity 0.3s linear, visibility 0.3s linear;
            }
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            a{ padding: 1em 0.8em !important; }
            .menu-text {
                display: none !important;
            }
            ul { 
                display: flex;
                align-items: stretch;
                justify-content: space-evenly;
            }
        }
    }

</style>


