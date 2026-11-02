describe('OAuth tests', () => {
  before(() => {
    // Local runs may opt into seeding a dedicated Cypress database. Never seed
    // automatically when this nightly spec targets a shared environment.
    if (Cypress.env('seedLocalOAuthDb')) {
      cy.task('seed:database', '07_oauth_browser.sql');
    }
  });

  const authorizeUrl =
    '/oauth/v2/auth?client_id=2_2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck&redirect_uri=http://suplascripts.local/authorize&response_type=code&scope=account_r%20iodevices_r%20channels_ea%20channels_r%20offline_access%20state_webhook&state=a%2Bb%3D%26c';

  const stubPresentation = () => {
    cy.intercept(
      {method: 'GET', pathname: '/api/oauth/authorization-request'},
      {
        statusCode: 200,
        delay: 1000,
        body: {
          application: {name: 'Permission screen test'},
          scopes: {requested: ['channels_rw', 'account_r'], implicit: ['channels_r']},
          status: 'unauthenticated',
          canAutoAuthorize: false,
        },
      }
    );
  };

  const stubBrowserTokenBootstrap = () => {
    cy.intercept({method: 'GET', pathname: '/api/**'}, (request) => {
      const pathname = new URL(request.url).pathname;
      if (pathname === '/api/server-info') {
        request.reply({fixture: 'server-info.json'});
      } else if (pathname === '/api/users/current') {
        request.reply({fixture: 'current-user.json'});
      } else if (pathname === '/api/channels/states') {
        request.reply({statusCode: 200, body: {states: [], devices: []}});
      } else {
        request.reply({statusCode: 200, body: []});
      }
    });
  };

  it('shows verified application permissions without changing the authorization URL', () => {
    const authorizationUrl =
      '/oauth/v2/auth?client_id=2_2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck&redirect_uri=http://suplascripts.local/authorize&response_type=code&scope=account_r%20iodevices_r%20channels_ea%20channels_r%20offline_access%20state_webhook&state=a%2Bb%3D%26c';
    cy.intercept(
      {method: 'GET', pathname: '/api/oauth/authorization-request'},
      {
        statusCode: 200,
        body: {
          application: {name: 'Permission screen test'},
          scopes: {requested: ['channels_rw', 'account_r'], implicit: ['channels_r']},
          status: 'unauthenticated',
          canAutoAuthorize: false,
        },
      }
    ).as('oauthRequest');

    cy.visit(authorizationUrl);
    cy.wait('@oauthRequest').then(({request}) => {
      expect(new URL(request.url).pathname).to.eq('/api/oauth/authorization-request');
      expect(request.url).to.include('state=a%2Bb%3D%26c');
    });
    cy.contains('Permission screen test').should('be.visible');
    cy.contains('Channels').should('be.visible');
    cy.contains('Modification').should('be.visible');
    cy.contains('Read').should('be.visible');
    cy.contains('Sign in to continue').should('be.visible');
    cy.location('pathname').should('eq', '/oauth/v2/auth');
    cy.location('search').should('eq', authorizationUrl.substring(authorizationUrl.indexOf('?')));
    cy.document().its('body.innerHTML').should('not.contain', 'window.oauthAuthorizeRequest');
  });

  it('automatically requests a new code when a browser token already has the complete grant', function () {
    cy.viewport(1280, 900);
    stubBrowserTokenBootstrap();
    cy.intercept(
      {method: 'GET', pathname: '/api/oauth/authorization-request'},
      {
        statusCode: 200,
        body: {
          application: {name: 'Existing grant application'},
          scopes: {requested: ['account_r'], implicit: []},
          status: 'already_authorized',
          canAutoAuthorize: true,
          email: 'user@example.com',
        },
      }
    ).as('existingGrant');
    cy.intercept({method: 'POST', pathname: '/api/oauth/authorize'}, (request) => {
      expect(request.body).to.include('target_token');
      expect(request.body).to.include('browser-webapp-token');
      expect(request.body).not.to.include('password');
      request.reply({
        statusCode: 200,
        body: {outcome: 'authorized', redirectUrl: 'http://localhost:8008/oauth-callback-test?code=auto&state=a%2Bb'},
      });
    }).as('autoAuthorize');

    cy.visit(authorizeUrl, {
      onBeforeLoad(window) {
        window.localStorage.setItem('supla-user-token', 'browser-webapp-token');
      },
    });
    cy.wait('@existingGrant');
    cy.wait('@autoAuthorize');
    cy.location('pathname').should('eq', '/oauth-callback-test');
    cy.location('search').should('eq', '?code=auto&state=a%2Bb');
  });

  it('escalates to a password when the target says the grant no longer covers requested scopes', function () {
    cy.viewport(1280, 900);
    stubBrowserTokenBootstrap();
    cy.intercept(
      {method: 'GET', pathname: '/api/oauth/authorization-request'},
      {
        statusCode: 200,
        delay: 1000,
        body: {
          application: {name: 'Expanded grant application'},
          scopes: {requested: ['account_r', 'channels_rw'], implicit: []},
          status: 'already_authorized',
          canAutoAuthorize: true,
          email: 'user@example.com',
        },
      }
    ).as('existingGrant');
    let attempt = 0;
    cy.intercept({method: 'POST', pathname: '/api/oauth/authorize'}, (request) => {
      attempt += 1;
      if (attempt === 1) {
        expect(request.body).to.include('target_token');
        expect(request.body).not.to.include('password');
        request.reply({statusCode: 401, body: {outcome: 'error', errorCode: 'password_required'}});
      } else {
        expect(request.body).to.include('password');
        expect(request.body).to.include('fresh-password');
        expect(request.body).not.to.include('target_token');
        request.reply({
          statusCode: 200,
          body: {outcome: 'authorized', redirectUrl: 'http://localhost:8008/oauth-callback-test?code=expanded'},
        });
      }
    }).as('authorizationAttempt');

    cy.visit(authorizeUrl, {
      onBeforeLoad(window) {
        window.localStorage.setItem('supla-user-token', 'browser-webapp-token');
      },
    });
    cy.wait('@existingGrant');
    cy.wait('@authorizationAttempt');
    cy.get('[role="alert"]').should('contain', 'Enter your password to approve these permissions.');
    cy.get('#oauth-password').scrollIntoView();
    cy.get('#oauth-password').type('fresh-password', {force: true});
    cy.contains('button', 'Sign in and grant access').click();
    cy.wait('@authorizationAttempt');
    cy.location('pathname').should('eq', '/oauth-callback-test');
  });

  it('clears an expired browser token and retries authorization details without changing the URL', function () {
    cy.viewport(1280, 900);
    stubBrowserTokenBootstrap();
    let requestCount = 0;
    cy.intercept({method: 'GET', pathname: '/api/oauth/authorization-request'}, (request) => {
      requestCount += 1;
      if (requestCount === 1) {
        expect(request.headers.authorization).to.eq('Bearer expired-browser-token');
        request.reply({statusCode: 401, delay: 2000, body: {error: 'invalid_token'}});
      } else {
        expect(request.headers.authorization).to.be.undefined;
        request.reply({
          statusCode: 200,
          body: {
            application: {name: 'Recoverable application'},
            scopes: {requested: ['account_r'], implicit: []},
            status: 'unauthenticated',
            canAutoAuthorize: false,
          },
        });
      }
    }).as('authorizationDetails');

    cy.visit(authorizeUrl, {
      onBeforeLoad(window) {
        window.localStorage.setItem('supla-user-token', 'expired-browser-token');
      },
    });
    cy.wait('@authorizationDetails');
    cy.wait('@authorizationDetails');
    cy.contains('Recoverable application').should('be.visible');
    cy.get('form').should('be.visible');
    cy.location('pathname').should('eq', '/oauth/v2/auth');
    cy.location('search').should('eq', authorizeUrl.substring(authorizeUrl.indexOf('?')));
  });

  it('keeps public authorization on the broker until the user explicitly selects a private Cloud', function () {
    cy.viewport(1280, 900);
    const privateAuthorizationUrl =
      '/oauth/v2/auth?client_id=2_2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck&redirect_uri=http%3A%2F%2Fsuplascripts.local%2Fauthorize&response_type=code&scope=account_r&state=private%2Bstate&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM&code_challenge_method=S256';
    cy.intercept({method: 'GET', pathname: '/api/server-info'}, {fixture: 'server-info-broker.json'});
    cy.intercept(
      {method: 'GET', pathname: '/api/oauth/authorization-request'},
      {
        statusCode: 200,
        body: {
          application: {name: 'Private selection application'},
          scopes: {requested: ['account_r'], implicit: []},
          status: 'unauthenticated',
          canAutoAuthorize: false,
          privateCloudSelectionAvailable: true,
        },
      }
    );
    cy.intercept({method: 'POST', pathname: '/api/oauth/private-cloud'}, (request) => {
      expect(request.body).to.include('targetCloud');
      expect(request.body).to.include('private.cloud');
      expect(request.body).to.include('state');
      expect(request.body).to.include('private+state');
      expect(request.body).to.include('code_challenge');
      expect(request.body).not.to.include('password');
      expect(request.body).not.to.include('target_token');
      request.reply({
        statusCode: 200,
        body: {
          redirectUrl:
            'https://private.cloud/oauth/v2/auth?client_id=1_mapped&redirect_uri=http%3A%2F%2Fsuplascripts.local%2Fauthorize&response_type=code&scope=account_r&state=private%2Bstate&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM&code_challenge_method=S256',
        },
      });
    }).as('privateCloudSelection');
    cy.intercept('https://private.cloud/oauth/v2/auth*', {
      statusCode: 200,
      body: '<!doctype html><html><body>Private authorization target</body></html>',
      headers: {'content-type': 'text/html'},
    });

    cy.visit(privateAuthorizationUrl);
    cy.contains('Private selection application').should('be.visible');
    cy.location('pathname').should('eq', '/oauth/v2/auth');
    cy.location('search').should('eq', privateAuthorizationUrl.substring(privateAuthorizationUrl.indexOf('?')));
    cy.contains('Connect to a private SUPLA Cloud instance').click();
    cy.get('#oauth-private-cloud').type('private.cloud');
    cy.contains('button', 'Continue to private Cloud').click();
    cy.wait('@privateCloudSelection');
    cy.origin('https://private.cloud', () => {
      cy.contains('Private authorization target').should('be.visible');
      cy.location('hostname').should('eq', 'private.cloud');
      cy.location('search').should('include', 'state=private%2Bstate');
      cy.location('search').should('include', 'code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM');
    });
  });

  it('shows a safe error and stays on the broker when a private Cloud destination is rejected', function () {
    cy.intercept({method: 'GET', pathname: '/api/server-info'}, {fixture: 'server-info-broker.json'});
    cy.intercept(
      {method: 'GET', pathname: '/api/oauth/authorization-request'},
      {
        statusCode: 200,
        body: {
          application: {name: 'Rejected private selection'},
          scopes: {requested: ['account_r'], implicit: []},
          status: 'unauthenticated',
          canAutoAuthorize: false,
        },
      }
    );
    cy.intercept(
      {method: 'POST', pathname: '/api/oauth/private-cloud'},
      {
        statusCode: 400,
        body: {outcome: 'error', errorCode: 'invalid_target'},
      }
    ).as('rejectedPrivateCloud');

    cy.visit(authorizeUrl);
    cy.contains('Connect to a private SUPLA Cloud instance').click();
    cy.get('#oauth-private-cloud').scrollIntoView();
    cy.get('#oauth-private-cloud').type('http://127.0.0.1', {force: true});
    cy.contains('button', 'Continue to private Cloud').click();
    cy.wait('@rejectedPrivateCloud');
    cy.get('[role="alert"]').should('contain', 'Enter a valid private Cloud domain name.');
    cy.location('pathname').should('eq', '/oauth/v2/auth');
    cy.location('search').should('eq', authorizeUrl.substring(authorizeUrl.indexOf('?')));
  });

  it('keeps the broker URL for remote approval until the structured callback', function () {
    cy.viewport(1280, 900);
    stubPresentation();
    cy.intercept({method: 'POST', pathname: '/api/oauth/authorize'}, (request) => {
      expect(request.body).to.include('decision');
      expect(request.body).to.include('approve');
      expect(request.body).to.include('password');
      expect(request.body).not.to.include('targetCloud');
      expect(request.body).not.to.include('target_address');
      request.reply({
        delay: 500,
        statusCode: 200,
        body: {outcome: 'authorized', redirectUrl: 'http://localhost:8008/oauth-callback-test?code=one&state=a%2Bb'},
      });
    }).as('approve');

    cy.visit(authorizeUrl);
    cy.contains('button', 'Sign in and grant access').should('be.visible');
    cy.get('#oauth-username').type('remote@example.com');
    cy.get('#oauth-password').type('test-password');
    cy.contains('button', 'Sign in and grant access').click();
    cy.location('pathname').should('eq', '/oauth/v2/auth');
    cy.location('search').should('eq', authorizeUrl.substring(authorizeUrl.indexOf('?')));
    cy.wait('@approve');
    cy.location('pathname').should('eq', '/oauth-callback-test');
    cy.location('search').should('eq', '?code=one&state=a%2Bb');
  });

  it('denies local authorization and navigates to the structured denial destination', function () {
    stubPresentation();
    cy.intercept({method: 'POST', pathname: '/api/oauth/authorize'}, (request) => {
      expect(request.body).to.include('decision');
      expect(request.body).to.include('deny');
      expect(request.body).not.to.include('name="password"');
      request.reply({
        statusCode: 200,
        body: {outcome: 'denied', redirectUrl: 'http://localhost:8008/oauth-callback-test?error=access_denied&state=a%2Bb'},
      });
    }).as('deny');

    cy.visit(authorizeUrl);
    cy.contains('button', 'Deny').click();
    cy.wait('@deny');
    cy.location('pathname').should('eq', '/oauth-callback-test');
    cy.location('search').should('eq', '?error=access_denied&state=a%2Bb');
  });

  [
    ['target_unavailable', 'The account cloud is temporarily unavailable. Please try again.', 503],
    ['unsupported_authorization_api', 'This account cloud needs an update before it can authorize this application.', 503],
    ['unknown_target', 'We could not find an account cloud for this e-mail address. Check the address and try again.', 404],
    ['invalid_client', 'This application is not registered on the account cloud and cannot be authorized.', 400],
  ].forEach(([errorCode, message, statusCode]) => {
    it(`keeps the broker authorization URL for remote ${errorCode} errors`, function () {
      cy.viewport(1280, 900);
      stubPresentation();
      cy.intercept(
        {method: 'POST', pathname: '/api/oauth/authorize'},
        {
          statusCode,
          body: {outcome: 'error', errorCode},
        }
      ).as('remoteAuthorizationError');

      cy.visit(authorizeUrl);
      cy.get('#oauth-username').type('remote@example.com');
      cy.get('#oauth-password').type('test-password');
      cy.contains('button', 'Sign in and grant access').click();
      cy.wait('@remoteAuthorizationError');
      cy.get('[role="alert"]').should('contain', message);
      cy.location('pathname').should('eq', '/oauth/v2/auth');
      cy.location('search').should('eq', authorizeUrl.substring(authorizeUrl.indexOf('?')));
    });
  });

  it('shows a safe retired response for stale legacy OAuth login pages', () => {
    stubPresentation();
    cy.visit(authorizeUrl);
    cy.contains('Permission screen test').should('be.visible');

    cy.intercept({method: 'GET', pathname: '/oauth/v2/auth_login'}).as('legacyLogin');
    cy.visit('/oauth/v2/auth_login', {failOnStatusCode: false});
    cy.wait('@legacyLogin').then(({response}) => {
      expect(response.statusCode).to.eq(410);
      expect(response.headers['cache-control']).to.include('no-store');
    });
    cy.contains('This OAuth login page is no longer available. Restart authorization from the application.').should('be.visible');
    cy.get('form').should('not.exist');

    cy.go('back');
    cy.location('pathname').should('eq', '/oauth/v2/auth');
    cy.location('search').should('eq', authorizeUrl.substring(authorizeUrl.indexOf('?')));
    cy.contains('Permission screen test').should('be.visible');
  });
});
