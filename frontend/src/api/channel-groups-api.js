import {api} from '@/api/api';

export const channelGroupsApi = {
  async getList() {
    const {body} = await api.get('channel-groups');
    return body;
  },
  async getOne(id) {
    const {body} = await api.get(`channel-groups/${id}`);
    return body;
  },
};
