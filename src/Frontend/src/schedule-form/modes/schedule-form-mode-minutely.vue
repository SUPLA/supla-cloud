<template>
    <div class="form-group form-group-lg">
        <label>{{ $t('Interval') }}</label>
        <div class="input-group">
            <span class="input-group-addon">{{ $t('Every') }}</span>
            <input type="number" class="form-control" step="5" min="5" max="30" maxlength="2"
                   v-model="minutes" @change="updateTimeExpression()">
            <span class="input-group-addon">{{ $t('minutes') }}</span>
        </div>
    </div>
</template>

<script type="text/babel">
    import {roundTo5} from "../schedule-helpers";
    import {mapState} from "vuex";
    export default {
        name: 'schedule-form-mode-minutely',
        data() {
            return {
                minutes: 5,
            }
        },
        methods: {
            updateTimeExpression() {
                let minutes = Math.min(Math.max(roundTo5(this.minutes), 5), 30);
                if (minutes == 25) { // cron expression does not understand "every 25 minutes"
                    minutes = 30;
                }
                this.minutes = minutes;
                let cronExpression = '*/' + this.minutes + ' * * * *';
                this.$store.dispatch('updateTimeExpression', cronExpression);
            }
        },
        mounted() {
            console.log('mounted');
            let currentMinutes = this.timeExpression.match(/^\*\/([01235]+)/);
            if (currentMinutes) {
                this.minutes = +currentMinutes[1];
            }
            this.updateTimeExpression(true);
        },
        computed: mapState(['timeExpression']),
    }
</script>
