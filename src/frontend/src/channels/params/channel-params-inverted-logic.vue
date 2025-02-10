<template>
    <div>
        <dl>
            <dd>{{ $t('Inverted logic') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.invertedLogic"
                    @input="$emit('change')"></toggler>
            </dt>
        </dl>
    </div>
</template>

<script>
    import Toggler from "../../common/gui/toggler";

    export default {
        components: {Toggler},
        props: {channel: Object},
    };
</script>
