<template>
    <div>
        <button :class="'devices-registration-button btn btn-outline btn-' + (enabledUntil ? 'orange' : 'grey')"
            type="button"
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
                        <span v-if="saving">{{ $t(captionI18n) }}</span>
                        <span v-else>{{ $t(captionI18n) }}:
                            <span class="big">{{ enabledUntil ? $t('ACTIVE') : $t('INACTIVE') }}</span></span>
                        <div v-if="enabledUntil">{{ $t('valid until') }}: {{ enabledUntilCalendar }}</div>
                        <div class="small text-muted"
                            v-if="!saving">{{ enabledUntil ? $t('CLICK TO DISABLE') : $t('CLICK TO ENABLE') }}
                        </div>
                    </td>
                </tr>
            </table>

        </button>
    </div>
</template>

<style lang="scss">
    .devices-registration-button {
        min-height: 62px;
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
    }
</style>

<script>
    import ButtonLoadingDots from "../../common/gui/loaders/button-loading-dots.vue";
    import {DateTime} from "luxon";

    export default {
        props: ['field', 'captionI18n'],
        components: {ButtonLoadingDots},
        data() {
            return {
                saving: false,
                enabledUntil: false,
            };
        },
        computed: {
            enabledUntilCalendar() {
                return this.enabledUntil ? DateTime.fromISO(this.enabledUntil).toLocaleString(DateTime.DATETIME_SHORT) : '';
            }
        },
        mounted() {
            this.loadUserInfo();
        },
        methods: {
            toggle() {
                this.saving = true;
                this.$http.patch('users/current', {action: 'change:' + this.field, enable: !this.enabledUntil})
                    .then(({body}) => this.enabledUntil = body[this.field])
                    .finally(() => this.saving = false);
            },
            loadUserInfo() {
                this.saving = true;
                this.$http.get('users/current')
                    .then(({body}) => this.enabledUntil = body[this.field])
                    .finally(() => this.saving = false);
            }
        }
    };
</script>
