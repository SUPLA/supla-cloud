<template>
  <div>
    <div v-if="item">
      <BreadcrumbList v-if="breadcrumbs" current class="container">
        <router-link :to="{name: listRouteName}">{{ $t(headerI18n) }}</router-link>
      </BreadcrumbList>
      <div v-if="item">
        <router-view
          v-if="items"
          :key="`carousel_page_details_${item.id || 'noid'}`"
          :item="item"
          @add="onItemAdded($event)"
          @delete="onItemDeleted()"
          @update="onItemUpdated($event)"
        ></router-view>
      </div>
    </div>
    <div v-else>
      <list-page
        :header-i18n="headerI18n"
        :tile="tile"
        :endpoint="endpoint"
        :create-new-label-i18n="createNewLabelI18n"
        :limit="limit"
        :filters="filters"
        :id-param-name="idParamName"
        :dont-set-page-title="dontSetPageTitle"
        :details-route="detailsRoute"
      ></list-page>
    </div>
  </div>
</template>

<script>
  import {warningNotification} from '../notifier';
  import ListPage from './list-page.vue';
  import {extendObject} from '@/common/utils';
  import {api} from '@/api/api.js';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';

  export default {
    components: {BreadcrumbList, ListPage},
    props: {
      headerI18n: String,
      tile: Object,
      filters: Object,
      endpoint: String,
      createNewLabelI18n: String,
      detailsRoute: String,
      listRoute: String,
      limit: Number,
      permanentCarouselView: Boolean,
      dontSetPageTitle: Boolean,
      store: Object,
      idParamName: {
        type: String,
        default: 'id',
      },
      newItemFactory: {
        type: Function,
        default: () => ({}),
      },
      breadcrumbs: Boolean,
    },
    data() {
      return {
        item: undefined,
        items: undefined,
      };
    },
    computed: {
      listRouteName() {
        return this.listRoute || this.detailsRoute + 's';
      },
    },
    watch: {
      '$route.params': {
        handler() {
          if (this.$route.params[this.idParamName]) {
            let selected = this.items.find((item) => item.id == this.$route.params[this.idParamName]);
            if (!selected) {
              selected = {};
            }
            this.itemChanged(selected);
          } else {
            this.item = undefined;
          }
        },
        deep: true,
      },
    },
    mounted() {
      api.get(this.endpoint).then(({body}) => {
        this.items = body;
        if (this.$route.params[this.idParamName]) {
          const selected = this.items.find((item) => item.id == this.$route.params[this.idParamName]);
          if (selected) {
            this.itemChanged(selected);
          } else if (this.$route.params[this.idParamName] === 'new') {
            this.itemChanged(this.newItemFactory());
          }
        }
      });
    },
    methods: {
      itemChanged(item) {
        if (!item.id) {
          if (this.limit && this.items.length >= this.limit) {
            return warningNotification('Limit has been exceeded');
          }
          item = this.newItemFactory();
          this.$router
            .push({
              name: this.detailsRoute,
              params: {[this.idParamName]: 'new'},
            })
            .catch(() => undefined);
        }
        this.item = item;
      },
      onItemAdded(item) {
        this.items.push(item);
        this.item = item;
        this.$router.push({name: this.detailsRoute, params: {[this.idParamName]: item.id}});
        this.store?.fetchAll(true);
      },
      onItemUpdated(item) {
        const itemToUpdate = this.items.find((c) => item.id == c.id);
        extendObject(itemToUpdate, item);
        this.store?.fetchAll(true);
      },
      onItemDeleted() {
        this.items.splice(this.items.indexOf(this.item), 1);
        this.item = undefined;
        this.$router.push({name: this.listRouteName});
        this.store?.fetchAll(true);
      },
    },
  };
</script>
