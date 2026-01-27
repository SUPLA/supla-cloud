import {api} from '@/api/api';

export const locationsApi = {
  async getList() {
    const {body} = await api.get('locations');
    return body;
  },
  async getOneWithPassword(locationId) {
    const {body} = await api.get(`locations/${locationId}?include=password`);
    return body;
  },
  async create() {
    const {body} = await api.post('locations', {});
    return body;
  },
  async update(locationId, newSettings) {
    const {body} = await api.put(`locations/${locationId}`, newSettings);
    return body;
  },
  async delete_(locationId) {
    return await api.delete_(`locations/${locationId}`);
  },
};
