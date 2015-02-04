<?php namespace App\Provider\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

use Symfony\Component\HttpFoundation\RedirectResponse;

class PushbulletProvider extends AbstractProvider implements ProviderInterface {

	/**
	 * {@inheritdoc}
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://www.pushbullet.com/authorize', $state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTokenUrl()
	{
		return 'https://api.pushbullet.com/oauth2/token';
	}

	/**
	 * Get the access token for the given code.
	 *
	 * @param  string  $code
	 * @return string
	 */
	public function getAccessToken($code)
	{
		$response = $this->getHttpClient()->post($this->getTokenUrl(), [
			'body' => $this->getTokenFields($code),
		]);

		return $this->parseAccessToken($response->getBody());
	}

	/**
	 * Get the POST fields for the token request.
	 *
	 * @param  string  $code
	 * @return array
	 */
	protected function getTokenFields($code)
	{
		return array_add(
			parent::getTokenFields($code), 'grant_type', 'authorization_code'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getUserByToken($token)
	{
        // Can catch token here
//        dd($token);

        $response = $this->getHttpClient()->get('https://api.pushbullet.com/v2/users/me', [
			'headers' => [
				'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
			],
		]);

		return json_decode($response->getBody(), true);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function mapUserToObject(array $user)
	{
		return (new User)->setRaw($user)->map([
			'id' => $user['iden'],
            'nickname' => null,
            'name' => $user['name'],
			'email' => $user['email'],
            'avatar' => array_get($user, 'image_url'),
		]);
	}

}
