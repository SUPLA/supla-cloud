<template>
  <dl>
    <dd>{{ $t('Relay switching time') }}</dd>
    <dt>
      <div v-if="times.length < 5" class="btn-group btn-group-flex">
        <a v-for="time in times" :key="time" :class="'btn ' + (value == time ? 'btn-green' : 'btn-default')" @click="$emit('input', time)">
          {{ timeInSeconds(time) }}
        </a>
      </div>
      <DropdownMenu v-else>
        <DropdownMenuTrigger button>
          {{ timeInSeconds(value) }}
        </DropdownMenuTrigger>
        <DropdownMenuContent>
          <li v-for="time in times" :key="time">
            <a v-show="time != value" @click="$emit('input', time)">{{ timeInSeconds(time) }}</a>
          </li>
        </DropdownMenuContent>
      </DropdownMenu>
    </dt>
  </dl>
</template>

<script>
  import DropdownMenu from '@/common/gui/dropdown/dropdown-menu.vue';
  import DropdownMenuTrigger from '@/common/gui/dropdown/dropdown-menu-trigger.vue';
  import DropdownMenuContent from '@/common/gui/dropdown/dropdown-menu-content.vue';

  export default {
    components: {DropdownMenuContent, DropdownMenuTrigger, DropdownMenu},
    props: ['value', 'times'],
    mounted() {
      if (this.times.indexOf(this.value) < 0) {
        this.$emit('input', this.times[0]);
      }
    },
    methods: {
      timeInSeconds(time) {
        return time / 1000 + ' ' + this.$t('sec.');
      },
    },
  };
</script>
