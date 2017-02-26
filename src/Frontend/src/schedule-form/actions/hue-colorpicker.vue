<template>
    <input type="text" class="colorpicker" ref="picker">
</template>

<script type="text/babel">
    import "spectrum-colorpicker";
    import "spectrum-colorpicker/spectrum.css";

    export default {
        name: 'hue-colorpicker',
        props: ['value'],
        mounted() {
            if (!this.value) {
                this.value = 0;
                this.$emit('input', this.value);
            }
            $(this.$refs.picker).spectrum({
                color: `hsv(${this.value}, 100%, 100%)`,
                showButtons: false,
                containerClassName: 'hue-colorpicker'
            }).change((e) => {
                let hue = e.target.value.match(/^hsv\(([0-9]+)/)[1];
                this.$emit('input', hue);
            });
        }
    };
</script>

<style lang="scss" rel="stylesheet/scss">
    .hue-colorpicker {
        .sp-color {
            display: none;
        }

        .sp-hue {
            left: 0;
        }
    }
</style>
