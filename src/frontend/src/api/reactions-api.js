import {api} from '@/api/api';

export const reactionsApi = {
  async getList() {
    const {body} = await api.get('reactions');
    return body;
  },
};
