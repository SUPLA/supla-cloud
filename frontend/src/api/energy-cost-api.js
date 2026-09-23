import {api} from '@/api/api';

const pathPart = (value) => encodeURIComponent(String(value));

export const energyCostApi = {
  async getPresets() {
    const {body} = await api.get('energy-tariff-presets');
    return body;
  },

  async getPreset(id) {
    const {body} = await api.get(`energy-tariff-presets/${pathPart(id)}`);
    return body;
  },

  async getPlans() {
    const {body} = await api.get('energy-cost-plans');
    return body;
  },

  async getPlan(id) {
    const {body} = await api.get(`energy-cost-plans/${pathPart(id)}`);
    return body;
  },

  async createPlan(data) {
    const {body} = await api.post('energy-cost-plans', data);
    return body;
  },

  async updatePlan(id, data) {
    const {body} = await api.put(`energy-cost-plans/${pathPart(id)}`, data);
    return body;
  },

  deletePlan(id) {
    return api.delete_(`energy-cost-plans/${pathPart(id)}`);
  },

  async getAssignment(channelId) {
    try {
      const {body} = await api.get(`channels/${pathPart(channelId)}/energy-cost-plan-assignment`, {skipErrorHandler: [404]});
      return body;
    } catch (error) {
      if (error.status === 404) return null;
      throw error;
    }
  },

  async assignPlan(channelId, planId) {
    const {body} = await api.put(`channels/${pathPart(channelId)}/energy-cost-plan-assignment`, {planId: Number(planId)});
    return body;
  },

  unassignPlan(channelId) {
    return api.delete_(`channels/${pathPart(channelId)}/energy-cost-plan-assignment`);
  },

  async calculate(channelId, fromTimestamp, toTimestamp, options = {}) {
    const query = new URLSearchParams({
      fromTimestamp: String(fromTimestamp),
      toTimestamp: String(toTimestamp),
      ...Object.fromEntries(Object.entries(options).map(([key, value]) => [key, String(value)])),
    });
    const {body} = await api.get(`channels/${pathPart(channelId)}/energy-cost-calculation?${query}`);
    return body;
  },
};
