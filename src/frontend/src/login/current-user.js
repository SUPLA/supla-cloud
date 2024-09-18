import Vue from "vue";
import {Base64} from 'js-base64';
import {DateTime, Settings} from 'luxon';
import {extendObject} from "@/common/utils";
import {getActivePinia} from "pinia";

export class CurrentUser {
    constructor() {
        this.synchronizeAuthState();
        window.addEventListener('storage', (e) => {
            if (e.key === '_token') {
                setTimeout(() => window.location.href = window.location.toString());
            }
        });
    }

    getToken() {
        return Vue.prototype.$localStorage.get('_token');
    }

    getFilesDownloadToken() {
        return Vue.prototype.$localStorage.get('_token_down');
    }

    synchronizeAuthState() {
        Vue.http.headers.common['Authorization'] = this.getToken() ? 'Bearer ' + this.getToken() : undefined;
        this.determineServerUrl();
        Vue.http.options.root = this.serverUrl + Vue.config.external.baseUrl + '/api';
        Settings.defaultZone = this.userData && this.userData.timezone || 'system';
        return this.userData;
    }

    determineServerUrl() {
        if (Vue.config.external.actAsBrokerCloud) {
            this.serverUrl = Base64.decode((this.getToken() || '').split('.')[1] || '') || Vue.config.external.suplaUrl;
        } else {
            this.serverUrl = '';
        }
        if (!this.serverUrl) {
            this.serverUrl = (location && location.origin) || '';
        }
        return this.serverUrl;
    }

    authenticate(username, password) {
        return Vue.http.post('webapp-auth', {username, password}, {skipErrorHandler: [401, 409, 429]})
            .then(response => this.handleNewToken(response))
            .then(() => getActivePinia()._s.forEach(store => store.$reset()))
            .then(() => this.fetchUserData());
    }

    handleNewToken(response) {
        Vue.prototype.$localStorage.set('_token', response.body.access_token);
        const tokenExpiration = DateTime.now().plus({seconds: response.body.expires_in})
            .startOf('second').toISO({suppressMilliseconds: true});
        Vue.prototype.$localStorage.set('_token_expiration', tokenExpiration);
        Vue.prototype.$localStorage.set('_token_down', response.body.download_token);
        this.synchronizeAuthState();
    }

    forget() {
        Vue.prototype.$localStorage.remove('_token');
        Vue.prototype.$localStorage.remove('_token_down');
        Vue.prototype.$localStorage.remove('_token_expiration');
        this.username = undefined;
        this.userData = undefined;
        this.serverUrl = undefined;
        this.synchronizeAuthState();
    }

    fetchUser() {
        if (this.getToken()) {
            this.synchronizeAuthState();
            return this.fetchUserData().then(() => this.synchronizeAuthState());
        } else {
            return Promise.resolve(false);
        }
    }

    fetchUserData() {
        return Vue.http.get('users/current')
            .then(response => {
                this.username = response.body.email;
                this.userData = response.body;
            })
            .catch(response => {
                if (response.status === 401 || response.status === 0 || response.status >= 500) {
                    this.forget();
                }
            })
            .then(() => {
                return Vue.http.get('server-info')
                    .then(({body: info}) => {
                        if (info.config) {
                            extendObject(Vue.config.external, info.config);
                        }
                        if (info.cloudVersion) {
                            Vue.prototype.compareFrontendAndBackendVersion(info.cloudVersion);
                        }
                    });
            });
    }
}


