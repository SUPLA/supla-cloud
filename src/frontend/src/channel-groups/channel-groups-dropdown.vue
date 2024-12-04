<template>
    <div>
        <SelectForSubjects
            none-option
            :options="channelGroupsForDropdown"
            :caption="channelGroupCaption"
            :option-html="channelGroupHtml"
            :choose-prompt-i18n="'choose the channel group'"
            v-model="chosenChannelGroup"/>
    </div>
</template>

<script>
    import {channelIconUrl} from "../common/filters";
    import SelectForSubjects from "@/devices/select-for-subjects.vue";

    export default {
        props: ['params', 'value', 'filter'],
        components: {SelectForSubjects},
        data() {
            return {
                channelGroups: undefined,
            };
        },
        mounted() {
            this.fetchChannelGroups();
        },
        methods: {
            fetchChannelGroups() {
                this.channelGroups = undefined;
                this.$http.get('channel-groups?' + (this.params || '')).then(({body: channelGroups}) => {
                    this.channelGroups = channelGroups;
                });
            },
            channelGroupCaption(channelGroup) {
                return channelGroup.caption || `ID${channelGroup.id} ${this.$t(channelGroup.function.caption)}`;
            },
            channelGroupHtml(channelGroup, escape) {
                return `<div>
                            <div class="subject-dropdown-option d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="my-1">
                                        <span class="line-clamp line-clamp-2">${escape(channelGroup.fullCaption)}</span>
                                        ${channelGroup.caption ? `<span class="small text-muted">ID${channelGroup.id} ${this.$t(channelGroup.function.caption)}</span>` : ''}
                                    </h5>
                                    <p class="line-clamp line-clamp-2 small mb-0 option-extra">${this.$t("No{'.'} of channels")}: ${channelGroup.relationsCount.channels}</p>
                                </div>
                                <div class="icon option-extra"><img src="${channelIconUrl(channelGroup)}"></div></div>
                            </div>
                        </div>`;
            },
        },
        computed: {
            channelGroupsForDropdown() {
                if (!this.channelGroups) {
                    return [];
                }
                const channelGroups = this.channelGroups.filter(this.filter || (() => true));
                this.$emit('update', channelGroups);
                return channelGroups;
            },
            chosenChannelGroup: {
                get() {
                    return this.value;
                },
                set(channelGroup) {
                    this.$emit('input', channelGroup);
                }
            },
        },
        watch: {
            params() {
                this.fetchChannelGroups();
            },
        }
    };
</script>
