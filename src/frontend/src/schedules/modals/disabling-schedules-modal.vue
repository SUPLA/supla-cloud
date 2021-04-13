<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Existing schedules')">
        {{ $t(messageI18n) }}
        <ul>
            <li v-for="schedule in schedules"
                :key="schedule.id">
                {{ $t('Schedule') }} ID{{ schedule.id }}
                <span class="small">{{ schedule.caption }}</span>
            </li>
        </ul>
        {{ $t('Are you sure you want to disable this device?') }}
    </modal-confirm>
</template>

<script>
    export default {
        props: ['messageI18n', 'schedules']
    };
</script>
