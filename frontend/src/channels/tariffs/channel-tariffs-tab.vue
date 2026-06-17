<template>
  <div class="container channel-tariffs-tab">
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="details-page-block h-100">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h3 class="m-0">{{ $t('Assigned tariffs') }}</h3>
            <a class="btn btn-default btn-sm" @click="startCreating()">{{ $t('Add period') }}</a>
          </div>

          <div v-if="!tariffAssignments.length" class="empty-state text-center text-muted py-4">
            {{ $t('No tariff periods configured yet.') }}
          </div>

          <div v-for="assignment in tariffAssignments" :key="`tariff-${assignment.id}`" class="assignment-card">
            <div class="assignment-card__header">
              <div>
                <strong>{{ assignment.tariff?.name || assignment.tariff?.code }}</strong>
                <div class="text-muted small">{{ assignment.tariff?.code }}</div>
              </div>
              <div class="assignment-actions">
                <a class="btn btn-link btn-sm" @click="startEditingTariffAssignment(assignment)">{{ $t('Edit') }}</a>
                <a class="btn btn-link btn-sm text-danger" @click="deleteTariffAssignment(assignment)">{{ $t('Delete') }}</a>
              </div>
            </div>
            <div class="small text-muted">
              {{ formatRange(assignment.validFrom, assignment.validTo) }}
            </div>
            <div class="linked-price-list mt-2" v-if="matchingPriceAssignments(assignment).length">
              <div class="small fw-semibold mb-1">{{ $t('Price assignments') }}</div>
              <div v-for="priceAssignment in matchingPriceAssignments(assignment)" :key="priceAssignment.id" class="linked-price-list__item">
                <div>
                  <strong>{{ priceAssignment.priceList?.name }}</strong>
                  <div class="small text-muted">{{ formatRange(priceAssignment.validFrom, priceAssignment.validTo) }}</div>
                </div>
                <div class="assignment-actions">
                  <a class="btn btn-link btn-sm" @click="startEditingPriceAssignment(priceAssignment)">{{ $t('Edit') }}</a>
                  <a class="btn btn-link btn-sm text-danger" @click="deletePriceAssignment(priceAssignment)">{{ $t('Delete') }}</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="details-page-block">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h3 class="m-0">{{ editorTitle }}</h3>
            <a v-if="editing" class="btn btn-grey btn-sm" @click="resetEditor()">{{ $t('Cancel changes') }}</a>
          </div>

          <div v-if="!tariffs.length" class="alert alert-warning mb-0">
            {{ $t('There are no tariff definitions available yet.') }}
          </div>

          <form v-else @submit.prevent="saveEditor()">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ $t('Tariff') }}</label>
                  <select v-model.number="editor.tariffId" class="form-control" @change="onTariffChange()">
                    <option v-for="tariff in tariffs" :key="tariff.id" :value="tariff.id">{{ tariff.name }} ({{ tariff.code }})</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ $t('Price list mode') }}</label>
                  <select v-model="editor.priceListMode" class="form-control" @change="onPriceListModeChange()">
                    <option value="new">{{ $t('Create new price list') }}</option>
                    <option value="existing" :disabled="!availablePriceLists.length">{{ $t('Use existing price list') }}</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="panel-block">
              <h4>{{ $t('Tariff assignment period') }}</h4>
              <DateRangePicker :value="editor.tariffDateRange" @input="(value) => (editor.tariffDateRange = value)" />
            </div>

            <div class="panel-block">
              <h4>{{ $t('Price assignment period') }}</h4>
              <DateRangePicker :value="editor.priceAssignmentDateRange" @input="(value) => (editor.priceAssignmentDateRange = value)" />
            </div>

            <div class="panel-block" v-if="editor.priceListMode === 'existing'">
              <div class="form-group">
                <label>{{ $t('Existing price list') }}</label>
                <select v-model.number="editor.priceListId" class="form-control" @change="loadSelectedPriceList()">
                  <option v-for="priceList in availablePriceLists" :key="priceList.id" :value="priceList.id">
                    {{ priceList.name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="panel-block">
              <div class="row">
                <div class="col-md-8">
                  <div class="form-group">
                    <label>{{ $t('Price list name') }}</label>
                    <input v-model="editor.priceList.name" class="form-control" type="text" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{ $t('Billing period starts on day') }}</label>
                    <input v-model.number="editor.priceList.billingPeriodStartDay" class="form-control" type="number" min="1" max="28" />
                  </div>
                </div>
              </div>
            </div>

            <div class="panel-block">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h4 class="m-0">{{ $t('Price components') }}</h4>
                <div class="d-flex gap-2">
                  <a class="btn btn-default btn-sm" @click="addPriceItem()">{{ $t('Add component') }}</a>
                  <a class="btn btn-default btn-sm" @click="addZoneSet()">{{ $t('Add one row per zone') }}</a>
                </div>
              </div>

              <div v-if="!editor.priceList.items.length" class="empty-state text-center text-muted py-4">
                {{ $t('No price components yet.') }}
              </div>

              <div v-for="(item, index) in editor.priceList.items" :key="item.key" class="price-item-card">
                <div class="row g-2 align-items-end">
                  <div class="col-md-4">
                    <label>{{ $t('Component') }}</label>
                    <input v-model="item.componentCode" class="form-control" list="energy-price-component-codes" type="text" />
                  </div>
                  <div class="col-md-3">
                    <label>{{ $t('Zone') }}</label>
                    <select v-model="item.zoneCode" class="form-control">
                      <option :value="null">{{ $t('No zone') }}</option>
                      <option v-for="zone in tariffZones" :key="zone.code" :value="zone.code">{{ zone.name || zone.code }}</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label>{{ $t('Price') }}</label>
                    <input v-model.number="item.amount" class="form-control" type="number" step="0.000001" min="0" />
                  </div>
                  <div class="col-md-2">
                    <label>{{ $t('Unit') }}</label>
                    <select v-model="item.unit" class="form-control">
                      <option v-for="unit in unitOptions" :key="unit" :value="unit">{{ unit }}</option>
                    </select>
                  </div>
                  <div class="col-md-1 text-end">
                    <a class="btn btn-link text-danger btn-sm" @click="removePriceItem(index)">{{ $t('Delete') }}</a>
                  </div>
                </div>
                <div class="row g-2 mt-1">
                  <div class="col-md-4">
                    <label>{{ $t('Currency') }}</label>
                    <CurrencyPicker v-model="item.currency" />
                  </div>
                </div>
              </div>
              <datalist id="energy-price-component-codes">
                <option v-for="component in suggestedComponents" :key="component" :value="component"></option>
              </datalist>
            </div>

            <div class="text-end mt-4">
              <button class="btn btn-white" :disabled="saving" type="submit">
                {{ saving ? $t('Saving') : $t('Save tariff and prices') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import {energyTariffsApi} from '@/api/energy-tariffs-api';
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import CurrencyPicker from '@/channels/params/currency-picker.vue';
  import {formatDateTime} from '@/common/filters-date';

  const suggestedComponents = ['ENERGY_ACTIVE_IMPORT', 'DISTRIBUTION_VARIABLE', 'DISTRIBUTION_FIXED', 'FEE'];

  export default {
    components: {DateRangePicker, CurrencyPicker},
    props: {
      channel: {type: Object, required: true},
    },
    data() {
      return {
        loading: true,
        saving: false,
        tariffs: [],
        tariffAssignments: [],
        priceAssignments: [],
        priceListsByTariff: {},
        editing: false,
        editor: this.createEmptyEditor(),
        unitOptions: ['kWh', 'month', 'day'],
        suggestedComponents,
      };
    },
    computed: {
      editorTitle() {
        return this.editing ? this.$t('Edit tariff and prices') : this.$t('Assign tariff and prices');
      },
      selectedTariff() {
        return this.tariffs.find((tariff) => tariff.id === this.editor.tariffId);
      },
      tariffZones() {
        return this.selectedTariff?.config?.zones || [];
      },
      availablePriceLists() {
        return this.priceListsByTariff[this.editor.tariffId] || [];
      },
    },
    async mounted() {
      await this.loadAll();
    },
    methods: {
      formatDateTime,
      createEmptyEditor() {
        return {
          tariffAssignmentId: null,
          priceAssignmentId: null,
          tariffId: null,
          tariffDateRange: {dateStart: undefined, dateEnd: undefined},
          priceAssignmentDateRange: {dateStart: undefined, dateEnd: undefined},
          priceListMode: 'new',
          priceListId: null,
          priceList: {
            id: null,
            name: '',
            billingPeriodStartDay: 1,
            items: [],
          },
        };
      },
      async loadAll() {
        this.loading = true;
        this.tariffs = await energyTariffsApi.getTariffs();
        this.tariffAssignments = await energyTariffsApi.getTariffAssignments(this.channel.id);
        this.priceAssignments = await energyTariffsApi.getPriceListAssignments(this.channel.id);
        await Promise.all(this.tariffs.map((tariff) => this.ensurePriceListsLoaded(tariff.id)));
        this.startCreating();
        this.loading = false;
      },
      async ensurePriceListsLoaded(tariffId) {
        if (!this.priceListsByTariff[tariffId]) {
          this.priceListsByTariff = {
            ...this.priceListsByTariff,
            [tariffId]: await energyTariffsApi.getPriceLists(tariffId),
          };
        }
      },
      async startCreating() {
        this.editing = false;
        this.editor = this.createEmptyEditor();
        this.editor.tariffId = this.tariffs[0]?.id || null;
        if (this.editor.tariffId) {
          await this.ensurePriceListsLoaded(this.editor.tariffId);
        }
        this.addPriceItem();
      },
      async onTariffChange() {
        await this.ensurePriceListsLoaded(this.editor.tariffId);
        this.editor.priceListId = this.availablePriceLists[0]?.id || null;
        if (this.editor.priceListMode === 'existing') {
          this.loadSelectedPriceList();
        }
      },
      onPriceListModeChange() {
        if (this.editor.priceListMode === 'existing') {
          this.editor.priceListId = this.availablePriceLists[0]?.id || null;
          this.loadSelectedPriceList();
        } else {
          this.editor.priceListId = null;
          this.editor.priceList = {id: null, name: '', billingPeriodStartDay: 1, items: []};
          this.addPriceItem();
        }
      },
      loadSelectedPriceList() {
        const priceList = this.availablePriceLists.find((entry) => entry.id === this.editor.priceListId);
        if (!priceList) {
          return;
        }
        this.editor.priceList = {
          id: priceList.id,
          name: priceList.name,
          billingPeriodStartDay: priceList.billingPeriodStartDay || 1,
          items: (priceList.items || []).map((item, index) => ({...item, key: `${priceList.id}-${index}`})),
        };
      },
      matchingPriceAssignments(assignment) {
        return this.priceAssignments.filter((priceAssignment) => priceAssignment.priceList?.tariffId === assignment.tariffId);
      },
      startEditingTariffAssignment(assignment) {
        this.editing = true;
        this.editor = this.createEmptyEditor();
        this.editor.tariffAssignmentId = assignment.id;
        this.editor.tariffId = assignment.tariffId;
        this.editor.tariffDateRange = {dateStart: assignment.validFrom, dateEnd: assignment.validTo};
        this.ensurePriceListsLoaded(this.editor.tariffId).then(() => {
          const priceAssignment = this.matchingPriceAssignments(assignment)[0];
          if (priceAssignment) {
            this.startEditingPriceAssignment(priceAssignment, false);
          }
        });
      },
      startEditingPriceAssignment(assignment, preserveEdit = true) {
        this.editing = preserveEdit;
        this.editor.priceAssignmentId = assignment.id;
        this.editor.tariffId = assignment.priceList.tariffId;
        this.editor.priceAssignmentDateRange = {dateStart: assignment.validFrom, dateEnd: assignment.validTo};
        this.editor.priceListMode = 'existing';
        this.ensurePriceListsLoaded(this.editor.tariffId).then(() => {
          this.editor.priceListId = assignment.priceListId;
          this.loadSelectedPriceList();
        });
      },
      resetEditor() {
        this.startCreating();
      },
      addPriceItem(zoneCode = null) {
        this.editor.priceList.items.push({
          key: `${Date.now()}-${Math.random()}`,
          componentCode: '',
          zoneCode,
          amount: 0,
          unit: 'kWh',
          currency: 'PLN',
        });
      },
      addZoneSet() {
        if (!this.tariffZones.length) {
          this.addPriceItem();
          return;
        }
        this.tariffZones.forEach((zone) => this.addPriceItem(zone.code));
      },
      removePriceItem(index) {
        this.editor.priceList.items.splice(index, 1);
      },
      formatRange(validFrom, validTo) {
        return `${formatDateTime(validFrom)} - ${validTo ? formatDateTime(validTo) : this.$t('Open-ended')}`;
      },
      async saveEditor() {
        this.saving = true;
        try {
          let priceListId = this.editor.priceListId;
          const priceListPayload = {
            name: this.editor.priceList.name,
            billingPeriodStartDay: this.editor.priceList.billingPeriodStartDay,
            items: this.editor.priceList.items.map(({componentCode, zoneCode, amount, unit, currency}) => ({componentCode, zoneCode, amount, unit, currency})),
          };

          if (this.editor.priceListMode === 'existing' && priceListId) {
            await energyTariffsApi.updatePriceList(this.editor.tariffId, priceListId, priceListPayload);
          } else {
            const createdPriceList = await energyTariffsApi.createPriceList(this.editor.tariffId, priceListPayload);
            priceListId = createdPriceList.id;
          }

          const tariffAssignmentPayload = {
            tariffId: this.editor.tariffId,
            validFrom: this.editor.tariffDateRange.dateStart,
            validTo: this.editor.tariffDateRange.dateEnd || null,
          };
          if (this.editor.tariffAssignmentId) {
            await energyTariffsApi.updateTariffAssignment(this.channel.id, this.editor.tariffAssignmentId, tariffAssignmentPayload);
          } else {
            await energyTariffsApi.createTariffAssignment(this.channel.id, tariffAssignmentPayload);
          }

          const priceAssignmentPayload = {
            priceListId,
            validFrom: this.editor.priceAssignmentDateRange.dateStart,
            validTo: this.editor.priceAssignmentDateRange.dateEnd || null,
          };
          if (this.editor.priceAssignmentId) {
            await energyTariffsApi.updatePriceListAssignment(this.channel.id, this.editor.priceAssignmentId, priceAssignmentPayload);
          } else {
            await energyTariffsApi.createPriceListAssignment(this.channel.id, priceAssignmentPayload);
          }

          this.priceListsByTariff[this.editor.tariffId] = await energyTariffsApi.getPriceLists(this.editor.tariffId);
          await this.loadAll();
        } finally {
          this.saving = false;
        }
      },
      async deleteTariffAssignment(assignment) {
        await energyTariffsApi.deleteTariffAssignment(this.channel.id, assignment.id);
        await this.loadAll();
      },
      async deletePriceAssignment(assignment) {
        await energyTariffsApi.deletePriceListAssignment(this.channel.id, assignment.id);
        await this.loadAll();
      },
    },
  };
</script>

<style lang="scss">
  .channel-tariffs-tab {
    .details-page-block {
      margin-bottom: 2rem;
    }

    .assignment-card,
    .price-item-card,
    .panel-block {
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 14px;
      padding: 1rem;
      background: rgba(255, 255, 255, 0.9);
      margin-bottom: 1rem;
    }

    .assignment-card__header,
    .linked-price-list__item {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      align-items: flex-start;
    }

    .assignment-actions {
      display: flex;
      gap: 0.25rem;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .panel-block h4 {
      margin-top: 0;
      margin-bottom: 1rem;
    }

    .empty-state {
      border: 1px dashed rgba(0, 0, 0, 0.15);
      border-radius: 12px;
    }

    @media (max-width: 767px) {
      .assignment-card__header,
      .linked-price-list__item {
        flex-direction: column;
      }

      .assignment-actions {
        justify-content: flex-start;
      }

      .price-item-card {
        padding: 0.85rem;
      }
    }
  }
</style>
