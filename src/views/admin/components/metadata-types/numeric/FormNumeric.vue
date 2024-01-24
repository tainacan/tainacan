<template>
    <section>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-numeric', 'step') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-numeric', 'step')"
                        :message="$i18n.getHelperMessage('tainacan-numeric', 'step')" />
            </label>
            <div
                    v-if="!showEditStepOptions"
                    class="is-flex">
                <b-select
                        v-model="step"
                        name="step_options"
                        @update:model-value="onUpdateStep">
                    <option value="0.001">
                        0.001
                    </option>
                    <option value="0.01">
                        0.01
                    </option>
                    <option value="0.1">
                        0.1
                    </option>
                    <option value="1">
                        1
                    </option>
                    <option value="2">
                        2
                    </option>
                    <option value="5">
                        5
                    </option>
                    <option value="10">
                        10
                    </option>
                    <option value="100">
                        100
                    </option>
                    <option value="1000">
                        1000
                    </option>
                    <option
                            v-if="step && ![0.001,0.01,0.1,1,2,5,10,100,1000].find( (element) => element == step )"
                            :value="step">
                        {{ step }}</option>
                </b-select>
                <button
                        class="button is-white is-pulled-right"
                        :aria-label="$i18n.get('edit')"
                        @click.prevent="showEditStepOptions = true">
                    <span 
                            v-tooltip="{
                                content: $i18n.get('edit'),
                                autoHide: true,
                                placement: 'bottom',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-edit has-text-secondary" />
                    </span>
                </button>
            </div>
            <div
                    v-if="showEditStepOptions"
                    class="is-flex">
                <b-input
                        v-model="step"
                        name="max_options"
                        type="number"
                        step="1"
                        @update:model-value="onUpdateStep" />
                <button
                        class="button is-white is-pulled-right"
                        @click.prevent="showEditStepOptions = false">
                    <span 
                            v-tooltip="{
                                content: $i18n.get('close'),
                                autoHide: true,
                                placement: 'bottom',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close has-text-secondary" />
                    </span>
                </button>
            </div>
        </b-field>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-numeric', 'min') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-numeric', 'min')"
                        :message="$i18n.getHelperMessage('tainacan-numeric', 'min')" />
            </label>
            <b-numberinput
                    v-model="min"
                    name="min"
                    step="1"
                    @update:model-value="onUpdateMin" />
        </b-field>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-numeric', 'max') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-numeric', 'max')"
                        :message="$i18n.getHelperMessage('tainacan-numeric', 'max')" />
            </label>
            <b-numberinput
                    v-model="max"
                    name="max"
                    step="1"
                    @update:model-value="onUpdateMax" />
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ]
        },
        emits: ['update:value'],
        data() {
            return {
                step: [Number, String],
                min: [Number, null],
                max: [Number, null],
                showEditStepOptions: false
            }
        },
        created() {
            this.step = this.value && this.value.step ? this.value.step : 0.01;
            this.min = this.value && this.value.min ? Number(this.value.min) : null;
            this.max = this.value && this.value.max ? Number(this.value.max) : null;
        },
        methods: {
            onUpdateStep(value) {
                this.$emit('update:value', { step: value, min: this.min, max: this.max });
            },
            onUpdateMin(value) {
                this.$emit('update:value', { step: this.step, min: value, max: this.max });
            },
            onUpdateMax(value) {
                this.$emit('update:value', { step: this.step, min: this.min, max: value });
            }
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
    .tainacan-help-tooltip-trigger {
        font-size: 1.25em;
    }
</style>