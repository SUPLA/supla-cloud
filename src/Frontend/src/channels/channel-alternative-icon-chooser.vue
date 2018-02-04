<template>
    <div>
        <a @click="choosing = true"
            v-if="channel.function.maxAlternativeIconIndex > 0"
            class="btn btn-link">
            <i class="pe-7s-settings"></i>
        </a>
        <modal class="modal-location-chooser"
            :header="$t('Select icon')"
            v-if="choosing">
            <carousel :navigation-enabled="true"
                :pagination-enabled="false"
                navigation-next-label="&gt;"
                navigation-prev-label="&lt;"
                :per-page-custom="[[1024, 4], [768, 3], [600, 2], [100, 1]]"
                ref="carousel">
                <slide v-for="index in channel.function.maxAlternativeIconIndex + 1"
                    :key="index">
                    <square-link :class="'clearfix pointer lift-up grey ' + (channel.altIcon == index - 1 ? 'selected' : '')">
                        <a @click="choose(index - 1)">
                            <function-icon :model="channel.function"
                                :alternative="index - 1"></function-icon>
                        </a>
                    </square-link>
                </slide>
            </carousel>
            <div slot="footer">
                <a @click="choosing = false"
                    class="cancel">
                    <i class="pe-7s-close"></i>
                </a>
            </div>
        </modal>
    </div>
</template>

<script>
    import {Carousel, Slide} from 'vue-carousel';
    import FunctionIcon from "./function-icon";

    export default {
        components: {
            FunctionIcon,
            Carousel, Slide
        },
        props: ['channel'],
        data() {
            return {
                choosing: false,
            };
        },
        methods: {
            choose(index) {
                this.channel.altIcon = index;
                this.choosing = false;
                this.$emit('change');
            }
        }
    };
</script>
