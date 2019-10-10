<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Some features depend on this device')">
        <p>{{ $t('Some of the features you have configured rely on channels from this device.') }}</p>
        <p v-if="dependencies.schedules.length > 0 || dependencies.directLinks.length > 0">
            {{ $t('The following items will be deleted with this device:') }}
        </p>
        <div class="row form-group">
            <div class="col-sm-6 col-12-if-alone"
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
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.directLinks.length > 0">
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
        <p v-if="dependencies.channelGroups.length > 0 || dependencies.sceneOperations.length > 0">
            {{ $t('The following items use the channels of these device. These references will be also removed.') }}
        </p>
        <div class="row">
            <div class="col-sm-6"
                v-if="dependencies.channelGroups.length > 0">
                <h5>{{ $t('Channel groups') }}</h5>
                <ul>
                    <li v-for="group in dependencies.channelGroups">
                        <div class="checkbox">
                            ID{{ group.id }}
                            <span class="small">{{ group.caption }}</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6"
                v-if="dependencies.sceneOperations.length > 0">
                <h5>{{ $t('Scenes') }}</h5>
                <ul>
                    <li v-for="sceneOperation in dependencies.sceneOperations">
                        <div class="checkbox">
                            ID{{ sceneOperation.owningSceneId }}
                            <span class="small">{{ sceneOperation.owningScene.caption }}</span>
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
