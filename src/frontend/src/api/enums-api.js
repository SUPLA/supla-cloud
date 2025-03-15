import {api} from "@/api/api";

export const enumsApi = {
    async getChannelFunctions() {
        const {body} = await api.get('enum/functions');
        return body;
    },
}
