<template>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label>Schedule mode</label>
                <div class="clearfix"></div>
                <schedule-mode-chooser></schedule-mode-chooser>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import ScheduleModeChooser from "./schedule-mode-chooser.vue"
    export default {
        name: 'schedule-form',
        data () {
            return {
                msg: 'SUPLA v2.0'
            }
        },
        components: {ScheduleModeChooser}
    }
</script>

<style lang="scss" scoped>
    h1 {
        color: red;
    }
</style>
