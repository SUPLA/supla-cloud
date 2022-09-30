<template>
    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <input type="datetime-local"
                    v-model="theDate"
                    :min="minDate"
                    class="form-control datetimepicker-start">
            </div>
        </div>
    </div>
</template>

<script>
    import moment from "moment";

    export default {
        props: ['value'],
        data() {
            return {
                picker: undefined,
            }
        },
        computed: {
            theDate: {
                set(value) {
                    const cronExpression = moment(value).format('m H D M * Y');
                    this.$emit('input', cronExpression);
                },
                get() {
                    return moment(this.value, 'm H D M * Y').format(moment.HTML5_FMT.DATETIME_LOCAL);
                }
            },
            minDate() {
                return moment().format(moment.HTML5_FMT.DATETIME_LOCAL);
            },
        },
    };
</script>
