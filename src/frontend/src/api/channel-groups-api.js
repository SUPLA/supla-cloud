import {api} from '@/api/api';

export const channelGroupsApi = {
  async getList() {
    const {body} = await api.get('channel-groups');
    return body;
  },
};
