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
    import {formatDateForHtmlInput} from "../../../common/filters-date";
    import {DateTime} from "luxon";

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
                    const cronExpression = DateTime.fromISO(value).toFormat('m H d L * y');
                    this.$emit('input', cronExpression);
                },
                get() {
                    const date = this.value ? DateTime.fromFormat(this.value, 'm H d L * y') : DateTime.now().plus({minutes: 1});
                    return formatDateForHtmlInput(date.toISO());
                }
            },
            minDate() {
                return formatDateForHtmlInput(DateTime.now().toISO());
            },
        },
    };
</script>
