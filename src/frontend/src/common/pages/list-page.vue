<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <div>
                    <h1 v-if="!subject" v-title>{{ $t(headerI18n) }}</h1>
                    <h4 v-if="subtitleI18n">{{ $t(subtitleI18n) }}</h4>
                </div>
                <div :class="subject ? 'mt-0' : ''">
                    <a @click="createNewItem()"
                        v-if="createNewLabelI18n"
                        class="btn btn-green btn-lg btn-wrapped">
                        <i class="pe-7s-plus"></i>
                        {{ $t(createNewLabelI18n) }}
                    </a>
                </div>
            </div>
            <component v-if="filters && items"
                :is="filters"
                :items="items"
                class="mt-3"
                @filter-function="filterFunction = $event; filter()"
                @compare-function="compareFunction = $event; filter()"
                @filter="filter()"></component>
        </div>
        <loading-cover :loading="!items">
            <div v-if="filteredItems">
                <square-links-grid v-if="filteredItems.length"
                    :count="filteredItems.length">
                    <div v-for="item in filteredItems"
                        :key="item.id">
                        <component :is="tile"
                            :model="item"></component>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
        </loading-cover>
    </div>
</template>

<script>
    import changeCase from "change-case";
    import AppState from "../../router/app-state";
    import {debounce} from "lodash";

    export default {
        props: ['subject', 'headerI18n', 'subtitleI18n', 'tile', 'filters', 'endpoint', 'createNewLabelI18n', 'detailsRoute', 'limit'],
        data() {
            return {
                items: undefined,
                filteredItems: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        mounted() {
            let endpoint = this.endpoint;
            if (this.subject) {
                endpoint = `${changeCase.paramCase(this.subject.ownSubjectType)}s/${this.subject.id}/${endpoint}`;
            }
            this.$http.get(endpoint)
                .then(response => this.items = response.body)
                .then(() => this.filter());
        },
        computed: {
            subjectId() {
                return this.subject.id;
            }
        },
        methods: {
            createNewItem() {
                if (this.detailsRoute) {
                    if (this.subject) {
                        AppState.addTask(this.detailsRoute + 'Create', this.subject);
                    }
                    this.$router.push({name: this.detailsRoute, params: {id: 'new'}});
                } else {
                    this.$emit('add');
                }
            },
            filter: debounce(function () {
                this.filteredItems = this.items ? this.items.filter(this.filterFunction) : this.items;
                if (this.filteredItems) {
                    this.filteredItems = this.filteredItems.sort(this.compareFunction);
                }
            }, 50),
        },
    };
</script>
