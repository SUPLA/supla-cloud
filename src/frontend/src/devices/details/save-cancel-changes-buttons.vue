<template>
    <div>
        <transition-expand>
            <div class="text-center mt-3" v-if="hasPendingChanges">
                <FormButton button-class="btn-grey" class="mx-1" @click="emit('cancel')" :disabled="loading">
                    <i class="pe-7s-back"></i>
                    {{ $t('Cancel changes') }}
                </FormButton>
                <FormButton button-class="btn-white" class="mx-1" @click="emitSave()" submit :loading="loading">
                    <i class="pe-7s-diskette"></i>
                    {{ $t('Save changes') }}
                </FormButton>
            </div>
        </transition-expand>
    </div>
</template>

<script setup>
    import {computed, ref, watch} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import FormButton from "@/common/gui/FormButton.vue";

    const props = defineProps({
        original: Object,
        changes: Object,
    });

    const emit = defineEmits(['save', 'cancel']);

    const loading = ref(false);
    const hasPendingChanges = computed(() => JSON.stringify(props.original) !== JSON.stringify(props.changes));

    function emitSave() {
        loading.value = true;
        emit('save', () => loading.value = false);
    }

    watch(() => hasPendingChanges.value, () => loading.value = false);
</script>
