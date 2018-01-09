import Vue from "vue";
import Modal from "./modal.vue";
import ModalConfirm from "./modal-confirm.vue";
import LoadingCover from "./gui/loaders/loading-cover.vue";
import Flipper from "./tiles/flipper.vue";
import SquareLink from "./tiles/square-link.vue";
import SquareLinksGrid from "./tiles/square-links-grid.vue";

Vue.component('modal', Modal);
Vue.component('modalConfirm', ModalConfirm);
Vue.component('loadingCover', LoadingCover);
Vue.component('flipper', Flipper);
Vue.component('squareLink', SquareLink);
Vue.component('squareLinksGrid', SquareLinksGrid);
