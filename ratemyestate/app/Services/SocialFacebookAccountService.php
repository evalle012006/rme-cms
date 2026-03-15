<?php
namespace App\Services;
use App\Models\SocialFacebookAccount;
use App\Models\User;
use Laravel\Socialite\Contracts\User as ProviderUser;
class SocialFacebookAccountService
{
    public function createOrGetUser(ProviderUser $providerUser, $provider)
    {

        $fileContents = file_get_contents($providerUser->getAvatar());
        $account = SocialFacebookAccount::whereProvider($provider)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if (!empty($account)) {
            return $account->user;
        } else {
            $account = new SocialFacebookAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $provider
            ]);
            $user = User::whereEmail($providerUser->getEmail())->first();
            if($provider == 'facebook')
            {
                $firstName = $providerUser->user['first_name'];
                $lastName = $providerUser->user['last_name'];
            }else{
                $nameArray = explode(' ',$providerUser->getName());
                $firstName = $nameArray[0];
                $lastName = $nameArray[1];
            }
            if (empty($user)) {
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'status' => 1,
                    'user_type' => 1
                ]);
                $user->assignRole('reviewer');
//                $user->sendEmailVerificationNotification();
            }
            $account->user()->associate($user);
            $account->save();
            return $user;
        }
    }
}
