import Vue from "vue";
import {Base64} from 'js-base64';

export class CurrentUser {
    constructor() {
        this.synchronizeAuthState();
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
        moment.tz.setDefault(this.userData && this.userData.timezone || undefined);
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
    }

    authenticate(username, password) {
        return Vue.http.post('webapp-auth', {username, password}, {skipErrorHandler: [401, 429]})
            .then(response => this.handleNewToken(response))
            .then(() => this.fetchUserData());
    }

    handleNewToken(response) {
        Vue.prototype.$localStorage.set('_token', response.body.access_token);
        Vue.prototype.$localStorage.set('_token_down', response.body.download_token);
        this.synchronizeAuthState();
    }

    forget() {
        Vue.prototype.$localStorage.remove('_token');
        Vue.prototype.$localStorage.remove('_token_down');
        this.synchronizeAuthState();
        this.username = undefined;
        this.userData = undefined;
        this.serverUrl = undefined;
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
                return this.userData;
            })
            .catch(response => {
                if (response.status == 401) {
                    this.forget();
                }
            });
    }
}


