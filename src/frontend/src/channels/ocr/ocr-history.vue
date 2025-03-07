<template>
    <div>

        <div class="clearfix left-right-header">
            <h2 class="no-margin-top">{{ $t('Photo history') }}</h2>
            <div>
                <a @click="execute()">
                    <fa icon="refresh" :spin="isFetching"/>
                </a>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>{{ $t('Date') }}</th>
                <th>{{ $t('Image') }}</th>
                <th class="text-center">{{ $t('Reading') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="image in images" :key="image.id">
                <td>{{ image.imageTakenAt | formatDateTime }}</td>
                <td>
                    <img :src="`data:image/png;base64,${image.imageCropped}`" class="ocr-image" v-if="image.imageCropped">
                    <span v-else class="label label-danger">{{ $t('Error') }}</span>
                </td>
                <td class="text-center">
                    <span v-if="image.processedAt">
                        <span
                            :class="['label', image.measurementValid === null ? 'label-default' : (image.measurementValid ? 'label-success' : 'label-warning')]">
                            {{ image.resultMeasurement || $t('Error') }}
                        </span>
                    </span>
                    <span class="label label-default" v-else>{{ $t('Waiting') }}</span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
    import {useSuplaApi} from "@/api/use-supla-api";

    const props = defineProps({subject: Object});

    const {data: images, execute, isFetching} = useSuplaApi(`integrations/ocr/${props.subject.id}/images`).json();
</script>

<style lang="scss" scoped>
    .ocr-image {
        max-height: 23px;
    }
</style>
