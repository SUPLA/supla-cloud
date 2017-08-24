<template>
    <div class="btn-group btn-group-filters">
        <button v-for="filter in filters"
            :class="'btn btn-black btn-outline ' + (chosenFilter === filter.value ? 'active' : '')"
            @click="setFilter(filter.value)">
            {{ filter.label }}
        </button>
    </div>
</template>

<script>
    export default {
        props: ['value', 'filters'],
        data() {
            return {
                chosenFilter: undefined,
            };
        },
        mounted() {
            this.setFilter(this.filters[0].value);
        },
        methods: {
            setFilter(filter) {
                this.chosenFilter = filter;
                this.$emit('input', this.chosenFilter);
            }
        }
    };
</script>

<style lang="scss">
    .btn-group-filters {
        opacity: .6;
        transition: opacity .2s;
        &:hover {
            opacity: 1;
        }
    }
</style>
