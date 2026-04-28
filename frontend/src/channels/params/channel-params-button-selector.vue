<template>
  <div>
    <div v-if="values.length < 5 && !useDropdown" class="btn-group btn-group-flex">
      <a
        v-for="valueDef in values"
        :key="valueDef.id"
        :class="'btn ' + (value == valueDef.id ? 'btn-green' : 'btn-default')"
        @click="$emit('input', valueDef.id)"
      >
        {{ valueDef.label }}
      </a>
    </div>
    <DropdownMenu>
      <DropdownMenuTrigger button>
        {{ currentValueDef.label }}
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <li v-for="valueDef in values" :key="valueDef.id">
          <a v-show="valueDef.id != value" @click="$emit('input', valueDef.id)">{{ valueDef.label }}</a>
        </li>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>

<script>
  import DropdownMenu from '@/common/gui/dropdown/dropdown-menu.vue';
  import DropdownMenuTrigger from '@/common/gui/dropdown/dropdown-menu-trigger.vue';
  import DropdownMenuContent from '@/common/gui/dropdown/dropdown-menu-content.vue';

  export default {
    components: {DropdownMenuContent, DropdownMenuTrigger, DropdownMenu},
    props: {
      value: String,
      values: Array,
      useDropdown: Boolean,
    },
    computed: {
      currentValueDef() {
        return this.values.find((v) => v.id === this.value);
      },
    },
    mounted() {
      if (!this.values.find((v) => v.id === this.value)) {
        this.$emit('input', this.values[0].id);
      }
    },
  };
</script>
