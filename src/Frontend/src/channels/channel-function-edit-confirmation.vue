<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Are you sure you want to change the channel\'s function?')">
        <p>{{ $t('Changing the channel\'s function will also result in the following changes.') }}</p>
        <div v-if="confirmationObject.groups.length">
            <strong>{{ $t('The channel will be removed from the following channel groups') }}</strong>
            <ul>
                <li v-for="group in confirmationObject.groups">
                    {{ $t('Channel group') }} ID{{ group.id }}
                    <span class="small">{{ group.caption }}</span>
                </li>
            </ul>
        </div>
        <div v-if="confirmationObject.schedules.length">
            <strong>{{ $t('The following schedules will be deleted') }}</strong>
            <ul>
                <li v-for="schedule in confirmationObject.schedules">
                    {{ $t('Schedule') }} ID{{ schedule.id }}
                    <span class="small">{{ schedule.caption }}</span>
                </li>
            </ul>
        </div>
    </modal-confirm>
</template>

<script>
    export default {
        props: ['confirmationObject']
    };
</script>
