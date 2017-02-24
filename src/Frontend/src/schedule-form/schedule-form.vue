<template>
    <div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label>{{ $t("Schedule mode") }}</label>
                    <div class="clearfix"></div>
                    <schedule-mode-chooser></schedule-mode-chooser>
                </div>
            </div>
        </div>
        <div class="row" v-show="scheduleMode">
            <div class="col-md-6">
                <div class="well">
                    <h3 class="no-margin-top">{{ $t('When') }}?</h3>
                    <div class="form-group">
                        <schedule-form-mode-once v-show="scheduleMode == 'once'"></schedule-form-mode-once>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import ScheduleModeChooser from "./schedule-mode-chooser.vue"
    import ScheduleFormModeOnce from "./modes/schedule-form-mode-once.vue"
    import {mapState} from "vuex";
    export default {
        name: 'schedule-form',
        data () {
            return {
                msg: 'SUPLA v2.0'
            }
        },
        computed: mapState(['scheduleMode']),
        components: {ScheduleModeChooser, ScheduleFormModeOnce}
    }
</script>

<style lang="scss" scoped>
    h1 {
        color: red;
    }
</style>
