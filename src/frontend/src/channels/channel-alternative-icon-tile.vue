<template>
    <square-link class="clearfix pointer lift-up grey">
        <a @click="$emit('click')"
            class="valign-center">
            <span v-if="model.id == 'new'">
                <i class="pe-7s-plus"
                    style="font-size: 3em"></i>
                {{ $t('Add a new icon') }}
            </span>
            <channel-user-icon-preview :icon="model"
                v-else-if="model.function"></channel-user-icon-preview>
            <function-icon v-else
                :model="model.channel.function"
                :config="model.channel.config"
                :alternative="model.index"></function-icon>
        </a>
    </square-link>
</template>

<script>
    import FunctionIcon from "./function-icon";
    import ChannelUserIconPreview from "./channel-user-icon-preview";

    export default {
        components: {ChannelUserIconPreview, FunctionIcon},
        props: ['model'],
    };
</script>
