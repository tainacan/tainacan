<template>
    <section>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-rich-text', 'maxlength') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-rich-text', 'maxlength')"
                        :message="$i18n.getHelperMessage('tainacan-rich-text', 'maxlength')" />
            </label>
            <b-numberinput
                    v-model="maxlength"
                    name="maxlength"
                    step="1"
                    min="0"
                    controls-position="compact"
                    controls-alignment="right"
                    expanded
                    @update:model-value="onUpdateMaxlength" />
        </b-field>
    </section>
</template>

<script>
export default {
    props: { value: [ String, Object, Array ] },
    emits: [ 'update:value' ],
    data() {
        return { maxlength: this.value?.maxlength ? Number(this.value.maxlength) : null };
    },
    methods: {
        onUpdateMaxlength(value) {
            this.$emit('update:value', { maxlength: value == 0 ? null : value });
        }
    }
};
</script>
