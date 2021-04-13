<template>
    <div class="register-slider">
        <div class="register-slide-content">
            <transition name="fade">
                <div v-if="slide">
                    <h1 class="nocapitalize">{{ $t(slide.title) }}</h1>
                    <p>{{ $t(slide.description) }}</p>
                </div>
            </transition>
        </div>
        <a class="register-slider-next"
            @click="showSlide(slideNumber + 1)">
            <i class="pe-7s-angle-right-circle"></i>
        </a>
        <a class="register-slider-prev"
            @click="showSlide(slideNumber - 1)">
            <i class="pe-7s-angle-left-circle"></i>
        </a>
    </div>
</template>

<script>
    export default {
        props: ['texts'],
        data() {
            return {
                slide: undefined,
                slideNumber: 0,
                timeout: undefined,
            };
        },
        computed: {
            slides() {
                const slides = [];
                for (let [index, text] of this.texts.entries()) {
                    slides.push({
                        img: `assets/img/${index + 1}.svg`,
                        title: `${text}-title`,
                        description: `${text}-text`,
                    });
                }
                return slides;
            }
        },
        mounted() {
            this.showSlide(0);
        },
        methods: {
            showSlide(number) {
                this.slideNumber = number % this.slides.length;
                if (this.slideNumber < 0) {
                    this.slideNumber = this.slides.length - 1;
                }
                this.slide = undefined;
                let cssClass = document.body.getAttribute('class');
                cssClass = cssClass.replace(/register-slide-[0-9]/, '').trim();
                cssClass += ' register-slide-' + this.slideNumber;
                document.body.setAttribute('class', cssClass);
                setTimeout(() => {
                    this.slide = this.slides[this.slideNumber];
                }, 200);
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => this.showSlide(this.slideNumber + 1), 12500);
            }
        },
        beforeDestroy() {
            clearTimeout(this.timeout);
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .register-slider {
        a.register-slider-next, a.register-slider-prev {
            position: absolute;
            display: block;
            font-size: 60px;
            color: rgba(255, 255, 255, 0.7);
            padding: 0px;
            background: transparent;
            transition: all 0.3s;
            top: 47%;

            &:active,
            &:focus,
            &:hover {
                color: $supla-white;
                background: transparent;
            }

            &.register-slider-prev {
                left: 20px;
            }
            &.register-slider-next {
                right: 20px;
            }
        }
    }
</style>
