<template>
    <div>
        <p v-if="dependencies.schedules.length > 0 || dependencies.directLinks.length > 0">
            <slot name="deletingHeader">{{ $t('The items below rely on this channel function, so they will be deleted.') }}</slot>
        </p>
        <div class="row form-group">
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.schedules.length > 0">
                <h5>{{ $t('Schedules') }}</h5>
                <ul>
                    <li v-for="schedule in dependencies.schedules"
                        :key="schedule.id">
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
                    <li v-for="link in dependencies.directLinks"
                        :key="link.id">
                        <div class="checkbox">
                            ID{{ link.id }}
                            <span class="small">{{ link.caption }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <p v-if="dependencies.channelGroups.length > 0 || dependencies.sceneOperations.length > 0">
            <slot name="removingHeader">{{ $t('Channel reference will be removed from the items below.') }}</slot>
        </p>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.channelGroups.length > 0">
                <h5>{{ $t('Channel groups') }}</h5>
                <ul>
                    <li v-for="group in dependencies.channelGroups"
                        :key="group.id">
                        <div class="checkbox">
                            ID{{ group.id }}
                            <span class="small">{{ group.caption }}</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.sceneOperations.length > 0">
                <h5>{{ $t('Scenes') }}</h5>
                <ul>
                    <li v-for="sceneOperation in dependencies.sceneOperations"
                        :key="sceneOperation.id">
                        <div class="checkbox">
                            ID{{ sceneOperation.owningSceneId }}
                            <span class="small">{{ sceneOperation.owningScene.caption }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.actionTriggers.length > 0">
                <h5>{{ $t('Action triggers') }}</h5>
                <ul>
                    <li v-for="channel in dependencies.actionTriggers"
                        :key="channel.id">
                        <div class="checkbox">
                            ID{{ channel.id }}
                            <span class="small">{{ channel.caption }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['dependencies']
    };
</script>
