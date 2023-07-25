import {config} from "@vue/test-utils";
import "@/common/common-directives";
import $ from "jquery";
import "@/common/bootstrap-select";

$.fn.selectpicker.Constructor.BootstrapVersion = '3.4.1';

config.mocks.$t = key => key;
config.mocks.$http = {
    get: () => Promise.resolve([]),
};
config.stubs.fa = true;
config.stubs['router-link'] = true;
config.stubs['button-loading-dots'] = true;
