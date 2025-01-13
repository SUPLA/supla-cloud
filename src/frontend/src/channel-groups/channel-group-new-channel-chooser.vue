<template>
    <flipper :flipped="!!addingNewChannel">
        <template #front>
            <square-link class="clearfix pointer black">
                <a class="valign-center text-center"
                    @click="addingNewChannel = true">
                    <span>
                        <i class="pe-7s-plus"></i>
                        <span v-if="!channelGroup.id">{{ $t('Add first channel to set channel group function and save it') }}</span>
                        <span v-else>{{ $t('Add new channel to this group') }}</span>
                    </span>
                </a>
            </square-link>
        </template>
        <template #back>
            <square-link class="clearfix pointer black not-transform">
                <span class="valign-center text-center">
                    <span>
                        <div v-if="$user.userData.limits.channelPerGroup <= channelGroup.channels.length"
                            @click="addingNewChannel = false">
                            <i class="pe-7s-close-circle"></i>
                            {{ $t('Limit has been exceeded') }}
                        </div>
                        <div v-else-if="channelsToChoose && channelsToChoose.length === 0"
                            @click="addingNewChannel = false">
                            <i class="pe-7s-paint-bucket"></i>
                            {{ $t('There are no more channels you can add to this group') }}
                        </div>
                        <form @submit.prevent="addChannel()"
                            v-else>
                            <div class="form-group">
                                <channels-dropdown
                                    :params="'io=output&hasFunction=1' + (channelGroup.function ? '&function=' + channelGroup.function.id : '')"
                                    v-model="newChannel"
                                    @update="channelsToChoose = $event"
                                    hide-none="true"
                                    :filter="filterOutChannelsNotForChannelGroup"
                                    :hidden-channels="channelGroup.channels">
                                </channels-dropdown>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-default pull-left"
                                    type="button"
                                    @click="addingNewChannel = false">
                                    {{ $t('Cancel') }}
                                </button>
                                <button class="btn btn-green pull-right"
                                    :disabled="!newChannel"
                                    type="submit">
                                    {{ $t('Add') }}
                                </button>
                            </div>
                        </form>
                    </span>
                </span>
            </square-link>
        </template>
    </flipper>
</template>

<script>
    import ChannelFunction from "@/common/enums/channel-function";
    import ChannelsDropdown from "../devices/channels-dropdown.vue";

    export default {
        props: ['channelGroup'],
        components: {ChannelsDropdown},
        data() {
            return {
                newChannel: undefined,
                addingNewChannel: false,
                channelsToChoose: undefined
            };
        },
        methods: {
            addChannel() {
                if (this.newChannel) {
                    this.channelGroup.channels.push(this.newChannel);
                    this.addingNewChannel = false;
                    this.$emit('add', this.newChannel);
                    this.newChannel = undefined;
                }
            },
            filterOutChannelsNotForChannelGroup(channel) {
                const nonGroupingFunctions = [
                    ChannelFunction.DIGIGLASS_VERTICAL,
                    ChannelFunction.DIGIGLASS_HORIZONTAL,
                    ChannelFunction.HVAC_THERMOSTAT,
                    ChannelFunction.HVAC_DOMESTIC_HOT_WATER,
                    ChannelFunction.HVAC_THERMOSTAT_DIFFERENTIAL,
                    ChannelFunction.HVAC_THERMOSTAT_HEAT_COOL,
                    ChannelFunction.THERMOSTAT,
                ];
                return !nonGroupingFunctions.includes(channel.function.id);
            },
        },
        watch: {
            channelGroup() {
                this.addingNewChannel = false;
            },
            'channelGroup.channels.length'() {
                this.channelsToChoose = undefined;
            }
        }
    };
</script>
