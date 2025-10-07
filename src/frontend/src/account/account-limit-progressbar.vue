<template>
    <div :class="'progress-bar account-limit-progressbar progress-bar-' + progressBarClass"
        role="progressbar"
        aria-valuemin="0"
        :aria-valuenow="value"
        :aria-valuemax="limit"
        :style="{width: progress + '%'}">
        <slot>
            {{ value }}/{{ limit }}
        </slot>
    </div>
</template>

<script>
    export default {
        props: ['value', 'limit'],
        computed: {
            progress() {
                return this.value * 100 / this.limit;
            },
            progressBarClass() {
                if (this.progress < 50) {
                    return 'success';
                } else if (this.progress < 80) {
                    return 'warning';
                } else {
                    return 'danger';
                }
            }
        }
    };
</script>

<style lang="scss">
    .account-limit-progressbar {
        min-width: 4em;
    }
</style>
