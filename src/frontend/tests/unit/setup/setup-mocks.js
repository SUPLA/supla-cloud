import {config} from "@vue/test-utils";

config.mocks.$t = key => key;
config.mocks.$http = {
    get: () => Promise.resolve([]),
};
