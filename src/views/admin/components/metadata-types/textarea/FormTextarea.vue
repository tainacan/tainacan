<template>
    <section> 
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-textarea', 'maxlength') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-textarea', 'maxlength')"
                        :message="$i18n.getHelperMessage('tainacan-textarea', 'maxlength')" />
            </label>
            <b-numberinput
                    v-model="maxlength"
                    name="maxlength"
                    step="1"
                    min="0"
                    @input="onUpdateMaxlength" />
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ]
        },
        data() {
            return {
                maxlength: [Number, null]
            }
        },
        created() {
            this.maxlength = this.value && this.value.maxlength ? Number(this.value.maxlength) : null;
        },
        methods: {
            onUpdateMaxlength(value) {
                if (value == 0) value = null;

                this.$emit('input', { maxlength: value });
            }
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
    .tainacan-help-tooltip-trigger {
        font-size: 1em;
    }
</style>