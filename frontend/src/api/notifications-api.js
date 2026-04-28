import {api} from '@/api/api';

export const notificationsApi = {
  async getList() {
    const {body} = await api.get('notifications');
    return body;
  },
};
