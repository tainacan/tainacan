<template>
    <section>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-user', 'default_author') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-user', 'default_author')"
                        :message="$i18n.getHelperMessage('tainacan-user', 'default_author')" />
            </label>
            <b-checkbox
                    v-model="defaultAuthor"
                    true-value="yes"
                    false-value="no"
                    @update:model-value="onUpdateDefaultAuthor">
                {{ $i18n.get('label_default_author_user') }}
            </b-checkbox>
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ]
        },
        emits: [
            'update:value',
        ],
        data() {
            return {
                defaultAuthor: String
            }
        },
        created() {
            this.defaultAuthor = this.value && this.value.default_author ? this.value.default_author : 'no';
        },
        methods: {
            onUpdateDefaultAuthor(value) {
                this.$emit('update:value', { default_author: value });
            }
        }
    }
</script>

<style scoped>
    .tainacan-help-tooltip-trigger {
        font-size: 1.25em;
    }
</style>