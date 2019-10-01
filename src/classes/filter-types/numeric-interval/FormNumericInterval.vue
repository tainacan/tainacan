<template>
    <div>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric-interval', 'input-mode') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-numeric-interval', 'input-mode')"
                        :message="$i18n.getHelperMessage('tainacan-filter-numeric-interval', 'input-mode')"/>
            </label>
            <b-radio
                    v-model="inputMode"
                    name="inputMode"
                    native-value="custom"
                    @input="onUpdate">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric-interval', 'custom') }}
            </b-radio>
            <b-radio
                    v-model="inputMode"
                    name="inputMode"
                    native-value="list"
                    @input="onUpdate">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric-interval', 'list') }}
            </b-radio>
       </b-field>
       
        <template v-if="inputMode == 'custom'">
            <b-field :addons="false">
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-filter-numeric-interval', 'step') }}<span>&nbsp;*&nbsp;</span>
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-filter-numeric-interval', 'step')"
                            :message="$i18n.getHelperMessage('tainacan-filter-numeric-interval', 'step')"/>
                </label>
                <div
                        v-if="!showEditStepOptions"
                        class="is-flex">
                    <b-select
                            name="step_options"
                            v-model="step"
                            @input="onUpdate">
                        <option value="0.001">0.001</option>
                        <option value="0.01">0.01</option>
                        <option value="0.1">0.1</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="100">100</option>
                        <option value="1000">1000</option>
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
                                    placement: 'bottom'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-edit has-text-secondary"/>
                        </span>
                    </button>
                </div>
                <div
                        v-if="showEditStepOptions"
                        class="is-flex">
                    <b-input
                            name="max_options"
                            v-model="step"
                            @input="onUpdate"
                            type="number"
                            step="1" />
                    <button
                            @click.prevent="showEditStepOptions = false"
                            class="button is-white is-pulled-right">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('close'),
                                    autoHide: true,
                                    placement: 'bottom'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close has-text-secondary"/>
                        </span>
                    </button>
                </div>
            </b-field>
        </template>
        <template v-if="inputMode == 'list'">
            <b-field :addons="false">
                <label class="label is-inline">
                    {{ $i18n.getHelperTitle('tainacan-filter-numeric-interval', '') }}<span>&nbsp;*&nbsp;</span>
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-filter-numeric-interval', 'step')"
                            :message="$i18n.getHelperMessage('tainacan-filter-numeric-interval', 'step')"/>
                </label>
                <a
                        role="button"
                        v-if="intervals.length == 0" 
                        @click="addInterval()"
                        class="is-inline add-link">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span>
                    &nbsp;add new
                </a>
                <div
                        v-for="(interval, index) of intervals"
                        :key="index">
                    <b-field label="c1">
                       <b-input 
                           @input="onUpdate"
                           v-model="interval.label" />
                   </b-field>
                   <b-field label="c2">
                       <b-numberinput 
                           @input="onUpdate"
                           v-model="interval.from" />
                   </b-field>
                   <b-field label="c3">
                       <b-numberinput 
                           @input="onUpdate"
                           v-model="interval.to" />
                   </b-field>
                   
                   <a
                           role="button"
                           @click="addInterval(index)"
                           class="is-inline add-link">
                       <span class="icon is-small">
                           <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                       </span>
                       &nbsp;add new
                   </a>
                   
                   <a
                           role="button"
                           @click="removeInterval(index)"
                           class="is-inline add-link">
                       <span class="icon is-small">
                           <i class="tainacan-icon has-text-secondary tainacan-icon-remove"/>
                       </span>
                       &nbsp;remove 
                   </a>
                   
               </div>
           </b-field>
       </template>
    </div>
</template>

<script>

    export default {
        props: {
            filter: {
                type: Object
            },
            value: [String, Number, Array],
            id: '',
            disabled: false,
        },
        data() {
            return {
                step: [Number, String],
                inputMode: 'custom',
                showEditStepOptions: false,
                intervals: [],
            }
        },
        methods: {
            onUpdate() {
                this.$emit('input', { 
                    step: this.step, 
                    intervals: this.intervals, 
                    inputMode: this.inputMode 
                });
            },
            
            removeInterval(index) {
                this.intervals.splice(index, 1);
            },
            addInterval(index) { 
                if (index) {
                    this.intervals.splice(index + 1, 0, {
                        label: '',
                        to: 0,
                        from: 0
                    })
                } else {
                    this.intervals.push({
                        label: '',
                        to: 0,
                        from: 0
                    });
                }
            }
        },
        created() {
            this.step = this.value && this.value.step ? this.value.step : 1;
            this.inputMode = this.value && this.value.inputMode ? this.value.inputMode : 'custom';
            this.intervals = this.value && this.value.intervals ? this.value.intervals : [];
        }
    }
</script>