import {api} from '@/api/api';

export const directLinksApi = {
  async getList() {
    const {body} = await api.get('direct-links');
    return body;
  },
  async create(subjectType, subjectId) {
    const toSend = {subjectType, subjectId, allowedActions: ['read']};
    const {body} = await api.post('direct-links', toSend);
    return body;
  },
  async update(linkId, newSettings) {
    const {body} = await api.put(`direct-links/${linkId}`, newSettings);
    return body;
  },
  async delete_(linkId) {
    return await api.delete_(`direct-links/${linkId}`);
  },
};
