<template>
    <div>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric-list-interval', 'showIntervalOnTag') }}<span>&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-numeric-list-interval', 'showIntervalOnTag')"
                        :message="$i18n.getHelperMessage('tainacan-filter-numeric-list-interval', 'showIntervalOnTag')" />
            </label>
            <div>
                <b-field>
                    <b-checkbox
                            v-model="showIntervalOnTag"
                            @update:model-value="onUpdateShowIntervalOnTag()">
                        {{ $i18n.get('info_show_interval_on_tag') }}
                    </b-checkbox>
                </b-field>
            </div>
        </b-field>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric-list-interval', 'intervals') }}<span>&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-numeric-list-interval', 'intervals')"
                        :message="$i18n.getHelperMessage('tainacan-filter-numeric-list-interval', 'intervals')" />
            </label>
            <transition-group name="filter-item">
                <div
                        v-for="(interval, index) of intervals"
                        :key="0 + index"
                        class="options-input">
                    <b-field>
                        <b-input
                                v-model="interval.label"
                                expanded
                                :placeholder="$i18n.get('label')"
                                @update:model-value="onUpdate(interval)" />
                    </b-field>
                    <b-field>
                        <b-input
                                v-model="interval.from"
                                expanded
                                type="number"
                                step="0.01"
                                :placeholder="$i18n.get('info_initial_value')"
                                @update:model-value="onUpdate(interval, true)" />
                        <b-input
                                v-model="interval.to"
                                expanded
                                type="number"
                                step="0.01"
                                :placeholder="$i18n.get('info_final_value')"
                                @update:model-value="onUpdate(interval, true)" />
                    </b-field>
                    <p class="control">
                        <a
                                role="button"
                                class="add-link"
                                :title="$i18n.get('add_value')"
                                @click="addInterval(index)">
                            <span class="icon is-small">
                                <i class="tainacan-icon has-text-secondary tainacan-icon-add" />
                            </span>
                                &nbsp;{{ $i18n.get('add_value') }}
                        </a>
                    </p>
                    <p 
                        v-if="intervals.length > 1"
                        class="control">
                        <a
                                role="button"
                                class="add-link"
                                :title="$i18n.get('remove_value')"
                                @click="removeInterval(index)">
                            <span class="icon is-small">
                                <i class="tainacan-icon has-text-secondary tainacan-icon-repprovedcircle" />
                            </span>
                            &nbsp;{{ $i18n.get('remove_value') }}
                        </a>
                    </p>
                </div>
            </transition-group>
        </b-field>
    </div>
</template>

<script>

    export default {
        props: {
            filter: Object,
            value: [String, Number, Array],
            id: '',
            disabled: false,
        },
        emits: [
            'input',
        ],
        data() {
            return {
                showIntervalOnTag: true,
                intervals: [],
                isValid: true,
            }
        },
        created() {
            this.intervals = 
                this.value && this.value.intervals && this.value.intervals.length > 0 ? 
                    this.value.intervals : 
                    [{
                        label: '',
                        to: null,
                        from: null
                    }];
            this.showIntervalOnTag = this.value && this.value.showIntervalOnTag != undefined ? this.value.showIntervalOnTag : true;
        },
        methods: {
            onUpdate: _.debounce( function(interval, validade) {
                if (validade != undefined && validade == true && 
                    (interval.to == null || interval.from == null ||
                     interval.to == "" || interval.from == "" ||
                     Number(interval.to) < Number(interval.from))
                ) {
                    this.isValid = false;
                    
                    if (interval.to != '' && interval.from != '' && interval.to != null && interval.from != null)
                        this.showErrorMessage()
                } else {
                    this.isValid = true;
                    this.$emit('input', {
                        intervals: this.intervals,
                        showIntervalOnTag: this.showIntervalOnTag
                    });
                }
            }, 600),
            onUpdateShowIntervalOnTag() {
                if (this.isValid) {
                    this.$emit('input', {
                        intervals: this.intervals,
                        showIntervalOnTag: this.showIntervalOnTag
                    });
                }
            },
            showErrorMessage() {
                this.$buefy.toast.open({
                    duration: 3000,
                    message: this.$i18n.get('info_error_first_value_greater'),
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            },
            removeInterval(index) {
                this.intervals.splice(index, 1);
            },
            addInterval(index) {
                if (index != undefined) {
                    this.intervals.splice(index + 1, 0, {
                        label: '',
                        to: null,
                        from: null
                    })
                } else {
                    this.intervals.push({
                        label: '',
                        to: null,
                        from: null
                    });
                }
            }
        }
    }
</script>

<style scoped lang="scss">
 
    .options-input {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;

        .field:first-child {
            width: 100%;
            margin-top: 1em;
            margin-bottom: -1px;
        }
        .field.has-addons {
            margin-bottom: 0.125em;
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            &>.control {
                flex-basis: 50%;
            }
        }
    }

</style>
