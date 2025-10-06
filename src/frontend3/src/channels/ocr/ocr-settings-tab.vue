<template>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <OcrSettingsForm :subject="subject"/>
            </div>
            <div class="col-md-6">
                <OcrHistory :subject="subject"/>
            </div>
        </div>
    </div>
</template>

<script setup>
    import OcrSettingsForm from "@/channels/ocr/ocr-settings-form.vue";
    import OcrHistory from "@/channels/ocr/ocr-history.vue";

    defineProps({subject: Object});
</script>
