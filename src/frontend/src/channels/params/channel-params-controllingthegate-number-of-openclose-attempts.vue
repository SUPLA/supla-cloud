<template>
    <div>
        <dl>
            <dd v-tooltip="$t('After you request to open or close the gate, we will wait 60 seconds after each relay switch and see if the gate reached the desired state. In case of failure, we will retry the action and check again. Here you can limit the number of these attempts. Recommended value is 5. Change of this parameter does not influence schedules.')">
                {{ $t('Number of attempts to open or close') }}
                <i class="pe-7s-help1"></i>
            </dd>
            <dt>
                <div class="btn-group btn-group-flex">
                    <a :class="'btn ' + (numberOfAttempts == number ? 'btn-green' : 'btn-default')"
                        v-for="number in possibleNumbers"
                        :key="number"
                        @click="numberOfAttempts = number">
                        {{ number }}
                    </a>
                </div>
            </dt>
        </dl>
    </div>
</template>

<script>

    export default {
        props: ['channel'],
        data() {
            return {
                possibleNumbers: [1, 2, 3, 4, 5],
            };
        },
        computed: {
            numberOfAttempts: {
                get() {
                    return this.channel.config.numberOfAttemptsToOpenOrClose;
                },
                set(value) {
                    this.channel.config.numberOfAttemptsToOpenOrClose = value;
                    this.$emit('change');
                }
            }
        },
    };
</script>
