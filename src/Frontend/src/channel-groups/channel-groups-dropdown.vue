<template>
    <div>
        <div class="select-loader"
            v-if="!channelGroups">
            <button-loading-dots></button-loading-dots>
        </div>
        <select class="selectpicker"
            :disabled="!channelGroups"
            ref="dropdown"
            data-live-search="true"
            :data-live-search-placeholder="$t('Search')"
            data-width="100%"
            :data-none-selected-text="$t('choose the channel group')"
            :data-none-results-text="$t('No results match {0}')"
            v-model="chosenChannelGroup"
            @change="$emit('input', chosenChannelGroup)">
            <option v-for="channelGroup in channelGroupsForDropdown"
                :value="channelGroup"
                :data-content="channelGroupHtml(channelGroup)">
                {{ channelGroupTitle(channelGroup) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {channelIconUrl} from "../common/filters";

    export default {
        props: ['params', 'value', 'filter'],
        components: {ButtonLoadingDots},
        data() {
            return {
                channelGroups: undefined,
                chosenChannelGroup: undefined
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
                    this.setChannelGroupFromModel();
                    Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
                });
            },
            channelGroupTitle(channelGroup) {
                return 'ID' + channelGroup.id + " " + this.$t(channelGroup.function.caption)
                    + (channelGroup.caption ? ` (${channelGroup.caption})` : '');
            },
            channelGroupHtml(channelGroup) {
                let content = `<div class='subject-dropdown-option flex-left-full-width'>`
                    + `<div class="labels full"><h4>ID${channelGroup.id} ${this.$t(channelGroup.function.caption)}`;
                if (channelGroup.caption) {
                    content += ` <span class='small text-muted'>${channelGroup.caption}</span>`;
                }
                content += '</h4>';
                content += `<p>${this.$t('No. of channels')}: ${channelGroup.relationsCount.channels}</p></div>`;
                content += `<div class="icon"><img src="${channelIconUrl(channelGroup)}"></div></div>`;
                return content;
            },
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
            },
            setChannelGroupFromModel() {
                if (this.value && this.channelGroups) {
                    this.chosenChannelGroup = this.channelGroups.filter(ch => ch.id == this.value.id)[0];
                } else {
                    this.chosenChannelGroup = undefined;
                }
            }
        },
        computed: {
            channelGroupsForDropdown() {
                this.updateDropdownOptions();
                if (!this.channelGroups) {
                    return [];
                }
                const channelGroups = this.channelGroups.filter(this.filter || (() => true));
                this.$emit('update', channelGroups);
                return channelGroups;
            }
        },
        watch: {
            value() {
                this.setChannelGroupFromModel();
                this.updateDropdownOptions();
            },
            params() {
                this.fetchChannelGroups();
            }
        }
    };
</script>
