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
            <div class="form-group d-flex align-items-center" v-if="!field.disablePhases">
                <label class="flex-grow-1 pr-3">{{ $t('Phase') }}</label>
                <div class="btn-group btn-group-flex">
                    <a :class="'btn ' + ((phase === p) ? 'btn-green' : 'btn-default')"
                        v-for="p in availablePhases"
                        :key="p"
                        @click="phase = p">
                        {{ p === 'all' ? $t('all') : p }}
                    </a>
                </div>
            </div>
        </transition-expand>
        <ReactionConditionThreshold v-if="field" v-model="trigger" :field="fieldName" :default-threshold="230"
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
        },
        data() {
            return {
                fields: [
                    {label: 'voltage', name: 'voltage',}, // i18n
                    {label: 'current', name: 'current',}, // i18n
                    {label: 'power active', name: 'power_active'}, // i18n
                    {label: 'power reactive', name: 'power_reactive'}, // i18n
                    {label: 'power apparent', name: 'power_apparent'}, // i18n
                    {label: 'forward active energy', name: 'fae'}, // i18n
                    {label: 'reverse active energy', name: 'rae'}, // i18n
                    {label: 'forward active energy balanced', name: 'fae_balanced', disablePhases: true}, // i18n
                    {label: 'reverse active energy balanced', name: 'rae_balanced', disablePhases: true}, // i18n
                ],
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
        },
        watch: {
            value() {
                this.initFromValue();
            }
        }
    }
</script>

<style lang="scss">
    .reaction-condition-threshold {
        input[type=number] {
            width: 80px;
        }
    }
</style>
