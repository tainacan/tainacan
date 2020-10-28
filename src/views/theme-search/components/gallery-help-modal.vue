<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2>{{ $i18n.get('title_gallery-help-modal') }}</h2>
            <hr>
        </header>
        <div class="gallery-help-modal-content">
            <p>{{ $i18n.get('info_gallery_help_introduction') }}:</p>
            <ul>
                <li><kbd>&larr;</kbd>&nbsp;{{ $i18n.get('info_gallery_previous_item') }};</li>
                <li><kbd>&rarr;</kbd>&nbsp;{{ $i18n.get('info_gallery_next_item') }};</li>
                <li><kbd>&uarr;</kbd>&nbsp;/&nbsp;<kbd>&darr;</kbd>&nbsp;{{ $i18n.get('info_gallery_hide_controls') }};</li>
                <li><kbd>{{ $i18n.get('label_space_key') }}</kbd>&nbsp;{{ $i18n.get('info_gallery_start_transition') }};</li>
                <li><kbd>{{ $i18n.get('label_esc_key') }}</kbd>&nbsp;{{ $i18n.get('info_gallery_exit') }}.</li>
            </ul>
            <p v-html="$i18n.getWithVariables('info_gallery_help_end', [`<i class='tainacan-icon tainacan-icon-metadata'></i>`, `<i class='tainacan-icon tainacan-icon-see'></i>`])" />
        </div>
        <footer class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-close-gallery-modal"
                        class="button is-outlined"
                        type="button"
                        @click="$parent.close();">
                    {{ $i18n.get('close') }}</button>
            </div>
        </footer>
    </div>
</template>

<style lang="scss">
    .modal.gallery-help-modal {
        z-index: 9999999999;

        .gallery-help-modal-content {
            font-size: 0.875em;

            li {
                margin-bottom: 0.25rem;
            }
        }
    }
</style>