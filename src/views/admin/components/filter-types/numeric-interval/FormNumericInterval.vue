<template>
    <div>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric-interval', 'step') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-numeric-interval', 'step')"
                        :message="$i18n.getHelperMessage('tainacan-filter-numeric-interval', 'step')" />
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
        
    </div>
</template>

<script>

    export default {
        props: {
            modelValue: Object
        },
        emits: [
            'update:model-value',
        ],
        data() {
            return {
                step: [Number, String],
                showEditStepOptions: false
            }
        },
        created() {
            this.step = this.modelValue && this.modelValue.step ? this.modelValue.step : 1;
        },
        methods: {
            onUpdateStep(modelValue) {
                this.$emit('update:model-value', { step: modelValue });
            },
        }
    }
</script>