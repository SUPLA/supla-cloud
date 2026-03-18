import {config} from '@vue/test-utils';

config.global.mocks.$t = (key) => key;
config.global.mocks.$http = {
  get: () => Promise.resolve({data: []}),
};
config.global.stubs.fa = true;
config.global.stubs['router-link'] = true;
config.global.stubs['button-loading-dots'] = true;
config.global.stubs['transition-expand'] = true;
