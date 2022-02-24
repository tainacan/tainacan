<template>
    <form class="tainacan-modal-content tainacan-form">
        <div class="tainacan-modal-title">
            <h2 id="item-thumbnail-alt-text-modal-title">{{ $i18n.get('label_thumbnail_alt') }}</h2>
            <hr>
        </div>
        <b-input
                type="textarea"
                ref="item-thumbnail-alt-text-input"
                :autofocus="true"
                lazy
                :placeholder="$i18n.get('instruction_thumbnail_alt')"
                :value="localAltText"
                @input="(newValue) => $emit('onUpdateThumbnailAlt', newValue)" />
        <p>{{ $i18n.get('info_thumbnail_alt') }}</p>

        <div class="field is-grouped form-submit">
            <div 
                    style="margin-left: auto;"
                    class="control">
                <button
                        id="button-submit-text-content-writing"
                        type="submit"
                        @click.prevent="$parent.close()"
                        class="button is-success">
                    {{ $i18n.get('finish') }}</button>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    props: {
        altText: ''
    },
    data(){
        return {
            localAltText: ''
        }
    },
    mounted() {
        this.localAltText = this.altText;

        if (
            this.$refs && 
            this.$refs['item-thumbnail-alt-text-input'] &&
            this.$refs['item-thumbnail-alt-text-input']['$el'] &&
            this.$refs['item-thumbnail-alt-text-input']['$el'].children &&
            this.$refs['item-thumbnail-alt-text-input']['$el'].children[0]
        ) {
            this.$refs['item-thumbnail-alt-text-input']['$el'].children[0].focus();
            this.$refs['item-thumbnail-alt-text-input']['$el'].children[0].click();
        }
    }
}
</script>