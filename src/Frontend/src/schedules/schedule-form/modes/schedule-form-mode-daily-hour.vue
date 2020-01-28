<template>
    <div class="form-group">
        <div class="clockpicker"
            ref="clockpicker"></div>
    </div>
</template>

<script>
    import Vue from "vue";
    import moment from "moment";
    import $ from "jquery";

    export default {
        props: ['weekdays', 'value'],
        watch: {
            weekdays() {
                this.updateTimeExpression();
            }
        },
        methods: {
            updateTimeExpression() {
                const date = $(this.$refs.clockpicker).data('DateTimePicker').date();
                const time = moment(date).format('H:m');
                const timeParts = this.roundTime(time).split(':');
                const cronExpression = [timeParts[1], timeParts[0], '*', '*', this.weekdays].join(' ');
                this.$emit('input', cronExpression);
            },
            roundTime(time) {
                const parts = time.split(':');
                if (parts.length != 2) {
                    return '0:0';
                }
                parts[1] = Math.min(Math.round(parts[1] / 5) * 5, 55);
                if (parts[1] < 10) parts[1] = '0' + parts[1];
                return parts.join(':');
            },
        },
        mounted() {
            $(this.$refs.clockpicker).datetimepicker({
                format: 'LT',
                inline: true,
                locale: Vue.config.lang,
                stepping: 5
            }).on("dp.change", () => this.updateTimeExpression());
            if (this.value) {
                const parts = this.value.split(' ');
                const currentDateFromExpression = moment().add(1, 'day').hour(parts[1]).minute(parts[0]);
                $(this.$refs.clockpicker).data('DateTimePicker').date(currentDateFromExpression);
            } else {
                this.updateTimeExpression();
            }
        }
    };
</script>
