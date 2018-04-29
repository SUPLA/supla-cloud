<template>
    <div>
        <modal :header="$t('You will be logged out shortly')"
            v-if="idleWarningShown">
            <p>{{ $t('If you want to stay here, do something!') }}</p>
        </modal>
    </div>
</template>

<script>
    // idea based on http://stackoverflow.com/a/4029518/878514
    const LOGOUT_AFTER_IDLE_MINUTES = 15;

    export default {
        data() {
            return {
                idleTime: 0,
                incrementInterval: undefined,
                pingInterval: undefined,
            };
        },
        mounted() {
            this.incrementInterval = setInterval(() => this.incrementIdleTime(), 60000);
            this.pingInterval = setInterval(() => this.pingSession(), LOGOUT_AFTER_IDLE_MINUTES * 60000);
            $(document).click(() => this.clearIdleTime());
            $(document).keypress(() => this.clearIdleTime());
        },
        computed: {
            idleWarningShown() {
                return this.idleTime >= LOGOUT_AFTER_IDLE_MINUTES - 1;
            }
        },
        methods: {
            incrementIdleTime() {
                ++this.idleTime;
                if (this.idleTime >= LOGOUT_AFTER_IDLE_MINUTES) {
                    $("#logoutButton")[0].click();
                    this.stop();
                }
            },
            clearIdleTime() {
                this.idleTime = 0;
            },
            pingSession() {
                this.$http.get('server-info');
            },
            stop() {
                clearInterval(this.incrementInterval);
                clearInterval(this.pingInterval);
            }
        }
    };
</script>
