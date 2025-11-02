import {api} from '@/api/api';

export const scenesApi = {
  async getList() {
    const {body} = await api.get('scenes');
    return body;
  },
};
