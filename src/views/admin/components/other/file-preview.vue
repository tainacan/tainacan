<template>
    <div 
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal
            class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2 v-if="file.title != undefined">{{ $i18n.get('label_attachment') + ': ' + file.title }}</h2>
            <hr>
        </header>
        <div    
                class="is-flex rendered-content"
                v-html="file.description ? file.description : `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + file.url + `'/>`" />
        <iframe
                style="width: 100%; min-height: 50vh;"    
                v-if="file.url != undefined && file.url != undefined && file.mime_type != undefined && file.mime_type == 'application/pdf'"
                :src="file.url" />
        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-cancel-importer-edition"
                        class="button is-outlined"
                        type="button"
                        @click="$parent.close()">
                    {{ $i18n.get('exit') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FilePreview',
    props: {
        file: Object
    }
}
</script>