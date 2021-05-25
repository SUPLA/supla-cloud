<template>
    <div class="form-group form-group-lg">
        <label>{{ $t('Interval') }}</label>
        <div class="input-group">
            <span class="input-group-addon">{{ $t('Every') }}</span>
            <input
                type="number"
                class="form-control"
                :step="multiplier === 1 ? 5 : 1"
                :min="multiplier === 1 ? 5 : 1"
                max="1000"
                maxlength="3"
                v-model="minutes"
                @change="updateTimeExpression()">
            <span class="input-group-btn">
                <a @click="changeMultiplier()"
                    class="btn btn-default btn-lg">
                    <span v-if="multiplier === 1">{{ $t('minutes') }}</span>
                    <span v-if="multiplier === 60">{{ $t('hours') }}</span>
                    <span v-if="multiplier === 1440">{{ $t('days') }}</span>
                </a>
            </span>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['value'],
        data() {
            return {
                minutes: 5,
                multiplier: 1,
            };
        },
        methods: {
            updateTimeExpression() {
                let minutes = this.minutes * this.multiplier;
                if (this.multiplier === 1) {
                    minutes = Math.min(Math.max(this.roundTo5(minutes), 5), 300);
                    this.minutes = minutes;
                }
                const cronExpression = '*/' + minutes + ' * * * *';
                this.$emit('input', cronExpression);
            },
            roundTo5(int) {
                return Math.round(Math.floor(int / 5) * 5);
            },
            changeMultiplier() {
                if (this.multiplier === 1) {
                    this.multiplier = 60;
                } else if (this.multiplier === 60) {
                    this.multiplier = 1440;
                } else {
                    this.multiplier = 1;
                }
                this.updateTimeExpression();
            },
        },
        mounted() {
            let currentMinutes = this.value && this.value.match(/^\*\/([0-9]+)/);
            if (currentMinutes) {
                this.minutes = +currentMinutes[1];
                if (this.minutes >= 60 && this.minutes % 60 === 0) {
                    this.multiplier = 60;
                    this.minutes /= 60;
                }
                if (this.minutes >= 24 && this.minutes % 24 === 0) {
                    this.multiplier = 1440;
                    this.minutes /= 24;
                }
            }
            this.updateTimeExpression();
        },
    };
</script>
