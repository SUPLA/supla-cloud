<template>
    <confirm-modal @confirm="enableSchedules()"
        @cancel="$emit('cancel')">
        <h4 slot="header">{{ $t('Existing schedules') }}</h4>
        {{ $t('Please select the schedules that should also be enabled.') }}
        <ul>
            <li v-for="schedule in schedules">
                <div class="checkbox">
                    <label>
                        <input type="checkbox"
                            :value="schedule.id"
                            v-model="schedulesToEnable">
                        {{ $t('Schedule') }} ID{{ schedule.id }}
                        <span class="small">{{ schedule.caption }}</span>
                    </label>
                </div>
            </li>
        </ul>
    </confirm-modal>
</template>

<script>
    import Vue from "vue";

    export default {
        props: ['schedules'],
        data(){
            return {
                schedulesToEnable: []
            }
        },
        methods: {
            enableSchedules() {
                if (this.schedulesToEnable.length > 0) {
                    Vue.http.patch('schedule', {enable: this.schedulesToEnable});
                }
                this.$emit('confirm', this.schedulesToEnable);
            }
        }
    }
</script>
