import {ref} from "vue";

export function useConfigGroups() {
    const configGroup = ref(undefined);

    const displayConfigGroup = (newGroup) => {
        if (configGroup.value === newGroup) {
            configGroup.value = undefined;
        } else {
            configGroup.value = newGroup;
        }
    }

    const configGroupChevron = (groupName) => {
        return configGroup.value === groupName ? 'chevron-down' : 'chevron-right'
    }

    return {configGroup, displayConfigGroup, configGroupChevron}
}
