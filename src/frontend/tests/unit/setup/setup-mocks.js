import {config} from "@vue/test-utils";
import "@/common/common-directives";
import Vue from "vue";

Vue.config.external = {};

config.mocks.$t = key => key;
config.mocks.$http = {
    get: () => Promise.resolve([]),
};
config.stubs.fa = true;
config.stubs['router-link'] = true;
config.stubs['button-loading-dots'] = true;
