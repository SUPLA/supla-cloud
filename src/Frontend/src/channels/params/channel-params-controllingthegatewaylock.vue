<template>
    <div>
        <dl>
            <dd>{{ $t('Relay switching time') }}</dd>
            <dt>
                <div class="btn-group btn-group-justified">
                    <div v-for="i in [1,2,3]"
                        class="btn-group">
                        <button class="btn btn-default">{{ i }}s</button>
                    </div>
                </div>
            </dt>
        </dl>
    </div>
</template>

<script>
    export default {}
</script>
