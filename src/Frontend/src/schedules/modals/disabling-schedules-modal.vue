<template>
    <confirm-modal @confirm="$emit('confirm')"
        @cancel="$emit('cancel')">
        <h4 slot="header">{{ $t('Existing schedules') }}</h4>
        {{ $t(message) }}
        <ul>
            <li v-for="schedule in schedules">
                {{ $t('Schedule') }} ID{{ schedule.id }}
                <span class="small">{{ schedule.caption }}</span>
            </li>
        </ul>
        {{ $t('Are you sure you want to disable this device?') }}
    </confirm-modal>
</template>

<script>
    export default {
        props: ['message', 'schedules']
    }
</script>
