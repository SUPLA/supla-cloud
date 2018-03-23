<template>
    <div class="api-setting-copy-input">
        <div class="input-group">
            <input type="text"
                class="form-control"
                v-model="value"
                readonly>
            <span class="input-group-btn">
                <button class="btn btn-default copy"
                    type="button"
                    v-clipboard:copy="value">
                    {{ $t('copy') }}
                </button>
            </span>
        </div>
        <label>{{ label }}</label>
    </div>
</template>

<script>
    export default {
        props: ['label', 'value'],
    };
</script>
