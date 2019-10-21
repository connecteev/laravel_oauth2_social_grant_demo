README for laravel_oauth2_social_grant_demo
----------------
This walks through the end-to-end social grant Authentication (for Social login using Laravel Socialite and Laravel Passport).

End-to-end social auth flow:
----------------
1. [User] Clicks login
2. [Frontend] redirects to backend to get code from AuthorizationURL
3. [Backend] forwards to provider's authorize URL to get code
4. [3rd party Provider] forwards to callback URL with code (Frontend)
5. [Frontend] makes post request with service name and code -> Backend
6. [Backend] verifies code and exchanges it for token from provider
7. [Backend] exchanges provider token for Passport token
8. [Backend] responds with XHR to frontend with Passport access_token in body
9. [Frontend] sets cookie and logs the user in

INSTALLATION:
----------------
Run:
```
- composer install
- php artisan migrate --seed
- php artisan passport:install
```
Note the Laravel Passport Credentials: the value for client id and client secret corresponding to the Password Grant Client in oauth_clients table (ex: "Laravel Password Grant Client")

Create a new oAuth app with your provider. Example: github in this case
Put the values for client ID, client secret and redirect URL in your Laravel .env (these values MUST match exactly)
```
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URL=http://localhost:3000/auth/socialCallback/github  (or whatever your callback URL will be)
```

Testing with Browser: POSTMAN:
Steps 1-4:
- Go to your provider's authorization URL. For Github it is: https://github.com/login/oauth/authorize?client_id=<GITHUB_CLIENT_ID>
- You'll get redirected (by the provider) to a URL like http://localhost:3000/auth/socialCallback/github?code=a1228e4218c5bf7fafad
- Copy the value of code in the URL

Steps 5-6:
- Use POSTMAN to make POST request to your provider's URL: https://github.com/login/oauth/access_token
Put these in the body:
- client_id: set to GITHUB_CLIENT_ID
- client_secret: set to GITHUB_CLIENT_SECRET
- redirect_uri: set to GITHUB_REDIRECT_URL
- code: the code that you got from steps 1-4 above

The response will be something like:
{
    "access_token": "ce754633a3b1f1222d212c1eb97ddd7cac2bd582",
    "token_type": "bearer",
    "scope": "read:user,user:email"
}
Copy the value of access_token

Steps 7-8:
Use POSTMAN to make POST request to your server's Laravel Passport endpoint: http://localhost:8000/oauth/token
Put these in the body:
- client_id: set to Laravel Passport's Password Grant Client ID
- client_secret: set to Laravel Passport's Password Grant Client Secret
- grant_type: set to 'social'
- provider: set to provider name ('github' in this example. Other values could be 'google', 'facebook', 'linkedin', 'twitter', 'gitlab' etc)
- access_token: the access token that you got from steps 5-6 above

The response will be something like this:

```
{
    "token_type": "Bearer",
    "expires_in": 31622400,
    "access_token": "eyJ0e22iOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVkN33hZGZiNTA5MmVlOTlkYWZjZWQ5N2NmODFjNjM3MTk5ZmNlZTk5ZGQyZGZhOTZmNGI0MzlhNDdlOWFjZGJkYjI3NmFkZDE3ODFjN2FjIn0.eyJhdWQiOiIyIiwianRpIjoiZWQ2OGFkZmI1MDkyZWU5OWRhZmNlZDk3Y2Y4MWM2MzcxOTlmY2VlOTlkZDJkZmE5NmY0YjQzOWE0N2U5YWNkYmRiMjc2YWRkMTc4MWM3YWMiLCJpYXQiOjE1NzE2OTUxNjUsIm5iZiI6MTU3MTY5NTE2NSwiZXhwIjoxNjAzMzE3NTY1LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.R9yPwjmXPICzM3yi6liuZmstgj1L9wQG_8BlhGWy6dp6D_Airh9HF69BkXBYfBrf57XSsnEyFR5ApHvIznaIb7jhucfzR4UEUYsQKulylh71Tjm_E8N9aYGXgr1sHrGTwW99QUe-tbB28JsNzkyFCoOpw9XK-qOWMo8AGiyvw8iVMeanh3CxGdfO1XXAwpFgomsukc_Ck4FquC-Vw62qH8EtSDKkhV56SaXZaVxoFX0YvS3HAiDqHQH8u9z_EyXAPWVrx8b6EGZkwbSR4z6pIlqoLMuj0qbmvheBEWWV8IVQAIQjCuxMpfjgwSkxJZkybc22UmUAlvOFIkN8bK4ho-rFD1GdKDavx_yepn55mpy3SHUs4rX3TI5jX9m0vwhoFc_05FjmjX5QnYDVeOcZeLgDREp7puJ-BQT-zzBJEIorKTYA6Ie4FlIDn_Io7Bh0-1iWH9cYzD9NeDsNJJlKMB9GvjMDUlNLjkJ9fq8rAJgn8pSnCPsy0qBtEhjOBR-6h49yBmGY60hTg0XEFASFG3uNhA3d50V1dGTfTQqa5Y1fLVl4J6JxFI1YcCapTuo6-1nFf0ucsaRIP0GFwihyUtFEKa9w26gS90Aju8ZsTeTaOOcXvONeYj8nlXI420g7LbSJM-CDOX_VO4bWmp6t-nTVTkg09XNDzldeknvqaxY",
    "refresh_token": "def5020035bfe94b44ce2804d8a3a7b3d4a436e3575579f1124da5bca7ba15a55b8daad7e8aec1d343abf31ff6574ca486dc6299bf2aaae6d553311cc016cbd3a05e4474d4931ea4cf5d79bfb0bf6c3b3778d4ce0f7b4c380ac364a3e065979c9978ebbb29c810fb05e2e2761a3c4930123cbcf73acb25a7be616d6e7bb3b9a7d53e2f1dd0783e00dcf17d605ba674806852b6dcb6275ff7dbcee4502b38baf9b5d51194616f93152699e4cad06bee7e76d369bcf7c9a1d1e28013134cb7f079d19a536cceaa7953eab4f61bbf539d37fe059f61e2591b9ab9d2cbcb066e69c995b1080230ba2dd12ca22adb300921e376790a282fe37732619e02b82131f7b1f231d63f2433051a85d6d3c917adb7f24514c42568bb3352c0fb9b24a7613c2c60b6493a5c3ae1501194bff939e06f7ba90331fb7b9ac34aaf0172fc2f979872dc57e6c86e19d7434a49a867ce047e2403ac79172f1dcf813f04f3bd02b2760307"
}
```

Note that Steps 5-8 can be wrapped into one Laravel endpoint, so that:
1. your first-party client (frontend) only has to make one Ajax request to the backend
2. front-end client does not need to know (or pass) values for GITHUB_CLIENT_SECRET, GITHUB_CLIENT_SECRET, Laravel Passport's Password Grant Client values (id and secret)

The endpoint may end up looking like this: 
http://localhost:8000/api/v1/auth/login/github/socialLoginGetAuthToken
and could accept these parameters in the POST body:
```
service: github
code: the code that you got from steps 1-4 above
```
