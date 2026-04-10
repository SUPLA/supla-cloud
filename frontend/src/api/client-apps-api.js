import {api} from '@/api/api';

export const clientAppsApi = {
  async getList() {
    const {body} = await api.get('client-apps?include=accessId,connected');
    return body;
  },
};
