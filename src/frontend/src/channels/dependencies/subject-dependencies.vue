<template>
    <div>
        <p v-if="dependencies.schedules.length > 0 || dependencies.directLinks.length > 0 || dependencies.reactions.length > 0 || (dependencies.ownReactions && dependencies.ownReactions.length)">
            <slot name="deletingHeader">{{ $t('The items below rely on this channel function, so they will be deleted.') }}</slot>
        </p>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.schedules.length > 0">
                <h5>{{ $t('Schedules') }}</h5>
                <ul>
                    <li v-for="schedule in dependencies.schedules"
                        :key="schedule.id">
                        ID{{ schedule.id }}
                        <span class="small">{{ schedule.caption }}</span>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.directLinks.length > 0">
                <h5>{{ $t('Direct links') }}</h5>
                <ul>
                    <li v-for="link in dependencies.directLinks"
                        :key="link.id">
                        ID{{ link.id }}
                        <span class="small">{{ link.caption }}</span>
                    </li>
                </ul>
            </div>
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.reactions.length > 0 || (dependencies.ownReactions && dependencies.ownReactions.length)">
                <h5>{{ $t('Reactions') }}</h5>
                <ul>
                    <li v-for="reaction in dependencies.reactions"
                        :key="reaction.id">
                        ID{{ reaction.owningChannelId }}
                        <span class="small">
                            {{ humanizeTrigger(reaction) }}
                            &raquo;
                            {{ $t(reaction.action.caption) }}
                        </span>
                    </li>
                    <li v-for="reaction in (dependencies.ownReactions || [])"
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
            v-if="(dependencies.channelGroups || []).length > 0 || dependencies.sceneOperations.length > 0 || dependencies.actionTriggers.length > 0">
            <slot name="removingHeader">{{ $t('Channel reference will be removed from the items below.') }}</slot>
        </p>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="(dependencies.channelGroups || []).length > 0">
                <h5>{{ $t('Channel groups') }}</h5>
                <ul>
                    <li v-for="group in dependencies.channelGroups"
                        :key="group.id">
                        ID{{ group.id }}
                        <span class="small">{{ group.caption }}</span>
                    </li>
                </ul>
            </div>
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
        </div>
        <div class="row">
            <div class="col-sm-6 col-12-if-alone"
                v-if="dependencies.actionTriggers.length > 0">
                <h5>{{ $t('Action triggers') }}</h5>
                <ul>
                    <li v-for="channel in dependencies.actionTriggers"
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
    import {triggerHumanizer} from "@/channels/reactions/trigger-humanizer";

    export default {
        props: ['dependencies'],
        methods: {
            humanizeTrigger(reaction) {
                return triggerHumanizer(reaction.functionId, reaction.trigger, this);
            }
        },
        computed: {
            dependentScenes() {
                return [...new Map(this.dependencies.sceneOperations.map(item => [item.owningSceneId, item.owningScene])).values()];
            },
        }
    };
</script>
