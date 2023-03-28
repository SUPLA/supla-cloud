import Vue from "vue";
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {faSquare} from '@fortawesome/free-regular-svg-icons'
import {
    faCheckSquare,
    faDownload,
    faGear,
    faKey,
    faPuzzlePiece,
    faShieldHalved,
    faSignIn,
    faSignOut,
    faTimesCircle
} from '@fortawesome/free-solid-svg-icons'

library.add(faSquare, faCheckSquare, faGear, faDownload, faSignOut, faSignIn, faShieldHalved, faPuzzlePiece, faKey, faTimesCircle);

Vue.component('fa', FontAwesomeIcon)
