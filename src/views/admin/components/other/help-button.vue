<template>
    <span class="tainacan-help-tooltip-trigger">
        <a 
                v-tooltip="{
                    content: getHelperTooltipContent,
                    autoHide: true,
                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-helper-tooltip', extraClasses],
                    html: true,
                    delay: {
                        show: 0,
                        hide: 100,
                    }
                }"
                :class="forcedIconColor ? 'has-text-' + forcedIconColor : ''">
            <span class="icon is-small">
                <i class="tainacan-icon tainacan-icon-help" />
            </span>
        </a>
    </span>
</template>

<script>
export default {
    name: 'HelpButton',
    props: {
        title: '',
        message: '',
        extraClasses: '',
        forcedIconColor: ''
    },
    computed: {
        getHelperTooltipContent() {
            return ( this.title ? ( `<p style="font-weight: bold; color: var(--tainacan-secondary);">` + this.title + `</p>` ) : '' ) +
                    `<p style="color: var(--tainacan-info-color);">` + ((this.message != '' && this.message != undefined) ? this.message : this.$i18n.get('info_no_description_provided')) + `</p>`;
        }
    }
}
</script>

<style lang="scss">

    .tainacan-help-tooltip-trigger > a .icon {
        i, i::before { font-size: 0.875em !important }
    }

</style>

