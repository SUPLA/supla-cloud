<template>
    <span class="password-display"
        @click.stop="">
        <span class="password text-monospace"
            :class="uncovered ? 'uncovered' : ''">{{ password }}</span>
        <a @mousedown="uncovered = true"
            @touchstart="uncovered = true"
            @mouseleave="uncovered = false"
            @touchend="uncovered = false"
            @mouseup="uncovered = false"
            class="uncover-link">
            <i class="pe-7s-look"></i>
        </a>
    </span>
</template>

<script>
    export default {
        props: ['password'],
        data() {
            return {
                uncovered: false,
            };
        }
    };
</script>


<style lang="scss">
    @import "../../styles/variables";

    .password-display {
        .password {
            color: transparent;
            text-shadow: 0 0 15px rgba($supla-grey-dark, 50%);
            transition: all .3s;
            user-select: none;
            &.uncovered {
                color: inherit;
                text-shadow: 0 0 0px rgba($supla-grey-dark, 50%);
            }
        }
        .uncover-link {
            display: inline-block;
            margin-left: 5px;
        }
    }
</style>
