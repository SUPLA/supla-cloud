<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Percentage') }}</label>
            <span class="input-group">
                <NumberDeltaInput v-model="percentage" @input="onChange()" :min="-100" :max="100"/>
                <span class="input-group-addon">%</span>
            </span>
        </div>
        <div class="form-group">
            <label>{{ $t('Tilt') }}</label>
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

    export default {
        components: {NumberDeltaInput},
        props: {
            value: Object,
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
            }
            if (this.value.percentage === undefined) {
                this.$nextTick(() => this.onChange());
            }
        },
        methods: {
            onChange() {
                this.$emit('input', {percentage: this.percentage, tilt: this.tilt});
            },
        },
        watch: {
            // value() {
            //     if (this.value && this.value.percentage === undefined) {
            //         this.$nextTick(() => this.onChange());
            //     } else {
            //         this.percentage = this.value?.percentage || 0;
            //     }
            // },
        },
    };
</script>
