<template>
    <square-link class="clearfix pointer lift-up grey">
        <a @click="$emit('click')">
            <span v-if="model.id == 'new'">{{ $t('Add new icon' )}}</span>
            <function-icon v-else
                :model="model.channel.function"
                :alternative="model.id"></function-icon>
        </a>
    </square-link>
</template>

<script>
    import FunctionIcon from "./function-icon";

    export default {
        components: {FunctionIcon},
        props: ['model'],
    };
</script>
