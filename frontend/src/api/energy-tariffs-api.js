import {api} from '@/api/api';

export const energyTariffsApi = {
  async getTariffs() {
    const {body} = await api.get('energy-tariffs');
    return body;
  },
  async getPriceLists(tariffId) {
    const {body} = await api.get(`energy-tariffs/${tariffId}/price-lists`);
    return body;
  },
  async createPriceList(tariffId, payload) {
    const {body} = await api.post(`energy-tariffs/${tariffId}/price-lists`, payload);
    return body;
  },
  async updatePriceList(tariffId, priceListId, payload) {
    const {body} = await api.put(`energy-tariffs/${tariffId}/price-lists/${priceListId}`, payload);
    return body;
  },
  async deletePriceList(tariffId, priceListId) {
    return await api.delete_(`energy-tariffs/${tariffId}/price-lists/${priceListId}`);
  },
  async getTariffAssignments(channelId) {
    const {body} = await api.get(`channels/${channelId}/energy-tariff-assignments`);
    return body;
  },
  async createTariffAssignment(channelId, payload) {
    const {body} = await api.post(`channels/${channelId}/energy-tariff-assignments`, payload);
    return body;
  },
  async updateTariffAssignment(channelId, assignmentId, payload) {
    const {body} = await api.put(`channels/${channelId}/energy-tariff-assignments/${assignmentId}`, payload);
    return body;
  },
  async deleteTariffAssignment(channelId, assignmentId) {
    return await api.delete_(`channels/${channelId}/energy-tariff-assignments/${assignmentId}`);
  },
  async getPriceListAssignments(channelId) {
    const {body} = await api.get(`channels/${channelId}/energy-price-list-assignments`);
    return body;
  },
  async createPriceListAssignment(channelId, payload) {
    const {body} = await api.post(`channels/${channelId}/energy-price-list-assignments`, payload);
    return body;
  },
  async updatePriceListAssignment(channelId, assignmentId, payload) {
    const {body} = await api.put(`channels/${channelId}/energy-price-list-assignments/${assignmentId}`, payload);
    return body;
  },
  async deletePriceListAssignment(channelId, assignmentId) {
    return await api.delete_(`channels/${channelId}/energy-price-list-assignments/${assignmentId}`);
  },
  async getEnergyCostLogs(channelId, params = {}) {
    const query = new URLSearchParams(
      Object.entries(params)
        .filter(([, value]) => value !== undefined && value !== null && value !== '')
        .map(([key, value]) => [key, String(value)])
    ).toString();
    const {body} = await api.get(`channels/${channelId}/energy-cost-logs${query ? `?${query}` : ''}`);
    return body;
  },
  async getEnergyCostSummaries(channelId, params = {}) {
    const query = new URLSearchParams(
      Object.entries(params)
        .filter(([, value]) => value !== undefined && value !== null && value !== '')
        .map(([key, value]) => [key, String(value)])
    ).toString();
    const {body} = await api.get(`channels/${channelId}/energy-cost-summaries${query ? `?${query}` : ''}`);
    return body;
  },
};
