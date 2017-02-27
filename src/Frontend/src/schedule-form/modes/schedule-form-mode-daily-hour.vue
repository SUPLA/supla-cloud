<template>
    <div class="form-group">
        <div class="clockpicker" ref="clockpicker"></div>
    </div>
</template>

<script type="text/babel">
    import Vue from "vue";
    import {mapState} from "vuex";
    import moment from "moment";
    import {roundTime} from "../schedule-helpers";

    export default {
        name: 'schedule-form-mode-daily-hour',
        props: ['weekdays'],
        watch: {
            weekdays() {
                this.updateTimeExpression();
            }
        },
        methods: {
            updateTimeExpression() {
                let date = $(this.$refs.clockpicker).data('DateTimePicker').date();
                let time = moment(date).format('H:m');
                var timeParts = roundTime(time).split(':');
                var cronExpression = [timeParts[1], timeParts[0], '*', '*', this.weekdays].join(' ');
                this.$store.dispatch('updateTimeExpression', cronExpression);
            }
        },
        mounted() {
            $(this.$refs.clockpicker).datetimepicker({
                minDate: 'now',
                format: 'LT',
                inline: true,
                locale: Vue.config.lang,
                stepping: 5
            }).on("dp.change", () => this.updateTimeExpression());
            debugger;
            if (this.timeExpression) {
                let parts = this.timeExpression.split(' ');
                let currentDateFromExpression = moment().hour(parts[1]).minute(parts[0]);
                $(this.$refs.clockpicker).data('DateTimePicker').date(currentDateFromExpression);
            }
            this.updateTimeExpression();
        },
        computed: mapState(['timeExpression']),
    }
</script>
