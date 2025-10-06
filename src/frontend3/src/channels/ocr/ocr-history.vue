<template>
    <div>

        <div class="clearfix left-right-header">
            <h2 class="no-margin-top">{{ $t('Photo history') }}</h2>
            <div>
                <a @click="fetchOcrMeasurements()">
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
            <tr v-for="(image, $index) in images" :key="image.id">
                <td>{{ image.imageTakenAt | formatDateTime }}</td>
                <td>
                    <img :src="`data:image/png;base64,${image.imageCropped}`" class="ocr-image" v-if="image.imageCropped">
                    <span v-else class="label label-danger">{{ $t('Error') }}</span>
                </td>
                <td class="text-center">
                    <span v-if="image.processedAt">
                        <span
                            :class="['label', image.measurementValid === null ? 'label-default' : (image.measurementValid ? 'label-success' : 'label-warning')]"
                        >{{ image.resultMeasurement || $t('Error') }}</span>
                        <span v-if="image.measurementValid === false && $index === 0" class="ml-2">
                            <a class="small text-warning" v-tooltip.bottom="$t('Does it look good?')"
                                @click="fixingLastMeasurement = true">
                                <fa icon="wrench"/>
                            </a>
                        </span>
                    </span>
                    <span class="label label-default" v-else>{{ $t('Waiting') }}</span>
                </td>
            </tr>
            </tbody>
        </table>

        <modal-confirm v-if="fixingLastMeasurement"
            class="modal-warning"
            @confirm="markLastMeasurementValid().then(fetchOcrMeasurements).finally(() => fixingLastMeasurement = false)"
            @cancel="fixingLastMeasurement = false"
            :header="$t('Everyone makes mistakes!')"
            :loading="markingLastMeasurement || isFetching">
            <div>
                <i18n-t
                    keypath="Our brilliant code detected that the value {value} should not be accepted as a valid measurement, based on the counter history, the quality of the photo and other minor factors."
                    tag="p">
                    <template #value>
                        <code>{{ latestMeasurement.resultMeasurement }}</code>
                    </template>
                </i18n-t>
                <p class="text-center mb-3" v-if="latestMeasurement.imageCropped">
                    <img :src="`data:image/png;base64,${latestMeasurement.imageCropped}`" class="ocr-image ocr-image-bigger">
                </p>
                <p>{{ $t('You can mark this reading manually as valid. This action WILL NOT immediately update the counter value, but it should improve the future readings for your counter.') }}</p>
                <p>{{ $t('Please confirm if you want to do this.') }}</p>
            </div>
        </modal-confirm>
    </div>
</template>

<script setup>
    import {useSuplaApi} from "@/api/use-supla-api";
    import {computed, ref} from "vue";

    const props = defineProps({subject: Object});
    const fixingLastMeasurement = ref(false);

    const {data: images, execute: fetchOcrMeasurements, isFetching} = useSuplaApi(`integrations/ocr/${props.subject.id}/images`).json();

    const latestMeasurement = computed(() => images.value[0]);

    const {execute: markLastMeasurementValid, isFetching: markingLastMeasurement} =
        useSuplaApi(`channels/${props.subject.id}/settings`, {immediate: false})
            .patch({
                action: 'ocr:markLastMeasurementValid',
                get imageId() {
                    return latestMeasurement.value.id;
                }
            });
</script>

<style lang="scss" scoped>
    .ocr-image {
        max-height: 23px;
    }

    .ocr-image-bigger {
        max-height: 33px;
    }
</style>
