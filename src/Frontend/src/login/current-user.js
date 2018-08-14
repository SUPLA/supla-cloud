import Vue from "vue";
import {Base64} from 'js-base64';

export class CurrentUser {
    constructor() {
        this.synchronizeAuthState();
    }

    getToken() {
        return localStorage.getItem('_token');
    }

    synchronizeAuthState() {
        Vue.http.headers.common['Authorization'] = this.getToken() ? 'Bearer ' + this.getToken() : undefined;
        const serverUrl = Base64.decode((this.getToken() || '').split('.')[1] || '');
        Vue.http.options.root = serverUrl + Vue.config.external.baseUrl + '/api';
        moment.tz.setDefault(this.userData && this.userData.timezone || undefined);
    }

    authenticate(username, password) {
        return Vue.http.post('webapp-auth', {username, password}, {skipErrorHandler: [401, 429]})
            .then(response => this.handleNewToken(response))
            .then(() => this.fetchUserData());
    }

    handleNewToken(response) {
        localStorage.setItem('_token', response.body.access_token);
        this.synchronizeAuthState();
        window.SS = () => this.synchronizeAuthState();
    }

    forget() {
        localStorage.removeItem('_token');
        this.synchronizeAuthState();
        this.username = undefined;
        this.userData = undefined;
    }

    fetchUser() {
        if (this.getToken()) {
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
                if (response.status == 401) {
                    this.forget();
                }
            });
    }
}


