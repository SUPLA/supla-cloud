<template>
    <loading-cover :loading="loading">
        <div v-if="accessId"
            class="container">
            <pending-changes-page :header="$t('Access Identifier') + ' ID' + accessId.id"
                @cancel="cancelChanges()"
                @save="saveAccessId()"
                deletable="true"
                @delete="deleteConfirm = true"
                :is-pending="hasPendingChanges">

            </pending-changes-page>
        </div>

        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteAccessId()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure?')"
            :loading="loading">
            {{ $t('Confirm if you want to remove Access Identifier') }}
        </modal-confirm>
    </loading-cover>
</template>

<script>
    import Vue from "vue";
    import PendingChangesPage from "../common/pages/pending-changes-page";

    export default {
        components: {PendingChangesPage},
        props: ['model'],
        data() {
            return {
                loading: false,
                accessId: undefined,
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
                    this.$http.get(`accessids/${this.model.id}?include=locations,clientApps,password`)
                        .then(response => this.accessId = response.body)
                        .finally(() => this.loading = false);
                } else {
                    this.$http.post('accessids', {}).then(response => this.$emit('add', response.body)).catch(() => this.$emit('delete'));
                }
            },
            saveAccessId() {
                const toSend = Vue.util.extend({}, this.accessId);
                this.loading = true;
                this.$http.put('accessids/' + this.accessId.id, toSend)
                    .then(response => this.$emit('update', response.body))
                    .finally(() => this.loading = this.hasPendingChanges = false);
            },
            deleteAccessId() {
                this.loading = true;
                this.$http.delete('accessids/' + this.accessId.id)
                    .then(() => this.$emit('delete'))
                    .then(() => this.accessId = undefined)
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
