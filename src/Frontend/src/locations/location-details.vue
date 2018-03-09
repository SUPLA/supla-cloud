<template>
    <loading-cover :loading="loading"
        class="location-details">
        <div v-if="location">
            <form @submit.prevent="saveLocation()">
                <div class="container">
                    <div class="clearfix left-right-header">
                        <h2 class="no-margin-top">
                            {{ $t('Location') }}
                            ID{{ location.id }}
                        </h2>
                        <div class="btn-toolbar no-margin-top"
                            v-if="hasPendingChanges">
                            <a class="btn btn-grey"
                                v-if="hasPendingChanges"
                                @click="cancelChanges()">
                                <i class="pe-7s-back"></i>
                                {{ $t('Cancel changes') }}
                            </a>
                            <button class="btn btn-yellow btn-lg"
                                type="submit">
                                <i class="pe-7s-diskette"></i>
                                {{ $t('Save changes') }}
                            </button>
                        </div>
                        <div class="btn-toolbar no-margin-top"
                            v-else>
                            <a class="btn btn-danger"
                                @click="deleteConfirm = true">
                                {{ $t('Delete') }}
                            </a>
                        </div>
                        <div v-else></div>
                    </div>
                    <p>Nazwa, hasło, czy włączona</p>
                </div>
            </form>
            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteLocation()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure?')"
                :loading="loading">
                {{ $t('Confirm if you want to remove Location ID{locationId}. You will no longer be able to connect the i/o devices to this Location.', {locationId: location.id}) }}
            </modal-confirm>
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <h2>{{ $t('I/O Devices') }} ({{ location.ioDevices.length }})</h2>
                        <p>Będą tu</p>
                    </div>
                    <div class="col-sm-6">
                        <h2>{{ $t('Access Identifiers') }} ({{ location.accessIds.length }})</h2>
                        <table class="table table-hover"
                            v-if="location.accessIds.length">
                            <thead>
                            <th>ID</th>
                            <th>{{ $t('Password') }}</th>
                            <th>{{ $t('Caption') }}</th>
                            </thead>
                            <tbody>
                            <tr v-for="aid in location.accessIds">
                                <td>{{ aid.id }}</td>
                                <td>{{ aid.password }}</td>
                                <td>{{ aid.caption }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <empty-list-placeholder v-else></empty-list-placeholder>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <h2>{{ $t('Channel groups') }} ({{ location.channelGroups.length }})</h2>
                        <table class="table table-hover">
                            <thead>
                            <th></th>
                            <th>ID</th>
                            <th>{{ $t('Caption') }}</th>
                            <th>{{ $t('Channels no') }}</th>
                            </thead>
                            <tbody>
                            <tr v-for="channelGroup in location.channelGroups"
                                v-go-to-link-on-row-click>
                                <td>
                                    <function-icon :model="channelGroup"
                                        width="30"></function-icon>
                                </td>
                                <td>
                                    <a :href="'/channel-groups/' + channelGroup.id | withBaseUrl">{{ channelGroup.id }}</a>
                                </td>
                                <td>
                                    <span v-if="channelGroup.caption">{{ channelGroup.caption }}</span>
                                    <em v-else>{{ $t('None') }}</em>
                                </td>
                                <td>{{ channelGroup.channelIds.length }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-6">
                        <h2>{{ $t('Channels') }} (0)</h2>
                        <p>Będą tu</p>
                    </div>
                </div>
            </div>
        </div>
    </loading-cover>
</template>

<script>
    import Vue from "vue";
    import SquareLinksCarousel from "../common/tiles/square-links-carousel";
    import AccessIdTile from "src/access-ids/access-id-tile";
    import ChannelGroupTile from "src/channel-groups/channel-group-tile";
    import FunctionIcon from "../channels/function-icon";
    import EmptyListPlaceholder from "src/common/gui/empty-list-placeholder";

    Vue.component('AccessIdTile', AccessIdTile);
    Vue.component('ChannelGroupTile', ChannelGroupTile);

    export default {
        components: {
            FunctionIcon,
            SquareLinksCarousel,
            EmptyListPlaceholder
        },
        props: ['model'],
        data() {
            return {
                loading: false,
                location: undefined,
                deleteConfirm: false,
                hasPendingChanges: false
            };
        },
        mounted() {
            this.initForModel();
        },
        methods: {
            initForModel() {
                this.hasPendingChanges = false;
                this.loading = true;
                if (this.model.id) {
                    this.$http.get(`locations/${this.model.id}?include=iodevices,channelGroups,accessids,password`)
                        .then(response => this.location = response.body)
                        .finally(() => this.loading = false);
                } else {
                    this.$http.post('locations', {}).then(response => this.$emit('add', response.body)).catch(() => this.$emit('delete'));
                }
            },
            saveLocation() {
                const toSend = Vue.util.extend({}, this.location);
                this.loading = true;
                this.$http.put('locations/' + this.location.id, toSend)
                    .then(response => this.$emit('update', response.body))
                    .finally(() => this.loading = this.hasPendingChanges = false);
            },
            deleteLocation() {
                this.loading = true;
                this.$http.delete('locations/' + this.location.id)
                    .then(() => this.$emit('delete'))
                    .then(() => this.location = undefined)
                    .catch(() => this.loading = false);
            }
        },
        watch: {
            model() {
                this.initForModel();
            }
        }
    };
</script>

<style scoped>

</style>
