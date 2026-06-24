import {api} from '@/api/api';

export const energyTariffsApi = {
  async getTariffs() {
    const {body} = await api.get('energy-tariffs');
    return body;
  },
  async getTariffProfiles() {
    const {body} = await api.get('energy-tariff-profiles');
    return body;
  },
  async getTariffProfile(profileId) {
    const {body} = await api.get(`energy-tariff-profiles/${profileId}`);
    return body;
  },
  async createTariffProfile(payload) {
    const {body} = await api.post('energy-tariff-profiles', payload);
    return body;
  },
  async updateTariffProfile(profileId, payload) {
    const {body} = await api.put(`energy-tariff-profiles/${profileId}`, payload);
    return body;
  },
  async deleteTariffProfile(profileId) {
    return await api.delete_(`energy-tariff-profiles/${profileId}`);
  },
  async getChannelTariffProfileAssignment(channelId) {
    const {body, status} = await api.get(`channels/${channelId}/energy-tariff-profile-assignment`, {
      skipErrorHandler: [204],
    });
    return status === 204 || body === '' ? null : body;
  },
  async assignChannelTariffProfile(channelId, profileId) {
    const {body} = await api.put(`channels/${channelId}/energy-tariff-profile-assignment`, {profileId});
    return body;
  },
  async deleteChannelTariffProfileAssignment(channelId) {
    return await api.delete_(`channels/${channelId}/energy-tariff-profile-assignment`);
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
