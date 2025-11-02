import {api} from '@/api/api';

export const usersApi = {
  async getCurrent() {
    return await api.get(`users/current`);
  },
  async technicalPasswordEnable(password) {
    return await api.patch(`users/current`, {action: 'technicalAccess:on', password});
  },
  async technicalPasswordDisable() {
    return await api.patch(`users/current`, {action: 'technicalAccess:off'});
  },
};
