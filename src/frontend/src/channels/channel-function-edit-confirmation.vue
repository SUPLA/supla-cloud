<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Are you sure you want to change channel’s function?')">
        <p>{{ $t('Changing channel’s function will also result in the following changes.') }}</p>
        <div v-if="confirmationObject.groups.length">
            <strong>{{ $t('Channel will be deleted from the channel group below') }}</strong>
            <ul>
                <li v-for="group in confirmationObject.groups"
                    :key="group.id">
                    {{ $t('Channel group') }} ID{{ group.id }}
                    <span class="small">{{ group.caption }}</span>
                </li>
            </ul>
        </div>
        <div v-if="confirmationObject.schedules.length">
            <strong>{{ $t('Schedules below will be deleted') }}</strong>
            <ul>
                <li v-for="schedule in confirmationObject.schedules"
                    :key="schedule.id">
                    {{ $t('Schedule') }} ID{{ schedule.id }}
                    <span class="small">{{ schedule.caption }}</span>
                </li>
            </ul>
        </div>
        <div v-if="confirmationObject.directLinks.length">
            <strong>{{ $t('Direct links below will be deleted') }}</strong>
            <ul>
                <li v-for="directLink in confirmationObject.directLinks"
                    :key="directLink.id">
                    {{ $t('Direct link') }} ID{{ directLink.id }}
                    <span class="small">{{ directLink.caption }}</span>
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
