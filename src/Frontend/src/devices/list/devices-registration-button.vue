<template>
    <div>
        <button :class="'btn btn-outline btn-' + (enabledUntil ? 'orange' : 'grey')"
            @click="toggle()"
            :disabled="saving">
            <table class="table">
                <tr>
                    <td>
                        <button-loading-dots v-if="saving"></button-loading-dots>
                        <i v-else
                            :class="enabledUntil ? 'pe-7s-attention' : 'pe-7s-close-circle'"></i>
                    </td>
                    <td>
                        <span v-if="saving">{{ $t(caption) }}</span>
                        <span v-else>{{ $t(caption) }}: <span class="big">{{ enabledUntil ? 'AKTYWNA' : 'NIEAKTYWNA' }}</span></span>
                        <div v-if="enabledUntil">{{ $t('will expire') }} : {{ enabledUntilCalendar }}</div>
                        <div class="small text-muted">{{ $t(enabledUntil ? 'CLICK TO DISABLE' : 'CLICK TO ENABLE') }}</div>
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
        props: ['field', 'caption'],
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
                this.$http.patch('account/current', {action: 'change:' + this.field, value: !!this.enabledUntil})
                    .then(({body}) => this.enabledUntil = body[this.field])
                    .finally(() => this.saving = false);
            },
            loadUserInfo() {
                this.saving = true;
                this.$http.get('account/current')
                    .then(({body}) => this.enabledUntil = body[this.field])
                    .finally(() => this.saving = false);
            }
        }
    };
</script>
