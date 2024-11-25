<template>
    <div>
        <p v-if="dependentSchedules.length > 0 || dependentDirectLinks.length > 0 || dependentReactions.length > 0 || dependentOwnReactions.length">
            <slot name="deletingHeader">{{ $t('The items below depend on this channel function, so they will be deleted.') }}</slot>
        </p>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependentSchedules.length > 0">
                <h5>{{ $t('Schedules') }}</h5>
                <ul>
                    <li v-for="schedule in dependentSchedules"
                        :key="schedule.id">
                        ID{{ schedule.id }}
                        <span class="small">{{ schedule.caption }}</span>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependentDirectLinks.length > 0">
                <h5>{{ $t('Direct links') }}</h5>
                <ul>
                    <li v-for="link in dependentDirectLinks"
                        :key="link.id">
                        ID{{ link.id }}
                        <span class="small">{{ link.caption }}</span>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependentReactions.length > 0 || dependentOwnReactions.length > 0">
                <h5>{{ $t('Reactions') }}</h5>
                <ul>
                    <li v-for="reaction in dependentReactions"
                        :key="reaction.id">
                        ID{{ reaction.owningChannelId }}
                        <span class="small">
                            {{ humanizeTrigger(reaction) }}
                            &raquo;
                            {{ $t(reaction.action.caption) }}
                        </span>
                    </li>
                    <li v-for="reaction in dependentOwnReactions"
                        :key="reaction.id">
                        ID{{ reaction.owningChannelId }}
                        <span class="small">
                            {{ humanizeTrigger(reaction) }}
                            &raquo;
                            {{ $t(reaction.action.caption) }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <p class="mt-3"
            v-if="dependentChannelGroups.length > 0 || dependentScenes.length > 0 || dependentActionTriggers.length > 0 || dependentChannels.length > 0">
            <slot name="removingHeader">{{ $t('Channel reference will be removed from the items below.') }}</slot>
        </p>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="(dependentChannels || []).length > 0">
                <h5>{{ $t('Channels') }}</h5>
                <ul>
                    <li v-for="channel in dependentChannels" :key="channel.id">
                        {{ channelCaption(channel) }}
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependentChannelGroups.length > 0">
                <h5>{{ $t('Channel groups') }}</h5>
                <ul>
                    <li v-for="group in dependentChannelGroups"
                        :key="group.id">
                        ID{{ group.id }}
                        <span class="small">{{ group.caption }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependentScenes.length > 0">
                <h5>{{ $t('Scenes') }}</h5>
                <ul>
                    <li v-for="scene in dependentScenes"
                        :key="scene.id">
                        ID{{ scene.id }}
                        <span class="small">{{ scene.caption }}</span>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependentActionTriggers.length > 0">
                <h5>{{ $t('Action triggers') }}</h5>
                <ul>
                    <li v-for="channel in dependentActionTriggers"
                        :key="channel.id">
                        ID{{ channel.id }}
                        <span class="small">{{ channel.caption }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import {reactionTriggerCaption} from "@/channels/reactions/channel-function-triggers";
    import {channelTitle} from "@/common/filters";

    export default {
        props: ['dependencies'],
        methods: {
            humanizeTrigger(reaction) {
                return reactionTriggerCaption(reaction);
            },
            channelCaption(channel) {
                return channelTitle(channel);
            }
        },
        computed: {
            dependentScenes() {
                return [...new Map(this.dependentSceneOperations.map(item => [item.owningSceneId, item.owningScene])).values()];
            },
            dependentSceneOperations() {
                return this.dependencies.sceneOperations || [];
            },
            dependentSchedules() {
                return this.dependencies.schedules || [];
            },
            dependentDirectLinks() {
                return this.dependencies.directLinks || [];
            },
            dependentReactions() {
                return this.dependencies.reactions || [];
            },
            dependentOwnReactions() {
                return this.dependencies.ownReactions || [];
            },
            dependentChannelGroups() {
                return this.dependencies.channelGroups || [];
            },
            dependentActionTriggers() {
                return this.dependencies.actionTriggers || [];
            },
            dependentChannels() {
                return this.dependencies.channels || [];
            },
        }
    };
</script>
