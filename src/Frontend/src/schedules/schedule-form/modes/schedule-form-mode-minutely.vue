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

<script type="text/babel">
    import {roundTo5} from "../schedule-helpers";
    import {mapState} from "vuex";

    export default {
        name: 'schedule-form-mode-minutely',
        data() {
            return {
                minutes: 5,
            };
        },
        methods: {
            updateTimeExpression() {
                this.minutes = Math.min(Math.max(roundTo5(this.minutes), 5), 300);
                let cronExpression = '*/' + this.minutes + ' * * * *';
                this.$store.dispatch('updateTimeExpression', cronExpression);
            }
        },
        mounted() {
            let currentMinutes = this.timeExpression.match(/^\*\/([0-9]+)/);
            if (currentMinutes) {
                this.minutes = +currentMinutes[1];
            }
            this.updateTimeExpression(true);
        },
        computed: mapState(['timeExpression']),
    };
</script>
