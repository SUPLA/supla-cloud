import {api} from '@/api/api';

export const schedulesApi = {
  async getList() {
    const {body} = await api.get('schedules');
    return body;
  },
};
