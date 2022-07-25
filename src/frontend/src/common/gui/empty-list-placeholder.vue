<template>
    <div class="text-center empty-list-placeholder">
        <i class="pe-7s-paint-bucket"></i>
        <span class="empty">{{ $t('Empty!') }}</span>
    </div>
</template>

<style lang="scss">
    .empty-list-placeholder {
        i {
            display: block;
            font-size: 4em;
            margin-top: 15px;
        }
        .empty {
            font-size: 2em;
        }
        &.inline {
            i {
                display: none;
            }
            .empty {
                font-size: 1em;
                font-style: italic;
            }
        }
    }
</style>

<script>
    export default {};
</script>
