import {config} from "@vue/test-utils";
import "@/common/common-directives";

config.mocks.$t = key => key;
config.mocks.$http = {
    get: () => Promise.resolve([]),
};
