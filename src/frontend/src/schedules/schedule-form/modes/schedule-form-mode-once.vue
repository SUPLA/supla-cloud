<template>
    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <div ref="datepicker"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from "moment";
    import {Namespace, TempusDominus} from "@eonasdan/tempus-dominus";
    import "@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css";

    export default {
        props: ['value'],
        data() {
            return {
                picker: undefined,
            }
        },
        mounted() {
            this.picker = new TempusDominus(this.$refs.datepicker, {
                restrictions: {
                    minDate: moment().add(1, 'minute').toDate(),
                },
                localization: {
                    locale: this.$i18n.locale,
                },
                stepping: 1,
                display: {
                    inline: true,
                    sideBySide: true,
                }
            });
            if (this.value) {
                const currentDateFromExpression = moment(this.value, 'm H D M * Y');
                if (currentDateFromExpression.isAfter(moment())) {
                    this.picker.dates.setFromInput(currentDateFromExpression.toDate());
                }
            }
            this.updateTimeExpression();
            this.picker.subscribe(Namespace.events.change, () => this.updateTimeExpression());
        },
        beforeDestroy() {
            this.picker?.dispose();
        },
        methods: {
            updateTimeExpression() {
                const date = this.picker.viewDate;
                const cronExpression = moment(date).format('m H D M * Y');
                this.$emit('input', cronExpression);
            }
        }
    };
</script>
