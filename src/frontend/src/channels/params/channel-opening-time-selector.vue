<template>
    <dl>
        <dd>{{ $t('Relay switching time') }}</dd>
        <dt>
            <div class="btn-group btn-group-flex"
                v-if="times.length < 5">
                <a :class="'btn ' + (value == time ? 'btn-green' : 'btn-default')"
                    v-for="time in times"
                    :key="time"
                    @click="$emit('input', time)">
                    {{ timeInSeconds(time) }}
                </a>
            </div>
            <div class="dropdown"
                v-else>
                <button class="btn btn-default dropdown-toggle btn-block btn-wrapped"
                    type="button"
                    data-toggle="dropdown">
                    {{ timeInSeconds(value) }}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li v-for="time in times"
                        :key="time">
                        <a @click="$emit('input', time)"
                            v-show="time != value">{{ timeInSeconds(time) }}</a>
                    </li>
                </ul>
            </div>
        </dt>
    </dl>
</template>

<script>
    export default {
        props: ['value', 'times'],
        methods: {
            timeInSeconds(time) {
                return (time / 1000) + ' ' + this.$t('sec.');
            }
        },
        mounted() {
            if (this.times.indexOf(this.value) < 0) {
                this.$emit('input', this.times[0]);
            }
        }
    };
</script>
