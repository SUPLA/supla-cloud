import {mount} from '@vue/test-utils';
import {afterEach, describe, expect, it, vi} from 'vitest';

const {get, post, currentUser, frontendConfig} = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn(),
  currentUser: {userToken: null, forgetOAuthToken: vi.fn()},
  frontendConfig: {config: {actAsBrokerCloud: true}},
}));
vi.mock('@/api/api', () => ({api: {get, post}}));
vi.mock('@/stores/current-user-store', () => ({useCurrentUserStore: () => currentUser}));
vi.mock('@/stores/frontend-config-store', () => ({useFrontendConfigStore: () => frontendConfig}));

import OAuthAuthorizeForm from '@/login/oauth-authorize-form.vue';

describe('OAuthAuthorizeForm request errors', () => {
  afterEach(() => {
    get.mockReset();
    post.mockReset();
    currentUser.userToken = null;
    currentUser.forgetOAuthToken.mockReset();
  });

  it.each([
    [{status: 401}, 'Sign in to continue'],
    [{status: 403}, 'Access to this authorization request is unavailable.'],
    [new Error('offline'), 'Authorization details are temporarily unavailable. Please try again.'],
  ])('shows an appropriate message for %s', async (error, message) => {
    get.mockRejectedValue(error);
    const wrapper = mount(OAuthAuthorizeForm, {
      global: {mocks: {$t: (text) => text}},
    });
    await new Promise((resolve) => setTimeout(resolve));

    expect(wrapper.get('[role="alert"]').text()).toBe(message);
  });

  it('posts the original authorization request and entered credentials for approval', async () => {
    get.mockResolvedValue({
      body: {
        application: {name: 'Test application'},
        scopes: {requested: ['account_r'], implicit: []},
        status: 'unauthenticated',
        canAutoAuthorize: false,
      },
    });
    post.mockRejectedValue({body: {errorCode: 'invalid_credentials'}, status: 401});
    window.history.replaceState(
      {},
      '',
      '/oauth/v2/auth?client_id=client&redirect_uri=https%3A%2F%2Fapp.test%2Fcb&response_type=code&scope=account_r&state=a%2Bb'
    );
    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));
    wrapper.vm.username = 'user@example.com';
    wrapper.vm.password = 'wrong-password';
    await wrapper.vm.$nextTick();
    await wrapper.get('form').trigger('submit');
    await new Promise((resolve) => setTimeout(resolve));

    expect(post).toHaveBeenCalledOnce();
    const [endpoint, form] = post.mock.calls[0];
    expect(endpoint).toBe('oauth/authorize');
    expect(form).toBeInstanceOf(FormData);
    expect(Object.fromEntries(form.entries())).toEqual({
      client_id: 'client',
      redirect_uri: 'https://app.test/cb',
      response_type: 'code',
      scope: 'account_r',
      state: 'a+b',
      decision: 'approve',
      username: 'user@example.com',
      password: 'wrong-password',
    });
    expect(wrapper.get('[role="alert"]').text()).toBe('The e-mail or password is incorrect.');
  });

  it('posts a denial without sending a password', async () => {
    get.mockResolvedValue({
      body: {
        application: {name: 'Test application'},
        scopes: {requested: ['account_r'], implicit: []},
        status: 'unauthenticated',
        canAutoAuthorize: false,
      },
    });
    post.mockRejectedValue({body: {errorCode: 'invalid_request'}, status: 400});
    window.history.replaceState(
      {},
      '',
      '/oauth/v2/auth?client_id=client&redirect_uri=https%3A%2F%2Fapp.test%2Fcb&response_type=code&scope=account_r&state=deny-state'
    );
    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));
    wrapper.vm.username = 'user@example.com';
    await wrapper.vm.$nextTick();
    await wrapper.get('button.btn-default').trigger('click');

    const [, form] = post.mock.calls[0];
    expect(Object.fromEntries(form.entries())).toEqual({
      client_id: 'client',
      redirect_uri: 'https://app.test/cb',
      response_type: 'code',
      scope: 'account_r',
      state: 'deny-state',
      decision: 'deny',
      username: 'user@example.com',
    });
  });

  it.each([
    [{status: 403}, 'Access to this authorization request is unavailable.'],
    [new Error('offline'), 'Authorization is temporarily unavailable. Please try again.'],
  ])('keeps authorization submission failures distinguishable: %s', async (failure, message) => {
    get.mockResolvedValue({body: {application: {name: 'Test application'}, scopes: {requested: [], implicit: []}}});
    post.mockRejectedValue(failure);
    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));
    wrapper.vm.username = 'user@example.com';
    await wrapper.vm.$nextTick();
    await wrapper.get('button.btn-default').trigger('click');
    await new Promise((resolve) => setTimeout(resolve));

    expect(wrapper.get('[role="alert"]').text()).toBe(message);
    expect(wrapper.get('form').exists()).toBe(true);
  });

  it('automatically exchanges a valid prior grant using the existing browser token without asking for a password', async () => {
    currentUser.userToken = 'verified-webapp-token';
    get.mockResolvedValue({
      body: {
        application: {name: 'Test application'},
        scopes: {requested: ['account_r'], implicit: []},
        status: 'already_authorized',
        canAutoAuthorize: true,
        email: 'user@example.com',
      },
    });
    post.mockRejectedValue({body: {outcome: 'error', errorCode: 'target_unavailable'}, status: 503});
    window.history.replaceState(
      {},
      '',
      '/oauth/v2/auth?client_id=client&redirect_uri=https%3A%2F%2Fapp.test%2Fcb&response_type=code&scope=account_r&state=auto'
    );
    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));

    expect(post).toHaveBeenCalledOnce();
    expect(Object.fromEntries(post.mock.calls[0][1].entries())).toEqual({
      client_id: 'client',
      redirect_uri: 'https://app.test/cb',
      response_type: 'code',
      scope: 'account_r',
      state: 'auto',
      decision: 'approve',
      username: 'user@example.com',
      target_token: 'verified-webapp-token',
    });
    expect(wrapper.get('form').exists()).toBe(true);
    expect(wrapper.get('[role="alert"]').text()).toBe('The account cloud is temporarily unavailable. Please try again.');
  });

  it('requires password when the target reports that the grant no longer covers the request', async () => {
    currentUser.userToken = 'verified-webapp-token';
    get.mockResolvedValue({
      body: {
        application: {name: 'Test application'},
        scopes: {requested: ['account_r', 'channels_rw'], implicit: []},
        status: 'already_authorized',
        canAutoAuthorize: true,
        email: 'user@example.com',
      },
    });
    post.mockRejectedValue({body: {outcome: 'error', errorCode: 'password_required'}, status: 401});

    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));

    expect(wrapper.get('form').exists()).toBe(true);
    expect(wrapper.find('#oauth-password').exists()).toBe(true);
    expect(wrapper.get('[role="alert"]').text()).toBe('Enter your password to approve these permissions.');
    expect(currentUser.forgetOAuthToken).not.toHaveBeenCalled();
  });

  it('clears an expired token and retries presentation without losing the OAuth URL', async () => {
    currentUser.userToken = 'expired-token';
    currentUser.forgetOAuthToken = vi.fn(() => {
      currentUser.userToken = null;
    });
    get.mockRejectedValueOnce({status: 401}).mockResolvedValueOnce({
      body: {
        application: {name: 'Test application'},
        scopes: {requested: ['account_r'], implicit: []},
        status: 'unauthenticated',
        canAutoAuthorize: false,
      },
    });
    const authorizationUrl = '/oauth/v2/auth?client_id=client&state=preserve%2Bstate';
    window.history.replaceState({}, '', authorizationUrl);

    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));

    expect(currentUser.forgetOAuthToken).toHaveBeenCalledOnce();
    expect(get).toHaveBeenCalledTimes(2);
    expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    expect(wrapper.get('form').exists()).toBe(true);
    expect(`${window.location.pathname}${window.location.search}`).toBe(authorizationUrl);
  });

  it('recovers when the target rejects a token during authorization', async () => {
    currentUser.userToken = 'revoked-token';
    currentUser.forgetOAuthToken = vi.fn(() => {
      currentUser.userToken = null;
    });
    get
      .mockResolvedValueOnce({
        body: {
          application: {name: 'Test application'},
          scopes: {requested: ['account_r'], implicit: []},
          status: 'already_authorized',
          canAutoAuthorize: true,
          email: 'user@example.com',
        },
      })
      .mockResolvedValueOnce({
        body: {
          application: {name: 'Test application'},
          scopes: {requested: ['account_r'], implicit: []},
          status: 'unauthenticated',
          canAutoAuthorize: false,
        },
      });
    post.mockRejectedValue({body: {outcome: 'error', errorCode: 'invalid_token'}, status: 401});

    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));
    await new Promise((resolve) => setTimeout(resolve));

    expect(currentUser.forgetOAuthToken).toHaveBeenCalledOnce();
    expect(get).toHaveBeenCalledTimes(2);
    expect(post).toHaveBeenCalledOnce();
    expect(wrapper.get('form').exists()).toBe(true);
    expect(wrapper.find('[role="alert"]').exists()).toBe(false);
  });

  it('explicitly requests a private Cloud handoff with the original OAuth intent and no credentials', async () => {
    get.mockResolvedValue({
      body: {
        application: {name: 'Test application'},
        scopes: {requested: ['account_r'], implicit: []},
        status: 'unauthenticated',
        canAutoAuthorize: false,
      },
    });
    post.mockRejectedValue({body: {outcome: 'error', errorCode: 'invalid_target'}, status: 400});
    window.history.replaceState(
      {},
      '',
      '/oauth/v2/auth?client_id=client&redirect_uri=https%3A%2F%2Fapp.test%2Fcb&response_type=code&scope=account_r&state=private%2Bstate&code_challenge=challenge&code_challenge_method=S256'
    );

    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));
    wrapper.vm.selectPrivateCloud = true;
    wrapper.vm.privateCloudHost = 'private.example';
    await wrapper.vm.$nextTick();
    await wrapper.get('#oauth-private-cloud').setValue('private.example');
    await wrapper.get('.private-cloud-form').trigger('submit');
    await new Promise((resolve) => setTimeout(resolve));

    expect(post).toHaveBeenCalledOnce();
    expect(post.mock.calls[0][0]).toBe(`${window.location.origin}/api/oauth/private-cloud`);
    const submitted = Object.fromEntries(post.mock.calls[0][1].entries());
    expect(submitted).toEqual({
      targetCloud: 'private.example',
      client_id: 'client',
      redirect_uri: 'https://app.test/cb',
      response_type: 'code',
      scope: 'account_r',
      state: 'private+state',
      code_challenge: 'challenge',
      code_challenge_method: 'S256',
    });
    expect(wrapper.get('[role="alert"]').text()).toBe('Enter a valid private Cloud domain name.');
    expect(`${window.location.pathname}${window.location.search}`).toContain('state=private%2Bstate');
  });

  it.each([
    ['target_unavailable', 'The account cloud is temporarily unavailable. Please try again.'],
    ['unsupported_authorization_api', 'This account cloud needs an update before it can authorize this application.'],
    ['unknown_target', 'We could not find an account cloud for this e-mail address. Check the address and try again.'],
    ['invalid_client', 'This application is not registered on the account cloud and cannot be authorized.'],
  ])('shows a safe actionable message for remote authorization error %s without navigating', async (errorCode, message) => {
    get.mockResolvedValue({body: {application: {name: 'Test application'}, scopes: {requested: [], implicit: []}}});
    post.mockRejectedValue({body: {outcome: 'error', errorCode}, status: 503});
    const authorizationUrl = '/oauth/v2/auth?client_id=client&redirect_uri=https%3A%2F%2Fapp.test%2Fcb&response_type=code&state=remote%2Bstate';
    window.history.replaceState({}, '', authorizationUrl);
    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));
    wrapper.vm.username = 'remote@example.com';
    await wrapper.vm.$nextTick();
    await wrapper.get('button.btn-default').trigger('click');
    await new Promise((resolve) => setTimeout(resolve));

    expect(wrapper.get('[role="alert"]').text()).toBe(message);
    expect(wrapper.get('form').exists()).toBe(true);
    wrapper.vm.username = 'corrected@example.com';
    wrapper.vm.password = 'updated-password';
    await wrapper.vm.$nextTick();
    await wrapper.get('form').trigger('submit');
    await new Promise((resolve) => setTimeout(resolve));

    expect(post).toHaveBeenCalledTimes(2);
    expect(Object.fromEntries(post.mock.calls[1][1].entries())).toMatchObject({
      username: 'corrected@example.com',
      password: 'updated-password',
    });
    expect(`${window.location.pathname}${window.location.search}`).toBe(authorizationUrl);
  });

  it('handles a structured authorization error response without navigating', async () => {
    get.mockResolvedValue({body: {application: {name: 'Test application'}, scopes: {requested: [], implicit: []}}});
    post.mockResolvedValue({body: {outcome: 'error', errorCode: 'target_unavailable'}});
    const authorizationUrl = '/oauth/v2/auth?client_id=client&state=structured';
    window.history.replaceState({}, '', authorizationUrl);
    const wrapper = mount(OAuthAuthorizeForm, {global: {mocks: {$t: (text) => text}}});
    await new Promise((resolve) => setTimeout(resolve));
    wrapper.vm.username = 'remote@example.com';
    await wrapper.vm.$nextTick();
    await wrapper.get('button.btn-default').trigger('click');
    await new Promise((resolve) => setTimeout(resolve));

    expect(wrapper.get('[role="alert"]').text()).toBe('The account cloud is temporarily unavailable. Please try again.');
    expect(`${window.location.pathname}${window.location.search}`).toBe(authorizationUrl);
  });
});
