<script setup>
  import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';
  import {DateTime} from 'luxon';
  import {useI18n} from 'vue-i18n';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {energyPriceApi} from '@/api/energy-price-api';

  const props = defineProps({channel: {type: Object, required: true}});
  const i18n = useI18n();
  const logs = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const now = ref(DateTime.now().setZone('Europe/Warsaw'));
  let requestToken = 0;
  let currentTimeInterval;
  const timezone = 'Europe/Warsaw';
  const levels = {
    0: {color: '#226b11', textColor: '#fff', label: 'RECOMMENDED USE. Take advantage of excess energy.'}, // i18n
    1: {color: '#98c21d', textColor: '#000', label: 'NORMAL USE. Use the energy as usual.'}, // i18n
    2: {color: '#f2c433', textColor: '#000', label: 'RECOMMENDED SAVING. Schedule energy-intensive household activities for other hours.'}, // i18n
    3: {color: '#e42313', textColor: '#fff', label: 'REQUIRED LIMITATION. Limit your electricity consumption to the essential minimum.'}, // i18n
  };

  const days = computed(() =>
    [
      ...new Set(
        logs.value.map(({dateTimestamp}) =>
          DateTime.fromMillis(dateTimestamp * 1000)
            .setZone(timezone)
            .toISODate()
        )
      ),
    ].slice(-7)
  );
  const cells = computed(() =>
    Object.fromEntries(
      logs.value.map(({dateTimestamp, value}) => {
        const date = DateTime.fromMillis(dateTimestamp * 1000).setZone(timezone);
        return [`${date.toISODate()}-${date.hour}`, value];
      })
    )
  );
  const dayLabel = (day) => DateTime.fromISO(day, {zone: timezone}).setLocale(i18n.locale.value).toFormat('ccc dd');
  const levelFor = (day, hour) => levels[cells.value[`${day}-${hour}`]];
  const isNow = (day, hour) => now.value.toISODate() === day && now.value.hour === hour;

  async function load() {
    const token = ++requestToken;
    loading.value = true;
    error.value = null;
    try {
      const loadedLogs = await energyPriceApi.getLogs(props.channel.id);
      if (token === requestToken) logs.value = loadedLogs;
    } catch (requestError) {
      if (token === requestToken) error.value = requestError.body?.message || 'Could not load peak-hour data.'; // i18n
    } finally {
      if (token === requestToken) loading.value = false;
    }
  }

  watch(() => props.channel.id, load, {immediate: true});
  onMounted(() => {
    currentTimeInterval = setInterval(() => (now.value = DateTime.now().setZone(timezone)), 60000);
  });
  onBeforeUnmount(() => clearInterval(currentTimeInterval));
</script>

<template>
  <div class="container channel-peak-hours">
    <loading-cover :loading="loading">
      <div v-if="error" class="alert alert-danger">{{ $t(error) }}</div>
      <div v-else-if="days.length" class="table-responsive">
        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th></th>
              <th v-for="day in days" :key="day">{{ dayLabel(day) }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="hour in 24" :key="hour">
              <th>{{ String(hour - 1).padStart(2, '0') }}:00</th>
              <td
                v-for="day in days"
                :key="day"
                :class="{'current-time': isNow(day, hour - 1)}"
                :style="levelFor(day, hour - 1) && {backgroundColor: levelFor(day, hour - 1).color, color: levelFor(day, hour - 1).textColor}"
              >
                <span v-if="levelFor(day, hour - 1)" :title="$t(levelFor(day, hour - 1).label)">{{ cells[`${day}-${hour - 1}`] }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="alert alert-info">{{ $t('No peak-hour data in this range') }}</div>
    </loading-cover>
  </div>
</template>

<style scoped lang="scss">
  @use '../../styles/variables' as *;

  .channel-peak-hours {
    td,
    th {
      min-width: 4.5em;
    }
    td {
      overflow: hidden;
      position: relative;
      &.current-time::after {
        border-left: 20px solid transparent;
        border-top: 20px solid $supla-white;
        content: '';
        position: absolute;
        right: 0;
        top: 0;
      }
    }
  }
</style>
