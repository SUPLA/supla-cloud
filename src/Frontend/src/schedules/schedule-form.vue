<template>
    <div id="schedule-form">
        <h1>{{msg}}</h1>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'app',
        data () {
            return {
                msg: 'SUPLA v2.0'
            }
        }
    }
</script>

<style lang="scss" scoped>
    h1 {
        color: red;
    }
</style>
