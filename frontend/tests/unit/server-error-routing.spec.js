import {afterEach, describe, expect, it, vi} from 'vitest';
import {createMemoryHistory, createRouter} from 'vue-router';

afterEach(() => {
  delete window.errorDetails;
  vi.resetModules();
});

async function resolveRoute(path) {
  const {default: routes} = await import('@/router/routes');
  return createRouter({history: createMemoryHistory(), routes}).resolve(path);
}

describe('server error routing', () => {
  it.each(['/oauth/v2/auth?client_id=unknown', '/oauth-authorize', '/login'])('uses the error page instead of the normal route for %s', async (path) => {
    window.errorDetails = {statusCode: 400, guruMeditation: 'invalid_client: Unknown OAuth application.'};
    const route = await resolveRoute(path);
    expect(route.matched[0].path).toBe('/:pathMatch(.*)*');
    expect(route.meta).toMatchObject({unrestricted: true, bodyClass: 'red'});
    expect(route.fullPath).toBe(path);
  });

  it('keeps normal OAuth routing when the server has not provided an error', async () => {
    const route = await resolveRoute('/oauth/v2/auth?client_id=valid');
    expect(route.matched[0].path).toBe('/oauth/v2/auth');
    expect(route.meta.bodyClass).toBe('centered-form-page');
  });
});
