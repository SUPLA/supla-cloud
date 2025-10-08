<template>
    <div class="btn-filters">
        <div class="btn-group btn-group-filters btn-group-filters-inline">
            <button v-for="filter in visibleFilters"
                :key="filter.label"
                :class="'btn ' + (chosenFilter === filter.value ? 'active' : '')"
                @click="setFilter(filter.value)"
                type="button">
                {{ filter.label }}
            </button>
        </div>
        <div class="btn-group-filters btn-group-filters-dropdown">
            <DropdownMenu>
              <DropdownMenuTrigger button>
                {{ chosenFilterLabel }}
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <li v-for="filter in filters"
                  :key="filter.label">
                  <a @click="setFilter(filter.value)">{{ filter.label }}</a>
                </li>
              </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>
</template>

<script>
  import DropdownMenu from "@/common/gui/dropdown/dropdown-menu.vue";
  import DropdownMenuTrigger from "@/common/gui/dropdown/dropdown-menu-trigger.vue";
  import DropdownMenuContent from "@/common/gui/dropdown/dropdown-menu-content.vue";

  export default {
      components: {DropdownMenuContent, DropdownMenuTrigger, DropdownMenu},
        props: ['id', 'value', 'filters', 'defaultSort'],
        data() {
            return {
                chosenFilter: undefined,
            };
        },
        mounted() {
            if (this.id) {
              const filter = this.defaultSort || localStorage.getItem(this.localStorageId);
                if (filter) {
                    const index = this.filters.findIndex(f => f.value === filter);
                    if (index >= 0) {
                        this.setFilter(filter);
                    }
                }
            }
            if (!this.chosenFilter) {
                this.setFilter(this.filters[0].value);
            }
        },
        methods: {
            setFilter(filter) {
                this.chosenFilter = filter;
                if (this.value != filter) {
                    this.$emit('input', this.chosenFilter);
                }
                if (this.id && !this.defaultSort) {
                  localStorage.setItem(this.localStorageId, filter);
                }
            }
        },
        computed: {
            localStorageId() {
                return `btnFilters${this.id}`;
            },
            chosenFilterLabel() {
                const filter = this.filters.find(f => f.value === this.chosenFilter);
                return filter ? filter.label : '';
            },
            visibleFilters() {
                return this.filters.filter(f => f.visible !== false);
            },
        },
        watch: {
            value(value) {
                if (value) {
                    this.setFilter(value);
                }
            }
        }
    };
</script>

<style lang="scss">
    .btn-filters {
        .btn-group-filters {
            margin-left: .5em;
        }
    }
</style>
