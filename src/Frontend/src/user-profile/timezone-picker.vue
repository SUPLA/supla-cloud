<template>
  <div id="timezone-picker">
    <h1>{{msg}}</h1>
  </div>
</template>

<script type="text/babel">
export default {
  name: 'app',
  data () {
    return {
      msg: 'Timezone picker'
    }
  }
}
</script>

<style lang="scss" scoped>
h1 {
  color: blue;
}
</style>
