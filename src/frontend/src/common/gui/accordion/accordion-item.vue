<script setup>
    import {computed, inject} from "vue";
    import {faChevronRight} from "@fortawesome/free-solid-svg-icons";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    const props = defineProps({
        titleI18n: String,
        name: {
            type: String,
            default: () => self.crypto.randomUUID(),
        },
    });

    const accordion = inject("accordion");

    const isOpen = computed(() => accordion.openItems.value.includes(props.name));
    const toggle = () => accordion.toggleItem(props.name);
</script>

<template>
    <div class="accordion-item" :class="{'open': isOpen}">
        <a class="d-flex align-items-flex-start accordion-header" @click="toggle">
            <span class="flex-grow-1">{{ $t(titleI18n) }}</span>
            <span class="accordion-header-icon">
                <fa :icon="faChevronRight"/>
            </span>
        </a>
        <TransitionExpand>
            <div v-show="isOpen">
                <slot/>
            </div>
        </TransitionExpand>
    </div>
</template>

<style scoped lang="scss">
    @import "../../../styles/variables";

    .accordion-item {
        .accordion-header {
            color: inherit;
            font-size: 1.1em;
            padding: .5em 0;
            margin: 0;
            border-top: 1px solid #ccc;
        }
        &:first-child .accordion-header {
            border-top: none;
        }
        .accordion-header-icon {
            transition: transform .2s;
        }
        &.open .accordion-header-icon {
            transform: rotate(90deg);
        }
    }
</style>
