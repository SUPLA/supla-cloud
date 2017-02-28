<template>
    <select data-placeholder="Wybierz strefę czasową" v-model="timezone" ref="dropdown">
        <option v-for="timezone in availableTimezones" :value="timezone.name">
            {{ timezone.name }}
            (UTC{{timezone.offset >= 0 ? '+' : ''}}{{timezone.offset}})
            {{ timezone.currentTime }}
        </option>
    </select>
</template>

<script type="text/babel">
    import Vue from "vue";
    import "chosen-js";
    import "bootstrap-chosen/bootstrap-chosen.css";

    //      editTimezone: function () {
    //        this.editingTimezone = true;
    //        var self = this;
    //        setTimeout(function () {
    //          $(self.$refs.timezoneDropdown).selectpicker({liveSearch: true});
    //        });
    //      },
    //      chooseTimezone: function () {
    //        $(this.$refs.timezoneDropdown).selectpicker('destroy');
    //        this.editingTimezone = false;
    //        moment.tz.setDefault(this.userTimezone);
    //        $.ajax({
    //          method: 'PUT',
    //          data: {timezone: this.userTimezone},
    //          url: BASE_URL + 'account/user-timezone'
    //        })
    //      },
    //      getAvailableTimezones: function () {

    export default {
        name: 'app',
        props: ['timezone'],
        mounted() {
            Vue.nextTick(() => $(this.$refs.dropdown).chosen());
//        .change((e) => {
//                this.channel = e.currentTarget.value;
//            <!--}));-->
        },
        computed: {
            availableTimezones() {
                return moment.tz.names().filter(function (timezone) {
                    return timezone.match(/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//);
                }).map(function (timezone) {
                    return {
                        name: timezone,
                        offset: moment.tz(timezone).utcOffset() / 60,
                        currentTime: moment.tz(timezone).format('H:mm')
                    }
                }).sort(function (timezone1, timezone2) {
                    if (timezone1.offset == timezone2.offset) {
                        return timezone1.name < timezone2.name ? -1 : 1;
                    } else {
                        return timezone1.offset - timezone2.offset
                    }
                });
            }
        }
    }
</script>

<style lang="scss" scoped>
    select {
        padding: 0;
    }
</style>
