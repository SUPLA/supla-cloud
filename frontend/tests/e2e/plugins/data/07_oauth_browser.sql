INSERT INTO supla_oauth_clients
(id, random_id, redirect_uris, secret, allowed_grant_types, `type`, user_id, name, description, public_client_id, long_description)
VALUES (2, '2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck',
        'a:1:{i:0;s:35:"http://suplascripts.local/authorize";}',
        'local-oauth-e2e-secret', 'a:2:{i:0;s:18:"authorization_code";i:1;s:13:"refresh_token";}',
        1, NULL, 'OAuth browser e2e application', NULL, NULL, NULL);
