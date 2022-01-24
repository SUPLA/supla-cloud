<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <channel-action-executor v-if="subject"
                        :subject="subject"></channel-action-executor>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import ChannelActionExecutor from "../channels/action/channel-action-executor";

    export default {
        components: {ChannelActionExecutor},
        data() {
            return {
                subject: undefined,
            };
        },
        mounted() {
            this.$http.get('channels/7').then(({body}) => this.subject = body);
        }
    };
</script>
