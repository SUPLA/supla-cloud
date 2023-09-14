<template>
    <div>
        <label class="d-flex">
            <span class="flex-grow-1">{{ $t(labelI18n) }}</span>
            <a class="ml-2 text-normal" @click="helpShown = !helpShown" v-if="!disabled">
                {{ $t('List of available variables') }}
                <fa icon="question-circle" class="ml-1"/>
            </a>
        </label>
        <transition-expand>
            <div class="variable-field-help" v-if="helpShown">
                <div class="d-flex">
                    <h6 class="flex-grow-1">{{ $t('Available variables') }}</h6>
                    <a class="ml-2" @click="helpShown = !helpShown">
                        <fa icon="times-circle"/>
                    </a>
                </div>
                <ul>
                    <li v-for="item in allVariables" :key="item.value">
                        {{ item.label }} <code @click="insertVariable(item)" class="pointer">{{ '{' + item.value }}</code>
                    </li>
                </ul>
            </div>
        </transition-expand>
        <div class="variable-field-container">
            <div class="preview" v-html="modelHighlighted" ref="preview"></div>
            <Mentionable
                :keys="['{']"
                :items="allVariables"
                :limit="7"
                insert-space>
                <input type="text" class="form-control" v-model="model" :disabled="disabled"
                    @input="model = $event.currentTarget.value"
                    maxlength="100" autocomplete="off" @scroll="syncScroll()" @keyup="syncScroll()" @click="syncScroll()" ref="input">
                <template #no-result>
                    <div>{{ $t('No results') }}</div>
                </template>
                <template #item="{ item }">
                    <div class="variable-hint">
                        {{ item.label }} <code>{{ '{' + item.value }}</code>
                    </div>
                </template>
            </Mentionable>
        </div>
    </div>
</template>

<script>
    import {Mentionable} from 'vue-mention'
    import ChannelFunction from "@/common/enums/channel-function";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {TransitionExpand, Mentionable},
        props: {
            labelI18n: String,
            value: String,
            disabled: Boolean,
            subject: Object,
        },
        data() {
            return {
                helpShown: false,
                lastCaretPosition: 0,
            };
        },
        methods: {
            insertVariable(variable) {
                this.model = (this.model.substring(0, this.lastCaretPosition)
                    + '{' + variable.value
                    + this.model.substring(this.lastCaretPosition))
                    .substring(0, 100)
                this.$nextTick(() => {
                    this.$refs.input.focus();
                    const caretPosition = Math.min(this.lastCaretPosition + variable.value.length + 1, 100);
                    this.$refs.input.selectionStart = caretPosition;
                    this.$refs.input.selectionEnd = caretPosition;
                    this.lastCaretPosition = caretPosition;
                });
            },
            syncScroll() {
                this.lastCaretPosition = this.$refs.input.selectionEnd;
                this.$refs.preview.scrollLeft = this.$refs.input.scrollLeft;
            }
        },
        computed: {
            model: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            modelHighlighted() {
                const highlighted = this.model
                    .replace(new RegExp("&", "g"), "&amp;")
                    .replace(new RegExp("<", "g"), "&lt;");
                return this.allVariables
                    .reduce((h, {value}) => h.replace(new RegExp('{' + value, 'g'), `<span class="variable">{${value}</span>`), highlighted);
            },
            subjectVariables() {
                switch (this.subject?.functionId) {
                    case ChannelFunction.THERMOMETER:
                        return [
                            {label: this.$t('Temperature'), value: '{temperature}'},
                        ];
                    case ChannelFunction.HUMIDITY:
                        return [
                            {label: this.$t('Humidity'), value: '{humidity}'},
                        ];
                    case ChannelFunction.HUMIDITYANDTEMPERATURE:
                        return [
                            {label: this.$t('Temperature'), value: '{temperature}'},
                            {label: this.$t('Humidity'), value: '{humidity}'},
                        ];
                    case ChannelFunction.ELECTRICITYMETER: {
                        const enabledPhases = this.subject.config.enabledPhases || [1, 2, 3];
                        const onePhase = enabledPhases.length <= 1;
                        const defaultModes = ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy'];
                        const availableModes = this.subject.config.countersAvailable || defaultModes;
                        const variables = [];
                        variables.push({label: this.$t('Voltage') + (onePhase ? '' : ` (${this.$t('average')})`), value: '{voltage_avg}'});
                        variables.push({label: this.$t('Current') + (onePhase ? '' : ` (${this.$t('sum')})`), value: '{current_sum}'});
                        variables.push({
                            label: this.$t('Power active') + (onePhase ? '' : ` (${this.$t('sum')})`),
                            value: '{power_active_sum}'
                        });
                        variables.push({
                            label: this.$t('Power reactive') + (onePhase ? '' : ` (${this.$t('sum')})`),
                            value: '{power_reactive_sum}'
                        });
                        variables.push({
                            label: this.$t('Power apparent') + (onePhase ? '' : ` (${this.$t('sum')})`),
                            value: '{power_apparent_sum}'
                        });
                        if (availableModes.includes('forwardActiveEnergy')) {
                            variables.push({
                                label: this.$t('Forward active energy') + (onePhase ? '' : ` (${this.$t('sum')})`),
                                value: '{fae_sum}'
                            });
                        }
                        if (availableModes.includes('reverseActiveEnergy')) {
                            variables.push({
                                label: this.$t('Reverse active energy') + (onePhase ? '' : ` (${this.$t('sum')})`),
                                value: '{rae_sum}'
                            });
                        }
                        if (!onePhase) {
                            enabledPhases.forEach(phaseNo => variables.push({
                                label: `${this.$t('Voltage')} (${this.$t(`Phase ${phaseNo}`).toLowerCase()})`,
                                value: `{voltage${phaseNo}}`
                            }));
                            enabledPhases.forEach(phaseNo => variables.push({
                                label: `${this.$t('Current')} (${this.$t(`Phase ${phaseNo}`).toLowerCase()})`,
                                value: `{current${phaseNo}}`
                            }));
                            enabledPhases.forEach(phaseNo => variables.push({
                                label: `${this.$t('Power active')} (${this.$t(`Phase ${phaseNo}`).toLowerCase()})`,
                                value: `{power_active${phaseNo}}`
                            }));
                            enabledPhases.forEach(phaseNo => variables.push({
                                label: `${this.$t('Power reactive')} (${this.$t(`Phase ${phaseNo}`).toLowerCase()})`,
                                value: `{power_reactive${phaseNo}}`
                            }));
                            enabledPhases.forEach(phaseNo => variables.push({
                                label: `${this.$t('Power apparent')} (${this.$t(`Phase ${phaseNo}`).toLowerCase()})`,
                                value: `{power_apparent${phaseNo}}`
                            }));
                        }
                        if (availableModes.includes('forwardActiveEnergy')) {
                            if (availableModes.includes('forwardActiveEnergyBalanced')) {
                                variables.push({label: this.$t('Forward active energy balanced'), value: '{fae_balanced}'});
                            }
                            if (!onePhase) {
                                enabledPhases.forEach(phaseNo => variables.push({
                                    label: `${this.$t('Forward active energy')} (${this.$t(`Phase ${phaseNo}`).toLowerCase()})`,
                                    value: `{fae${phaseNo}}`
                                }));
                            }
                        }
                        if (availableModes.includes('reverseActiveEnergy')) {
                            if (availableModes.includes('reverseActiveEnergyBalanced')) {
                                variables.push({label: this.$t('Reverse active energy balanced'), value: '{rae_balanced}'});
                            }
                            if (!onePhase) {
                                enabledPhases.forEach(phaseNo => variables.push({
                                    label: `${this.$t('Reverse active energy')} (${this.$t(`Phase ${phaseNo}`).toLowerCase()})`,
                                    value: `{rae${phaseNo}}`
                                }));
                            }
                        }
                        return variables;
                    }
                    default:
                        return [];
                }
            },
            allVariables() {
                return [
                    ...this.subjectVariables,
                    {label: this.$t('Date'), value: '{date}'},
                    {label: this.$t('Time'), value: '{time}'},
                    {label: this.$t('Date and time'), value: '{date_time}'},
                ].map(v => ({label: v.label, value: v.value.substring(1), searchText: `${v.label} ${v.value}`}))
            }
        },
    };
</script>

<style lang="scss">
    @import '../styles/variables';

    .mention-item {
        padding: 4px 10px;
        border-radius: 4px;
    }

    .mention-selected {
        background: $supla-green;
        color: white;
    }

    .label-hint {
        font-weight: normal;
        font-size: .8em;
        color: $supla-grey-dark;
    }

    .variable-field-container {
        position: relative;
        .form-control {
            color: transparent;
            background: transparent;
            caret-color: black;
        }
        .preview {
            position: absolute;
            top: 0;
            left: 0;
            padding: 8px 23px 0 13px;
            overflow: hidden;
            width: 100%;
            white-space: pre;
            .variable {
                color: #3f903f;
                text-shadow: -0.5px 0 #3f903f;
            }
        }
    }

    .variable-field-help {
        font-size: .8em;
    }
</style>
