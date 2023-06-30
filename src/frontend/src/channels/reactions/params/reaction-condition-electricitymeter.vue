<template>
    <div class="reaction-condition-em">
        <div class="form-group d-flex align-items-center">
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
        <div class="form-group">
            <div class="btn-group btn-group-flex flex-wrap">
                <a :class="'btn ' + ((field === f) ? 'btn-green' : 'btn-default')"
                    v-for="f in fields"
                    :key="f.name"
                    @click="field = f">
                    {{ $t(f.label) }}
                </a>
            </div>
        </div>
        <ReactionConditionThreshold v-if="field" v-model="trigger" :field="fieldName" :unit="field.unit" :default-threshold="230"/>
    </div>
</template>

<script>
    import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";

    export default {
        components: {ReactionConditionThreshold},
        props: {
            value: Object,
            subject: Object,
        },
        data() {
            return {
                fields: [
                    {label: 'voltage', name: 'voltage', unit: 'V'}, // i18n
                    {label: 'current', name: 'current', unit: 'A'}, // i18n
                    {label: 'power active', name: 'power_active', unit: 'W'}, // i18n
                    {label: 'power reactive', name: 'power_reactive', unit: 'var'}, // i18n
                    {label: 'power apparent', name: 'power_apparent', unit: 'VA'}, // i18n
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
                    this.field = this.fields.find(f => name.startsWith(f.name));
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
                if (this.field.name === 'voltage' && this.phase === 'all') {
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
