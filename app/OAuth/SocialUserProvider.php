<?php

namespace App\OAuth;

use Laravel\Socialite\Facades\Socialite;
use Laravel\Passport\Bridge\User as UserEntity;
use League\OAuth2\Server\Exception\OAuthServerException;
use App\User;

class SocialUserProvider implements SocialUserProviderInterface
{
    public function __construct()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByAccessToken($provider, $accessToken)
    {
        $user = $this->getUserFromSocialProvider($provider, $accessToken);

        if (!$user) {
            return;
        }

        return new UserEntity($user->getAuthIdentifier());
    }

    /**
     * Get the user from the specified provider using the given access token.
     *
     * @param string  $provider
     * @param string  $accessToken
     *
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     *
     * @return \App\User
     */
    public function getUserFromSocialProvider($provider, $accessToken)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->userFromToken($accessToken);
        } catch (\Exception $ex) {
            throw new OAuthServerException(
                'Authentication error, invalid access token',
                $errorCode = 400,
                'invalid_request'
            );
        }

        return $this->findOrCreateSocialUser(
            $socialiteUser,
            $provider,
        );
    }

    private function findOrCreateSocialUser($socialiteUser, $provider)
    {
        return User::firstOrCreate(
            [
                'email'             => $socialiteUser->getEmail(),
            ],
            [
                'email_verified_at' => now(),
                'name'              => $socialiteUser->getName(),
                'social_id'         => $socialiteUser->getId(),
                'service'           => $provider,
                'social_name'       => $socialiteUser->getName(),
                'social_username'   => $socialiteUser->getNickname(),
                'social_avatar'     => $socialiteUser->getAvatar(),
            ],
        );
    }
}
