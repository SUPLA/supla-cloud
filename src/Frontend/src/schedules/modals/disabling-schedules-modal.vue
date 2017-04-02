<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Existing schedules')">
        {{ $t(message) }}
        <ul>
            <li v-for="schedule in schedules">
                {{ $t('Schedule') }} ID{{ schedule.id }}
                <span class="small">{{ schedule.caption }}</span>
            </li>
        </ul>
        {{ $t('Are you sure you want to disable this device?') }}
    </modal-confirm>
</template>

<script>
    export default {
        props: ['message', 'schedules']
    }
</script>
