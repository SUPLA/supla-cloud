<template>
    <modal-confirm
        class="modal-warning"
        @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :loading="loading"
        :header="$t(headerI18n)">
        <p v-if="descriptionI18n">{{ $t(descriptionI18n) }}</p>
        <subject-dependencies :dependencies="dependencies">
            <template v-slot:deletingHeader>
                {{ $t(deletingHeaderI18n) }}
            </template>
            <template v-slot:removingHeader>
                {{ $t(removingHeaderI18n) }}
            </template>
        </subject-dependencies>
    </modal-confirm>
</template>

<script>
    import SubjectDependencies from "./subject-dependencies";

    export default {
        components: {SubjectDependencies},
        props: ['dependencies', 'headerI18n', 'descriptionI18n', 'deletingHeaderI18n', 'removingHeaderI18n', 'loading'],
    };
</script>
