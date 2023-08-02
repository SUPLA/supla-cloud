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
            data-width="100%"
            :data-container="dropdownContainer"
            data-style="btn-default btn-wrapped"
            v-model="chosenChannelGroup"
            @change="$emit('input', chosenChannelGroup)">
            <option v-for="channelGroup in channelGroupsForDropdown"
                :key="channelGroup.id"
                :value="channelGroup"
                :data-content="channelGroupHtml(channelGroup)">
                {{ channelGroupCaption(channelGroup) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import "@/common/bootstrap-select";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {channelIconUrl} from "../common/filters";
    import $ from "jquery";

    export default {
        props: ['params', 'value', 'filter', 'dropdownContainer'],
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
                    this.initSelectPicker();
                });
            },
            channelGroupCaption(channelGroup) {
                return channelGroup.caption || `ID${channelGroup.id} ${this.$t(channelGroup.function.caption)}`;
            },
            channelGroupHtml(channelGroup) {
                let content = `<div class='subject-dropdown-option flex-left-full-width'>`
                    + `<div class="labels full"><h4>${this.channelGroupCaption(channelGroup)}`;
                if (channelGroup.caption) {
                    content += ` <span class='small text-muted'>ID${channelGroup.id} ${this.$t(channelGroup.function.caption)}</span>`;
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
                    if (!this.chosenChannelGroup) {
                        this.$emit('input');
                    }
                } else {
                    this.chosenChannelGroup = undefined;
                }
            },
            initSelectPicker() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker(this.selectOptions));
            },
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
            },
            selectOptions() {
                return {
                    noneSelectedText: this.$t('choose the channel group'),
                    liveSearchPlaceholder: this.$t('Search'),
                    noneResultsText: this.$t('No results match {0}'),
                };
            },
        },
        watch: {
            value() {
                this.setChannelGroupFromModel();
                this.updateDropdownOptions();
            },
            params() {
                this.fetchChannelGroups();
            },
            '$i18n.locale'() {
                $(this.$refs.dropdown).selectpicker('destroy');
                this.initSelectPicker();
            },
        }
    };
</script>
