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
    import {mapActions, mapState} from "vuex";
    import BootstrapDateTimePicker from "eonasdan-bootstrap-datetimepicker";
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css';
    import moment from "moment";

    export default {
        name: 'schedule-form-mode-once',
        mounted() {
            $(this.$refs.datepicker).datetimepicker({
                minDate: 'now',
                locale: Vue.config.lang,
                stepping: 5,
                inline: true,
                sideBySide: true
            });
            $(this.$refs.datepicker).on("dp.change", () => this.updateTimeExpression());
            if (this.timeExpression) {
                let currentDateFromExpression = moment(this.timeExpression, 'm H D M * Y');
                $(this.$refs.datepicker).data('DateTimePicker').date(currentDateFromExpression);
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
    }
</script>
