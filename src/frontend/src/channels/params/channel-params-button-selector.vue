<template>
    <div>
        <div class="btn-group btn-group-flex"
            v-if="values.length < 5 && !useDropdown">
            <a :class="'btn ' + (value == valueDef.id ? 'btn-green' : 'btn-default')"
                :key="valueDef.id"
                v-for="valueDef in values"
                @click="$emit('input', valueDef.id)">
                {{ valueDef.label }}
            </a>
        </div>
        <DropdownMenu>
          <DropdownMenuTrigger button>
            {{ currentValueDef.label }}
          </DropdownMenuTrigger>
          <DropdownMenuContent>
                <li v-for="valueDef in values"
                    :key="valueDef.id">
                    <a @click="$emit('input', valueDef.id)"
                        v-show="valueDef.id != value">{{ valueDef.label }}</a>
                </li>
          </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>

<script>
  import DropdownMenu from "@/common/gui/dropdown/dropdown-menu.vue";
  import DropdownMenuTrigger from "@/common/gui/dropdown/dropdown-menu-trigger.vue";
  import DropdownMenuContent from "@/common/gui/dropdown/dropdown-menu-content.vue";

  export default {
      components: {DropdownMenuContent, DropdownMenuTrigger, DropdownMenu},
        props: {
            value: String,
            values: Array,
            useDropdown: Boolean,
        },
        mounted() {
            if (!this.values.find(v => v.id === this.value)) {
                this.$emit('input', this.values[0].id);
            }
        },
        computed: {
            currentValueDef() {
                return this.values.find(v => v.id === this.value);
            }
        }
    };
</script>
