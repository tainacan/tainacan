<template>
    <div 
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal
            class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2 v-if="file.title != undefined">
                {{ $i18n.get('label_attachment') + ': ' + file.title }}
            </h2>
            <button         
                    class="button is-medium is-white is-align-self-flex-start"
                    :aria-label="$i18n.get('close')"
                    @click="$emit('close')">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-close tainacan-icon-1-125em" />
                </span>
            </button>
        </header>
        <div    
                class="is-flex rendered-content"
                v-html="file.description ? file.description : `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + file.url + `'/>`" />
        <iframe
                v-if="file.url != undefined && file.url != undefined && file.mime_type != undefined && file.mime_type == 'application/pdf'"    
                style="width: 100%; min-height: 50vh;"
                :src="file.url" />
        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-cancel-file-preview"
                        class="button is-outlined"
                        type="button"
                        @click="$emit('close')">
                    {{ $i18n.get('exit') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FilePreviewModal',
    props: {
        file: Object
    },
    emits: [
        'close'
    ]
}
</script>