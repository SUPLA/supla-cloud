<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Some features depend on this device')">
        <p>{{ $t('Some of the features you have configured rely on channels from this device.') }}</p>
        <p>{{ $t('The following items will be deleted with this device:') }}</p>
        <div class="row">
            <div class="col-sm-6"
                v-if="dependencies.schedules.length > 0">
                <h5>{{ $t('Schedules') }}</h5>
                <ul>
                    <li v-for="schedule in dependencies.schedules">
                        <div class="checkbox">
                            ID{{ schedule.id }}
                            <span class="small">{{ schedule.caption }}</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6"
                v-if="dependencies.schedules.length > 0">
                <h5>{{ $t('Direct links') }}</h5>
                <ul>
                    <li v-for="link in dependencies.directLinks">
                        <div class="checkbox">
                            ID{{ link.id }}
                            <span class="small">{{ link.caption }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </modal-confirm>
</template>

<script>
    export default {
        props: ['dependencies'],
        data() {
            return {};
        },
        methods: {}
    };
</script>
