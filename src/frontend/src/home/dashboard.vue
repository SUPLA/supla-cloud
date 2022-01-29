<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-lg-3"
                    v-for="subject in subjects"
                    :key="subject.id">
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
                subjects: [],
            };
        },
        mounted() {
            this.$http.get('channels/7').then(({body}) => this.subjects.push(body));
            this.$http.get('channels/115').then(({body}) => this.subjects.push(body));
            this.$http.get('channels/141').then(({body}) => this.subjects.push(body));
            this.$http.get('channels/162').then(({body}) => this.subjects.push(body));
        }
    };
</script>
