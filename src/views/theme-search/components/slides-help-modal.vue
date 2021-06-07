<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2>{{ $i18n.get('title_slides-help-modal') }}</h2>
            <hr>
        </header>
        <div class="slides-help-modal-content">
            <p>{{ $i18n.get('info_slides_help_introduction') }}:</p>
            <ul>
                <li><kbd>&larr;</kbd>&nbsp;{{ $i18n.get('info_slides_previous_item') }};</li>
                <li><kbd>&rarr;</kbd>&nbsp;{{ $i18n.get('info_slides_next_item') }};</li>
                <li><kbd>&uarr;</kbd>&nbsp;/&nbsp;<kbd>&darr;</kbd>&nbsp;{{ $i18n.get('info_slides_hide_controls') }};</li>
                <li><kbd>{{ $i18n.get('label_space_key') }}</kbd>&nbsp;{{ $i18n.get('info_slides_start_transition') }};</li>
                <li><kbd>{{ $i18n.get('label_esc_key') }}</kbd>&nbsp;{{ $i18n.get('info_slides_exit') }}.</li>
            </ul>
            <p v-html="$i18n.getWithVariables('info_slides_help_end', [`<i class='tainacan-icon tainacan-icon-metadata'></i>`, `<i class='tainacan-icon tainacan-icon-see'></i>`])" />
        </div>
        <footer class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-close-slides-modal"
                        class="button is-outlined"
                        type="button"
                        @click="$parent.close();">
                    {{ $i18n.get('close') }}</button>
            </div>
        </footer>
    </div>
</template>

<style lang="scss">
    .tainacan-modal.slides-help-modal {
        z-index: 9999999999;

        .slides-help-modal-content {
            font-size: 0.875em;

            li {
                margin-bottom: 0.25rem;
            }
        }
    }
</style>