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
        </div>
    </loading-cover>
</template>

<script>
    import Vue from "vue";

    export default {
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
                this.$http.get(`locations/${this.model.id}?include=iodevices,channelGroups,accessids`)
                    .then(response => this.location = response.body)
                    .finally(() => this.loading = false);
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
