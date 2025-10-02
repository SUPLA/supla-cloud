import {api} from "@/api/api";

export const locationsApi = {
    async getList() {
        const {body} = await api.get('locations');
        return body;
    },
}
