import {api} from '@/api/api';

const pathPart = (value) => encodeURIComponent(String(value));

export const energyPriceApi = {
  async getLogs(channelId) {
    const {body} = await api.get(`channels/${pathPart(channelId)}/energy-price-logs`);
    return body;
  },
};
