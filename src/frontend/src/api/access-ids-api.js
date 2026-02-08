import {api} from '@/api/api';

export const accessIdsApi = {
  async getList() {
    const {body} = await api.get('accessids');
    return body;
  },
  async create() {
    const {body} = await api.post('accessids', {});
    return body;
  },
};
