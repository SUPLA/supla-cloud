import Vue from "vue";
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {faSquare} from '@fortawesome/free-regular-svg-icons'
import {faCheckSquare} from '@fortawesome/free-solid-svg-icons'

library.add(faSquare, faCheckSquare);

Vue.component('fa', FontAwesomeIcon)
