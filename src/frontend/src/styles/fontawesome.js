import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {faSquare} from '@fortawesome/free-regular-svg-icons'
import {
  faAngleDoubleDown,
  faAngleDoubleRight,
  faAngleDoubleUp,
  faArrowRight,
  faCalendarWeek,
  faCaretDown,
  faCaretUp,
  faCheck,
  faCheckSquare,
  faChevronDown,
  faChevronLeft,
  faChevronRight,
  faCircle,
  faCircleNotch,
  faClock,
  faDownload,
  faEdit,
  faExclamationTriangle,
  faInfoCircle,
  faKey,
  faPlus,
  faPowerOff,
  faPuzzlePiece,
  faQuestionCircle,
  faSave,
  faTimesCircle,
  faTrash,
  faUnlock,
  faWrench,
} from '@fortawesome/free-solid-svg-icons';

library.add(faSquare, faCheckSquare, faDownload, faPuzzlePiece, faKey, faTimesCircle, faCheck,
  faChevronLeft, faChevronRight, faChevronDown, faArrowRight, faQuestionCircle, faPlus, faTrash, faInfoCircle, faCircleNotch,
  faPowerOff, faEdit, faSave, faCaretUp, faCaretDown, faAngleDoubleDown, faAngleDoubleRight, faAngleDoubleUp,
  faCalendarWeek, faClock, faUnlock, faCircle, faExclamationTriangle, faWrench);

export function registerFontAwesome(app) {
  app.component('fa', FontAwesomeIcon)
}

