<template>
    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <div ref="datepicker"></div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import Vue from "vue";
    import $ from "jquery";
    import {mapState} from "vuex";
    import moment from "moment";

    export default {
        name: 'schedule-form-mode-once',
        mounted() {
            let datepicker = $(this.$refs.datepicker);
            datepicker.datetimepicker({
                minDate: moment().add(5, 'minute').toDate(),
                locale: Vue.config.lang,
                stepping: 5,
                inline: true,
                sideBySide: true
            }).on("dp.change", () => this.updateTimeExpression());
            if (this.timeExpression) {
                let currentDateFromExpression = moment(this.timeExpression, 'm H D M * Y');
                if (currentDateFromExpression.year() < 2010) {
                    this.updateTimeExpression();
                } else {
                    datepicker.data('DateTimePicker').date(currentDateFromExpression);
                }
            } else {
                this.updateTimeExpression();
            }
        },
        computed: mapState(['timeExpression']),
        methods: {
            updateTimeExpression() {
                let date = $(this.$refs.datepicker).data('DateTimePicker').date();
                let cronExpression = moment(date).format('m H D M * Y');
                this.$store.dispatch('updateTimeExpression', cronExpression);
            }
        }
    };
</script>
