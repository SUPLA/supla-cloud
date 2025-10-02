import {api} from "@/api/api";

export const accessIdsApi = {
    async getList() {
        const {body} = await api.get('accessids?include=locations');
        return body;
    },
}
