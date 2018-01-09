<template>
    <flipper :flipped="!!addingNewChannel">
        <square-link class="clearfix pointer black"
            slot="front">
            <a class="valign-center text-center"
                @click="addingNewChannel = true">
                <span>
                    <i class="pe-7s-plus"></i>
                    <span v-if="!channelGroup.id">{{ $t('Add the first channel to save the group') }}</span>
                    <span v-else>{{ $t('Add new channel to this group') }}</span>
                </span>
            </a>
        </square-link>
        <square-link class="clearfix pointer black not-transform"
            slot="back">
            <span class="valign-center text-center">
                <span>
                    <form @submit.prevent="addChannel()"
                        v-show="!channelsToChoose || channelsToChoose.length !== 0">
                        <div class="form-group">
                            <channels-dropdown :params="'include=iodevice,location,function,type&io=output&hasFunction=1' + (channelGroup.function ? '&function=' + channelGroup.function.id : '')"
                                v-model="newChannel"
                                @update="channelsToChoose = $event"
                                :hidden-channels="channelGroup.channels">
                            </channels-dropdown>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-default pull-left"
                                type="button"
                                @click="addingNewChannel = false">
                                Cancel
                            </button>
                            <button class="btn btn-green pull-right"
                                :disabled="!newChannel"
                                type="submit">
                                Add
                            </button>
                        </div>
                    </form>
                    <div v-show="channelsToChoose && channelsToChoose.length === 0">
                        <i class="pe-7s-paint-bucket"></i>
                        {{ $t('There are no more channels you can add to this group') }}
                    </div>
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
            }
        }
    };
</script>
