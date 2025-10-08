<template>
  <loading-cover :loading="!auditEntries">
    <div v-if="auditEntries" class="list-group">
      <div
        v-for="entry in auditEntries"
        :key="entry.id"
        :class="'list-group-item list-group-item-' + (entry.event.name == 'DIRECT_LINK_EXECUTION' ? 'success' : 'danger')"
      >
        <div v-if="entry.event.name == 'DIRECT_LINK_EXECUTION'">
          {{ $t('Executed action') }}
          <strong>{{ functionLabel(entry.textParam) }}</strong>
        </div>
        <div v-else>
          {{ errorEntryMessage(entry.textParam) }}
        </div>
        <div class="text-muted small">
          {{ formatDateTime(entry.createdAt) }}
          {{ $t('from IP') }}
          {{ entry.ipv4 }}
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-6 text-left">
        <a v-if="page > 1" @click="loadPage(page - 1)">&laquo; {{ $t('Newer') }}</a>
      </div>
      <div class="col-xs-6 text-right">
        <a v-if="hasMorePages" @click="loadPage(page + 1)">{{ $t('Older') }} &raquo;</a>
      </div>
    </div>
    <empty-list-placeholder v-if="auditEntries && auditEntries.length === 0"></empty-list-placeholder>
  </loading-cover>
</template>

<script>
  import {safeJsonParse} from '../common/utils';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import {api} from '@/api/api.js';
  import {formatDateTime} from '@/common/filters-date.js';

  export default {
    components: {EmptyListPlaceholder, LoadingCover},
    props: ['directLink'],
    data() {
      return {
        auditEntries: undefined,
        timer: undefined,
        page: 1,
        hasMorePages: false,
      };
    },
    computed: {
      possibleActions() {
        if (this.directLink) {
          return [
            {
              id: 1000,
              name: 'READ',
              caption: 'Read',
              nameSlug: 'read',
            },
          ].concat(this.directLink.subject.possibleActions);
        }
        return [];
      },
    },
    watch: {
      'directLink.id'() {
        this.fetch();
      },
    },
    mounted() {
      this.fetch();
    },
    beforeUnmount() {
      clearTimeout(this.timer);
    },
    methods: {
      formatDateTime,
      fetch() {
        clearTimeout(this.timer);
        this.timer = undefined;
        api.get(`direct-links/${this.directLink.id}/audit?pageSize=5&page=` + this.page).then((response) => {
          this.auditEntries = response.body;
          this.timer = setTimeout(() => this.fetch(), 15000);
          this.hasMorePages = +response.headers.get('X-Total-Count') > this.page * 5;
        });
      },
      loadPage(page) {
        if (this.timer) {
          this.page = page;
          this.fetch();
        }
      },
      functionLabel(functionId) {
        return this.$t(this.possibleActions.filter((action) => action.id == functionId)[0].caption);
      },
      errorEntryMessage(text) {
        const failureData = safeJsonParse(text);
        if (failureData) {
          let message = this.$t(failureData.reason);
          if (failureData.details && (failureData.details.action_name || failureData.details.action)) {
            message += ': ' + (failureData.details.action_name || failureData.details.action);
          }
          return message;
        } else {
          return this.$t(text);
        }
      },
    },
  };
</script>
