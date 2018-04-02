<template>
    <square-link class="clearfix pointer lift-up grey">
        <a @click="$emit('click')">
            <function-icon :model="model.channel.function"
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
