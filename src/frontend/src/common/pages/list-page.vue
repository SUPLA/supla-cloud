<template>
  <div>
    <div class="container">
      <div class="d-flex my-3 align-items-center">
        <div class="flex-grow-1">
          <h1 v-if="!subject" class="m-0">
            <span v-if="dontSetPageTitle">{{ $t(headerI18n) }}</span>
            <span v-else v-title>{{ $t(headerI18n) }}</span>
          </h1>
          <h4 v-if="subtitleI18n">{{ $t(subtitleI18n) }}</h4>
        </div>
        <div :class="subject ? 'mt-0' : ''">
          <a v-if="createNewLabelI18n" class="btn btn-green btn-lg btn-wrapped" @click="createNewItem()">
            <i class="pe-7s-plus"></i>
            {{ $t(createNewLabelI18n) }}
          </a>
        </div>
      </div>
      <Component
        :is="filters"
        v-if="filters && items"
        :items="items"
        class="mt-3"
        @filter-function="
          filterFunction = $event;
          filter();
        "
        @compare-function="
          compareFunction = $event;
          filter();
        "
        @filter="filter()"
      />
    </div>
    <loading-cover :loading="!items">
      <div v-if="(!filters || compareFunction) && filteredItems">
        <square-links-grid v-if="filteredItems.length" :count="filteredItems.length">
          <div v-for="item in filteredItems" :key="item.id">
            <component :is="tile" :model="item"></component>
          </div>
        </square-links-grid>
        <empty-list-placeholder v-else></empty-list-placeholder>
      </div>
    </loading-cover>
  </div>
</template>

<script>
  import * as changeCase from 'change-case';
  import AppState from '../../router/app-state';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import SquareLinksGrid from '@/common/tiles/square-links-grid.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {api} from '@/api/api.js';
  import {useDebounceFn} from '@vueuse/core';

  export default {
    components: {LoadingCover, SquareLinksGrid, EmptyListPlaceholder},
    props: [
      'subject',
      'headerI18n',
      'subtitleI18n',
      'tile',
      'filters',
      'endpoint',
      'createNewLabelI18n',
      'detailsRoute',
      'limit',
      'idParamName',
      'dontSetPageTitle',
    ],
    data() {
      return {
        items: undefined,
        filteredItems: undefined,
        filterFunction: () => true,
        compareFunction: undefined,
      };
    },
    computed: {
      subjectId() {
        return this.subject.id;
      },
    },
    mounted() {
      let endpoint = this.endpoint;
      if (this.subject) {
        endpoint = `${changeCase.kebabCase(this.subject.ownSubjectType)}s/${this.subject.id}/${endpoint}`;
      }
      api
        .get(endpoint)
        .then((response) => (this.items = response.body))
        .then(() => this.filter());
    },
    methods: {
      createNewItem() {
        if (this.detailsRoute) {
          AppState.addTask(this.detailsRoute + 'Create', this.subject || 'new');
          const idParamName = this.idParamName || 'id';
          this.$router.push({name: this.detailsRoute, params: {[idParamName]: 'new'}});
        } else {
          this.$emit('add');
        }
      },
      filter: useDebounceFn(function () {
        this.filteredItems = this.items ? this.items.filter(this.filterFunction) : this.items;
        if (this.filteredItems && this.compareFunction) {
          this.filteredItems = this.filteredItems.sort(this.compareFunction);
        }
      }, 50),
    },
  };
</script>
