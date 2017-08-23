<template>
    <div>
        <button :class="'btn btn-outline btn-' + (enabledUntil ? 'green' : 'black')"
            @click="toggle()"
            :disabled="saving">
            <table class="table">
                <tr>
                    <td>
                        <button-loading-dots v-if="saving"></button-loading-dots>
                        <i v-else
                            :class="enabledUntil ? 'pe-7s-simple-check' : 'pe-7s-close-circle'"></i>
                    </td>
                    <td>
                        Rejestracja klientów: <span class="big">{{ enabledUntil ? 'AKTYWNA' : 'NIEAKTYWNA' }}</span>
                        <div v-if="enabledUntil">wygaśnie: {{ enabledUntilCalendar }}</div>
                        <div class="small text-muted">kliknij, by {{ enabledUntil ? 'wyłączyć' : 'włączyć' }}</div>
                    </td>
                </tr>
            </table>

        </button>
    </div>
</template>

<style lang="scss">
    .btn {
        transition: all .2s;
    }

    .table {
        margin: 0;
    }

    i {
        font-size: 3em;
        margin-right: 10px;
    }

    label {
        vertical-align: super;
        margin: 0;
    }

    .vue-switcher {
        vertical-align: text-top;
    }

    .help-block {
        font-size: .7em;
    }
</style>

<script>
    import Switches from "vue-switches";
    import ButtonLoadingDots from "../common/button-loading-dots.vue";

    export default {
        components: {Switches, ButtonLoadingDots},
        data() {
            return {
                saving: false,
                enabledUntil: false,
            };
        },
        computed: {
            enabledUntilCalendar() {
                return this.enabledUntil ? moment(this.enabledUntil).calendar() : '';
            }
        },
        mounted() {
            this.loadUserInfo();
        },
        methods: {
            toggle() {
                this.saving = true;
                let promise;
                if (this.enabledUntil) {
                    promise = this.$http.patch('account/current', {action: 'disableClientRegistration'});
                } else {
                    promise = this.$http.patch('account/current', {action: 'enableClientRegistration'});
                }
                promise
                    .then(({body}) => this.enabledUntil = body.clientRegistrationEnabled)
                    .finally(() => this.saving = false);
            },
            loadUserInfo() {
                this.saving = true;
                this.$http.get('account/current')
                    .then(({body}) => this.enabledUntil = body.clientRegistrationEnabled)
                    .finally(() => this.saving = false);
            }
        }
    };
</script>
