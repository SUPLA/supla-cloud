<template>
    <flipper :flipped="!!addingNewChannel">
        <square-link class="clearfix pointer black"
            slot="front">
            <a class="valign-center text-center"
                @click="addingNewChannel = true">
                <span>
                    <i class="pe-7s-plus"></i>
                    <span v-if="!channelGroup.id">{{ $t('Add first channel to set channel group function and save it') }}</span>
                    <span v-else>{{ $t('Add new channel to this group') }}</span>
                </span>
            </a>
        </square-link>
        <square-link class="clearfix pointer black not-transform"
            slot="back">
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
                            <channels-dropdown :params="'io=output&hasFunction=1' + (channelGroup.function ? '&function=' + channelGroup.function.id : '')"
                                v-model="newChannel"
                                @update="channelsToChoose = $event"
                                hide-none="true"
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
    </flipper>
</template>

<script>
    import ChannelsDropdown from "src/devices/channels-dropdown.vue";

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
            }
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
