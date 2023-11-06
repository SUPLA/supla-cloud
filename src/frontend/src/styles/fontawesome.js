import Vue from "vue";
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {faSquare} from '@fortawesome/free-regular-svg-icons'
import {
    faAngleDoubleDown,
    faAngleDoubleRight,
    faAngleDoubleUp,
    faArrowRight,
    faCalendarWeek,
    faCancel,
    faCaretDown,
    faCaretUp,
    faCheck,
    faCheckSquare,
    faChevronDown,
    faChevronLeft,
    faChevronRight,
    faCircleHalfStroke,
    faCircleNotch,
    faClock,
    faDownload,
    faEdit,
    faGear,
    faHand,
    faInfoCircle,
    faKey,
    faPlus,
    faPowerOff,
    faPuzzlePiece,
    faQuestionCircle,
    faRefresh,
    faSave,
    faShieldHalved,
    faShuffle,
    faSignIn,
    faSignOut,
    faTimesCircle,
    faTrash,
} from '@fortawesome/free-solid-svg-icons';

library.add(faSquare, faCheckSquare, faGear, faDownload, faSignOut, faSignIn, faShieldHalved, faPuzzlePiece, faKey, faTimesCircle, faCheck,
    faChevronLeft, faChevronRight, faChevronDown, faArrowRight, faQuestionCircle, faPlus, faTrash, faShuffle, faInfoCircle, faCircleNotch,
    faPowerOff, faEdit, faSave, faCancel, faRefresh, faCaretUp, faCaretDown, faAngleDoubleDown, faAngleDoubleRight, faAngleDoubleUp,
    faCalendarWeek, faHand, faClock, faCircleHalfStroke);
Vue.component('fa', FontAwesomeIcon)
