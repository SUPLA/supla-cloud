<template>
    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <div class="once-datepicker"></div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import Vue from "vue";
    import {mapMutations, mapState} from "vuex";
    import BootstrapDateTimePicker from "eonasdan-bootstrap-datetimepicker";
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css';
    import moment from "moment";

    export default {
        name: 'schedule-form-mode-once',
        data () {
            return {}
        },
        mounted() {
            let datepicker = $(this.$el).find('.once-datepicker');
            datepicker.datetimepicker({
                minDate: 'now',
                locale: Vue.config.lang,
                stepping: 5,
                inline: true,
                sideBySide: true
            });
            datepicker.on("dp.change", (e) => {
                let cronExpression = moment(e.date).format('m H D M * Y');
                this.updateTimeExpression(cronExpression);
            });
            if (this.scheduleMode == 'once' && this.timeExpression) {
                datepicker.data('DateTimePicker').date(moment(this.timeExpression, 'm H D M * Y'));
            }
        },
        computed: mapState(['scheduleMode', 'timeExpression']),
        methods: mapMutations(['updateTimeExpression'])
    }
</script>
