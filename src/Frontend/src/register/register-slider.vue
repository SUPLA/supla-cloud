<template>
    <div id="slides">
        <ul class="slides-container">
            <li v-for="slide in slides">
                <img :src="slide.img"
                    alt="">
                <div class="container">
                    <div class="info-wrapper">
                        <article>
                            <h1>{{ $t(slide.title) }}</h1>
                            <p>{{ $t(slide.description) }}</p>
                        </article>
                    </div>
                </div>
            </li>
        </ul>
        <nav class="slides-navigation">
            <a class="next"><i class="pe-7s-angle-right-circle"></i></a>
            <a class="prev"><i class="pe-7s-angle-left-circle"></i></a>
        </nav>
    </div>
</template>

<script>
    import "superslides/dist/jquery.superslides";

    export default {
        props: ['texts'],
        computed: {
            slides() {
                const slides = [];
                for (let [index, text] of this.texts.entries()) {
                    slides.push({
                        img: `img: 'assets/img/${index + 1}.svg`,
                        title: `${text}-title`,
                        description: `${text}-text`,
                    });
                }
                return slides;
            }
        },
        mounted() {
            if (this.texts) {
                this.slides = this.texts;
            }
            $('#slides').superslides({
                animation: 'fade',
                play: 12500,
                pagination: false,
                inherit_width_from: window,
                inherit_height_from: window
            });
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    #slides {
        position: relative;
        width: 100%;
        overflow: visible;

        @media screen and (max-width: 899px) {
            display: none;
        }

        .slides-container {
            display: none;
        }

        .slides-navigation {
            margin: 0 auto;
            position: absolute;
            z-index: 3;
            top: 46%;
            width: 100%;

            a {
                position: absolute;
                display: block;
                font-size: 60px;
                color: rgba(255, 255, 255, 0.7);
                padding: 0px;
                background: transparent;
                transition: all 0.3s;

                &:active,
                &:focus,
                &:hover {
                    color: $supla-white;
                    background: transparent;
                }

                &.prev {
                    left: 20px;
                }
                &.next {
                    right: 20px;
                }
            }
        }

        .container {
            height: 100%;
            display: table;

            .info-wrapper {
                display: table-cell;
                height: 100%;
                vertical-align: middle;

                article {
                    margin-left: 90px;
                    width: 40%;

                    h1 {
                        color: $supla-white;
                        text-transform: none;
                    }

                    p {
                        color: $supla-white;
                    }
                }
            }
        }
    }
</style>
