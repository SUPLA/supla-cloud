<template>
    <div class="reaction-condition-em">
        <div class="form-group">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                    {{ $t(field.label) }}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li v-for="f in fields" :key="f.name">
                        <a @click="field = f">{{ $t(f.label) }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <transition-expand>
            <div class="form-group d-flex align-items-center" v-if="subject.config.enabledPhases.length > 1 && !field.disablePhases">
                <label class="flex-grow-1 pr-3">{{ $t('Phase') }}</label>
                <div class="btn-group btn-group-flex">
                    <a :class="'btn ' + ((phase === p) ? 'btn-green' : 'btn-default')"
                        v-for="p in availablePhases"
                        :key="p"
                        @click="phase = p">
                        {{ p === 'all' ? (field.name === 'voltage' ? $t('average') : $t('sum')) : p }}
                    </a>
                </div>
            </div>
        </transition-expand>
        <ReactionConditionThreshold v-if="field" v-model="trigger" :field="fieldName"
            :subject="subject" :operators="operators"
            :default-threshold="defaultThreshold(fieldName, subject)"
            :unit="unit" :label-i18n="labelI18n" :resume-label-i18n="resumeLabelI18n"/>
    </div>
</template>

<script>
    import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {TransitionExpand, ReactionConditionThreshold},
        props: {
            value: Object,
            subject: Object,
            unit: Function,
            labelI18n: Function,
            resumeLabelI18n: Function,
            defaultThreshold: Function,
        },
        data() {
            return {
                phase: 'all',
                field: undefined,
            };
        },
        beforeMount() {
            this.initFromValue();
        },
        methods: {
            initFromValue() {
                if (this.value?.on_change_to) {
                    const name = this.value.on_change_to.name;
                    this.field = this.fields.find(f => name === f.name);
                    if (!this.field) {
                        this.field = this.fields.find(f => name.startsWith(f.name));
                    }
                    if (name.endsWith('3')) {
                        this.phase = 3;
                    } else if (name.endsWith('2')) {
                        this.phase = 2;
                    } else if (name.endsWith(1)) {
                        this.phase = 1;
                    } else {
                        this.phase = 'all';
                    }
                }
                if (!this.field) {
                    this.field = this.fields[0];
                }
            }
        },
        computed: {
            availablePhases() {
                return (this.subject.config.enabledPhases || [1, 2, 3]).concat(['all']);
            },
            fields() {
                const fields = [
                    {label: 'Voltage', name: 'voltage',}, // i18n
                    {label: 'Current', name: 'current',}, // i18n
                    {label: 'Power active', name: 'power_active'}, // i18n
                    {label: 'Power reactive', name: 'power_reactive'}, // i18n
                    {label: 'Power apparent', name: 'power_apparent'}, // i18n
                ];
                const defaultModes = ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy'];
                const availableModes = this.subject.config.countersAvailable || defaultModes;
                if (availableModes.includes('forwardActiveEnergy')) {
                    fields.push({label: 'Forward active energy', name: 'fae'}); // i18n
                }
                if (availableModes.includes('reverseActiveEnergy')) {
                    fields.push({label: 'Reverse active energy', name: 'rae'}); // i18n
                }
                if (availableModes.includes('forwardActiveEnergyBalanced')) {
                    fields.push({label: 'Forward active energy balanced', name: 'fae_balanced'}); // i18n
                }
                if (availableModes.includes('reverseActiveEnergyBalanced')) {
                    fields.push({label: 'Reverse active energy balanced', name: 'rae_balanced'}); // i18n
                }
                return fields;
            },
            trigger: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            fieldName() {
                if (this.field.disablePhases) {
                    return this.field.name;
                } else if (this.field.name === 'voltage' && this.phase === 'all') {
                    return 'voltage_avg';
                } else if (this.phase === 'all') {
                    return `${this.field.name}_sum`
                } else {
                    return `${this.field.name}${this.phase}`;
                }
            },
            operators() {
                if (this.fieldName.startsWith('fae') || this.fieldName.startsWith('rae')) {
                    return ['gt', 'ge', 'eq'];
                } else {
                    return undefined;
                }
            }
        },
        watch: {
            value() {
                this.initFromValue();
            }
        }
    }
</script>
