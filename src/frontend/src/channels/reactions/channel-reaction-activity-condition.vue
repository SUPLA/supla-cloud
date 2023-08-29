<template>
    <div :class="{night: times[0] > times[1]}">
        <div class="mt-5 mb-5 px-2">
            <vue-slider v-model="times" :enable-cross="true" :min="-120" :max="120" :marks="marks" :order="false"
                tooltip="none" @change="updateModel()"
            />
        </div>
        <div class="alert alert-info">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div>
                        <strong>{{ $t('Active from') }}:</strong>
                        {{ timeFormatter(times[0]) }}
                    </div>
                    <div>
                        <strong>{{ $t('Active to') }}:</strong>
                        {{ timeFormatter(times[1]) }}
                    </div>
                </div>
                <a @click="swapTimes()">
                    {{ $t('swap') }}
                    <fa icon="shuffle"/>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import 'vue-slider-component/theme/antd.css';
    import {deepCopy} from "@/common/utils";

    export default {
        components: {
            VueSlider: () => import('vue-slider-component'),
        },
        props: {
            value: Array,
        },
        data() {
            return {
                times: [-60, 60],
                marks: {
                    '-120': '🌃',
                    '-60': '🌅',
                    '0': '☀️',
                    '60': '🌇',
                    '120': '🌃',
                },
                afterCondition: undefined,
                beforeCondition: undefined,
            };
        },
        mounted() {
            this.times = deepCopy(this.value || [-60, 60]);
        },
        methods: {
            updateModel() {
                this.$emit('input', this.times);
            },
            swapTimes() {
                this.times = [this.times[1], this.times[0]];
                this.updateModel();
            },
            timeFormatter(value) {
                if (value === -120 || value === 120) {
                    return this.$t('midnight');
                } else if (value < -60) {
                    return this.$t('{count} minutes before sunrise', {count: Math.abs(value + 60)});
                } else if (value === -60) {
                    return this.$t('sunrise');
                } else if (value < 0) {
                    return this.$t('{count} minutes after sunrise', {count: Math.abs(value + 60)});
                } else if (value < 60) {
                    return this.$t('{count} minutes before sunset', {count: 60 - value});
                } else if (value === 60) {
                    return this.$t('sunset');
                } else {
                    return this.$t('{count} minutes after sunset', {count: value - 60});
                }
            }
        },
    }
</script>

<style lang="scss">
    @import "../../styles/variables";

    $activeColor: $supla-green;
    $activeColorHover: lighten($supla-green, 5%);
    $inactiveColor: darken($supla-grey-light, 10%);

    .vue-slider {
        .vue-slider-rail {
            background-color: $inactiveColor;
        }
        .vue-slider-process {
            background-color: $activeColor;
        }
        .vue-slider-dot-handle {
            border-color: $activeColor;
            border-color: $activeColor;
        }
        .vue-slider-mark-step {
            box-shadow: 0 0 0 2px $inactiveColor;
        }
        &:hover {
            .vue-slider-process {
                background-color: $activeColorHover;
            }
            .vue-slider-dot-handle {
                &, &:hover {
                    border-color: $activeColorHover;
                }
            }
            .vue-slider-mark-step {
                box-shadow: 0 0 0 2px $inactiveColor;
            }
        }
    }

    .night {
        .vue-slider {
            .vue-slider-rail {
                background-color: $activeColor;
            }
            .vue-slider-process {
                background-color: $inactiveColor;
            }
        }
        &:hover {
            .vue-slider-rail {
                background-color: $activeColorHover;
            }
            .vue-slider-process {
                background-color: $inactiveColor;
            }
        }
    }
</style>
