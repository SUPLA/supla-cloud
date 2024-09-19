<template>
    <page-container :error="error">
        <loading-cover :loading="loading"
            class="location-details">
            <div v-if="location">
                <div class="container">
                    <pending-changes-page :header="$t('Location') + ' ID' + location.id"
                        @cancel="cancelChanges()"
                        @save="saveLocation()"
                        @delete="deleteConfirm = true"
                        :deletable="true"
                        :is-pending="hasPendingChanges">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-sm-8">
                                <div class="hover-editable hovered text-left">
                                    <dl>
                                        <dd>{{ $t('Enabled') }}</dd>
                                        <dt class="text-center">
                                            <toggler v-model="location.enabled"
                                                @input="locationChanged()"></toggler>
                                        </dt>
                                        <dd>{{ $t('Location name') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control"
                                                @keydown="locationChanged()"
                                                v-model="location.caption">
                                        </dt>
                                        <dd>{{ $t('Password') }}</dd>
                                        <dt>
                                            <password-display :password="location.password"
                                                editable="true"
                                                @change="updatePassword($event)"></password-display>
                                        </dt>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <h3>{{ $t('I/O Devices') }} ({{ location.ioDevices.length }})</h3>
                                <table class="table table-hover"
                                    v-if="location.ioDevices.length">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ $t('Name') }}</th>
                                        <th>{{ $t('Comment') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="ioDevice in location.ioDevices"
                                        :key="ioDevice.id"
                                        v-go-to-link-on-row-click>
                                        <td>
                                            <router-link :to="{name: 'device', params: {id: ioDevice.id}}">{{ ioDevice.id }}</router-link>
                                        </td>
                                        <td>{{ ioDevice.name }}</td>
                                        <td>{{ ioDevice.comment }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <empty-list-placeholder v-else
                                    class="inline"></empty-list-placeholder>
                            </div>
                            <div class="col-sm-6">
                                <h3>{{ $t('Access Identifiers') }} ({{ location.accessIds.length }})</h3>
                                <table class="table table-hover"
                                    v-if="location.relationsCount.accessIds > 0">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ $t('Password') }}</th>
                                        <th>{{ $t('Caption') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="aid in location.accessIds"
                                        :key="aid.id"
                                        v-go-to-link-on-row-click>
                                        <td>
                                            <router-link :to="{name: 'accessId', params: {id: aid.id}}">{{ aid.id }}</router-link>
                                        </td>
                                        <td>
                                            <password-display :password="aid.password"></password-display>
                                        </td>
                                        <td>{{ aid.caption }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <a @click="assignAccessIds = true">
                                    <i class="pe-7s-more"></i>
                                    {{ $t('Assign Access Identifiers') }}
                                </a>
                                <access-id-chooser v-if="assignAccessIds"
                                    title-i18n="Choose Access Identifiers to be assigned to this Location"
                                    :selected="location.accessIds"
                                    @cancel="assignAccessIds = false"
                                    @confirm="updateAccessIds($event)"></access-id-chooser>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <h3>{{ $t('Channel groups') }} ({{ location.channelGroups.length }})</h3>
                                <table class="table table-hover table-valign-middle"
                                    v-if="location.channelGroups.length">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>ID</th>
                                        <th>{{ $t('Caption') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="channelGroup in location.channelGroups"
                                        :key="channelGroup.id"
                                        v-go-to-link-on-row-click>
                                        <td style="width: 45px">
                                            <function-icon :model="channelGroup"></function-icon>
                                        </td>
                                        <td>
                                            <router-link :to="{name: 'channelGroup', params: {id: channelGroup.id}}">{{ channelGroup.id }}</router-link>
                                        </td>
                                        <td>
                                            <span v-if="channelGroup.caption">{{ channelGroup.caption }}</span>
                                            <em v-else>{{ $t('None') }}</em>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <empty-list-placeholder v-else
                                    class="inline"></empty-list-placeholder>
                            </div>
                            <div class="col-sm-6">
                                <h3>{{ $t('Channels') }} ({{ location.channels.length }})</h3>
                                <table class="table table-hover table-valign-middle"
                                    v-if="location.channels.length">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>ID</th>
                                        <th>{{ $t('Caption') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="channel in location.channels"
                                        :key="channel.id"
                                        v-go-to-link-on-row-click>
                                        <td style="width: 45px">
                                            <function-icon :model="channel"></function-icon>
                                        </td>
                                        <td>
                                            <router-link :to="{name: 'channel', params: {id: channel.id}}">{{ channel.id }}</router-link>
                                        </td>
                                        <td>{{ channelTitle(channel) }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <empty-list-placeholder v-else
                                    class="inline"></empty-list-placeholder>
                            </div>
                        </div>
                    </pending-changes-page>
                </div>
            </div>
            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteLocation()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure?')"
                :loading="loading">
                {{ $t('Confirm if you want to remove the Location ID{locationId}. You will no longer be able to connect the i/o devices to this Location.', {locationId: location.id}) }}
            </modal-confirm>
        </loading-cover>
    </page-container>
</template>

<script>
    import Vue from "vue";
    import FunctionIcon from "../channels/function-icon";
    import EmptyListPlaceholder from "../common/gui/empty-list-placeholder";
    import AccessIdChooser from "../access-ids/access-id-chooser";
    import Toggler from "../common/gui/toggler";
    import {channelTitle} from "../common/filters";
    import PasswordDisplay from "../common/gui/password-display";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PageContainer from "../common/pages/page-container";

    export default {
        components: {
            PageContainer,
            PendingChangesPage,
            PasswordDisplay,
            Toggler,
            AccessIdChooser,
            FunctionIcon,
            EmptyListPlaceholder
        },
        props: ['id'],
        data() {
            return {
                loading: false,
                location: undefined,
                error: false,
                deleteConfirm: false,
                hasPendingChanges: false,
                assignAccessIds: false
            };
        },
        mounted() {
            this.initForModel();
        },
        methods: {
            initForModel() {
                this.hasPendingChanges = false;
                this.loading = true;
                if (this.id && this.id != 'new') {
                    this.error = false;
                    this.$http.get(
                        `locations/${this.id}?include=iodevices,channelGroups,accessids,password,channels`,
                        {skipErrorHandler: [403, 404]}
                    )
                        .then(response => this.location = response.body)
                        .catch(response => this.error = response.status)
                        .finally(() => this.loading = false);
                } else {
                    this.$http.post('locations', {}).then(response => this.$emit('add', response.body)).catch(() => this.$emit('delete'));
                }
            },
            cancelChanges() {
                this.initForModel();
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
            },
            updateAccessIds(accessIds) {
                this.location.accessIds = accessIds;
                this.locationChanged();
                this.assignAccessIds = false;
            },
            updatePassword(password) {
                this.location.password = password;
                this.locationChanged();
            },
            locationChanged() {
                this.hasPendingChanges = true;
            },
            channelTitle(channel) {
                return channelTitle(channel).replace(/^ID[0-9]+ /, '');
            }
        },
        watch: {
            id() {
                this.initForModel();
            }
        }
    };
</script>
