import {api} from '@/api/api';

export const directLinksApi = {
  async getList() {
    const {body} = await api.get('direct-links');
    return body;
  },
};
