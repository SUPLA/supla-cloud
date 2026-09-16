<template>
  <div>
    <loading-cover :loading="loading">
      <div v-if="logs.length">
        <table class="table table-condensed">
          <thead>
            <tr>
              <th>{{ $t('Date and time') }}</th>
              <th>{{ $t('Value') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.date_timestamp">
              <td>{{ formatDateTime(log.date_timestamp) }}</td>
              <td>{{ log.value }}</td>
            </tr>
          </tbody>
        </table>
        <div class="text-center">
          <a v-if="hasMore" class="btn btn-default" @click="loadMore()">{{ $t('Load more') }}</a>
        </div>
      </div>
      <empty-list-placeholder v-else-if="!loading" class="my-5" />
    </loading-cover>
  </div>
</template>

<script>
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {api} from '@/api/api';
  import {formatDateTime} from '@/common/filters-date.js';

  const PAGE_SIZE = 100;

  export default {
    components: {EmptyListPlaceholder, LoadingCover},
    props: ['channel'],
    emits: ['has-logs'],
    data() {
      return {
        loading: true,
        logs: [],
        hasMore: false,
        offset: 0,
      };
    },
    async mounted() {
      await this.loadMore();
    },
    methods: {
      formatDateTime,
      async loadMore() {
        this.loading = true;
        try {
          const params = new URLSearchParams({offset: this.offset, limit: PAGE_SIZE, order: 'DESC'});
          const response = await api.get(`channels/${this.channel.id}/measurement-logs?${params}`);
          const items = response.body || [];
          this.logs.push(...items);
          this.hasMore = items.length === PAGE_SIZE;
          this.offset += items.length;
          this.$emit('has-logs', this.logs.length > 0);
        } finally {
          this.loading = false;
        }
      },
    },
  };
</script>
