<template>
    <div>
        <div class="form-group">
            <label v-if="isDelta(percentage)">{{ $t('Change percentage by') }}</label>
            <label v-else>{{ $t('Change percentage to') }}</label>
            <span class="input-group">
                <NumberDeltaInput v-model="percentage" @input="onChange()" :min="-100" :max="100"/>
                <span class="input-group-addon">%</span>
            </span>
        </div>
        <div class="form-group" v-if="hasTilt">
            <label v-if="isDelta(tilt)">{{ $t('Change tilt by') }}</label>
            <label v-else>{{ $t('Change tilt to') }}</label>
            <span class="input-group">
                <NumberDeltaInput v-model="tilt" @input="onChange()" :min="-100" :max="100"/>
                <span class="input-group-addon">%</span>
            </span>
        </div>
        <div class="small">
            {{ $t('You can set relative values here. For example +10 or -20 will make the setting larger by 10 or smaller by 20, respectively.') }}
        </div>
    </div>
</template>

<script>
    import NumberDeltaInput from "@/channels/action/number-delta-input.vue";
    import ChannelFunction from "@/common/enums/channel-function";

    export default {
        components: {NumberDeltaInput},
        props: {
            value: Object,
            subject: Object,
        },
        data() {
            return {
                percentage: '',
                tilt: '',
            };
        },
        mounted() {
            if (this.value) {
                this.percentage = this.value.percentage || '';
                this.tilt = this.value.tilt || '';
            }
            if (this.value.percentage === undefined) {
                this.$nextTick(() => this.onChange());
            }
        },
        methods: {
            onChange() {
                const data = {percentage: this.percentage};
                if (this.hasTilt) {
                    data.tilt = this.tilt;
                }
                this.$emit('input', data);
            },
            isDelta(value) {
                if (typeof value === 'string') {
                    return value[0] === '+' || value[0] === '-';
                } else {
                    return value < 0;
                }
            }
        },
        computed: {
            hasTilt() {
                return [ChannelFunction.CONTROLLINGTHEFACADEBLIND, ChannelFunction.VERTICAL_BLIND].includes(this.subject.functionId);
            }
        },
        watch: {
            value() {
                if (this.value && this.value.percentage === undefined) {
                    this.$nextTick(() => this.onChange());
                } else {
                    this.percentage = this.value?.percentage || '';
                    this.tilt = this.value?.tilt || '';
                }
            },
        },
    };
</script>
