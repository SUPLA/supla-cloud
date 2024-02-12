<template>
    <div :class="'loading-cover' + (isLoading ? ' is-loading' : '')">
        <div class="loading">
            <loader-dots></loader-dots>
        </div>
        <div class="loading-content clearfix">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    import LoaderDots from "./loader-dots.vue";

    export default {
        props: {
            loading: {
                type: Boolean,
                default: false
            },
            debounce: {
                default: 300,
                type: Number
            }
        },
        components: {LoaderDots},
        data() {
            return {
                timeout: undefined,
                isLoading: false,
            };
        },
        mounted() {
            this.loadingChanged();
        },
        methods: {
            loadingChanged() {
                if (this.loading && !this.timeout) {
                    this.timeout = setTimeout(() => {
                        this.isLoading = true;
                        this.timeout = undefined;
                    }, this.debounce);
                } else if (!this.loading) {
                    if (this.timeout) {
                        clearTimeout(this.timeout);
                        this.timeout = undefined;
                    }
                    this.isLoading = false;
                }
            }
        },
        watch: {
            loading() {
                this.loadingChanged();
            }
        }
    };
</script>

<style lang="scss">
    .loading-cover {
        &.is-loading {
            position: relative;
            min-height: 150px;
            > .loading-content {
                opacity: .4;
                .loading {
                    display: none;
                }
            }
            > .loading {
                display: block;
            }
            + * {
                .loading {
                    display: none;
                }
            }
        }
        > .loading-content {
            transition: opacity .3s;
        }
        > .loading {
            display: none;
            .loader-dots {
                position: absolute;
                left: 50%;
                margin: 0 0 0 -60px;
                top: 50%;
                z-index: 1000;
            }
            &:before, &:after {
                content: " ";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                min-height: 50px;
                z-index: 1000;
            }
            &:before {
                opacity: 0;
            }
        }
    }
</style>
