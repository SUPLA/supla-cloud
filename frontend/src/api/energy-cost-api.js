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

  async getPlanStarters() {
    const {body} = await api.get('energy-cost-plan-starters');
    return body;
  },

  async getPlanStarter(id) {
    const {body} = await api.get(`energy-cost-plan-starters/${pathPart(id)}`);
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
      return body || null;
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

  async assignStarter(channelId, starterId, configuration) {
    const {body} = await api.post(`channels/${pathPart(channelId)}/energy-cost-plan-assignment/from-starter`, {starterId, configuration});
    return body;
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

  async simulate(channelId, fromTimestamp, toTimestamp, configuration) {
    const {body} = await api.post(`channels/${pathPart(channelId)}/energy-cost-calculation`, {fromTimestamp, toTimestamp, configuration});
    return body;
  },

  async getMeasurementBounds(channelId) {
    const path = `channels/${pathPart(channelId)}/measurement-logs?limit=1&logsType=default`;
    const [{body: oldest}, {body: newest}] = await Promise.all([api.get(`${path}&order=ASC`), api.get(`${path}&order=DESC`)]);
    return {oldest: oldest[0] || null, newest: newest[0] || null};
  },
};
