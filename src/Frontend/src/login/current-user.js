import Vue from "vue";
import {Base64} from 'js-base64';

export class CurrentUser {
    constructor() {
        this.synchronizeAuthState();
    }

    getToken() {
        return localStorage.getItem('_token');
    }

    getRefreshToken() {
        return localStorage.getItem('_rtoken');
    }

    synchronizeAuthState() {
        Vue.http.headers.common['Authorization'] = this.getToken() ? 'Bearer ' + this.getToken() : undefined;
        const serverUrl = Base64.decode((this.getToken() || '').split('.')[1] || '');
        Vue.http.options.root = serverUrl + Vue.config.external.baseUrl + '/api';
    }

    authenticate(username, password) {
        return Vue.http.post('webapp-auth', {username, password}, {skipErrorHandler: [401]})
            .then(response => this.handleNewToken(response))
            .then(() => this.fetchUserData());
    }

    refreshToken() {
        return Vue.http.post('webapp-tokens?grant_type=refresh_token', {refresh_token: this.getRefreshToken()}, {skipErrorHandler: [401]})
            .then(response => this.handleNewToken(response))
            .catch(() => this.forget());
    }

    handleNewToken(response) {
        localStorage.setItem('_token', response.body.access_token);
        localStorage.setItem('_rtoken', response.body.refresh_token);
        this.synchronizeAuthState();
        const refreshTokenTimeout = (response.body.expires_in || 300) * .9 * 1000;
        setTimeout(() => this.refreshToken(), refreshTokenTimeout);
    }

    forget() {
        localStorage.removeItem('_token');
        localStorage.removeItem('_rtoken');
        this.synchronizeAuthState();
        this.username = undefined;
        this.userData = undefined;
    }

    refreshUser() {
        if (this.getToken()) {
            return this.fetchUserData().then(() => {
                if (this.username) {
                    return this.refreshToken();
                }
            });
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


