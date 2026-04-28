import {api} from '@/api/api';

export const usersApi = {
  async getCurrent() {
    return await api.get(`users/current`);
  },
};
