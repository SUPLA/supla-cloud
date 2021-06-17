<template>
    <div class="form-group form-group-lg">
        <label>{{ $t('Interval') }}</label>
        <div class="input-group">
            <span class="input-group-addon">{{ $t('Every') }}</span>
            <input type="number"
                class="form-control"
                step="5"
                min="5"
                max="300"
                maxlength="2"
                v-model="minutes"
                @change="updateTimeExpression()">
            <span class="input-group-addon">{{ $t('minutes') }}</span>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['value'],
        data() {
            return {
                minutes: 5,
            };
        },
        methods: {
            updateTimeExpression() {
                this.minutes = Math.min(Math.max(this.roundTo5(this.minutes), 5), 300);
                const cronExpression = '*/' + this.minutes + ' * * * *';
                this.$emit('input', cronExpression);
            },
            roundTo5(int) {
                return Math.round(Math.floor(int / 5) * 5);
            },
        },
        mounted() {
            let currentMinutes = this.value && this.value.match(/^\*\/([0-9]+)/);
            if (currentMinutes) {
                this.minutes = +currentMinutes[1];
            }
            this.updateTimeExpression();
        },
    };
</script>
