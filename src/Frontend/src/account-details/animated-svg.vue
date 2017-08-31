<template>
    <div :id="id"></div>
</template>

<script>
    import Vivus from "vivus";

    export default {
        props: ['file', 'id'],
        mounted() {
            new Vivus(this.id, {
                duration: 200,
                file: this.file,
                start: 'autostart',
            });
        }
    };
</script>
