<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Collection;
use \JsonMachine\Items;
use \JsonMachine\JsonDecoder\ExtJsonDecoder;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'profile' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            try {
                $_initial_company_profile_data = [
                    'email' => $input['email'],
                    'profile' => $input['profile'],
                ];

                if ($input['profile'] == 'chuck') {
                    $_company_profile_url = "https://api.chucknorris.io/jokes/random";
                    $_response = Http::get($_company_profile_url);
                    $_initial_company_profile_data['notes'] = $_response['value'];
                    $user->forceFill($_initial_company_profile_data)->save();
                } else {
                    $user->forceFill($_initial_company_profile_data)->save();
                }

            } catch (\Exception $e) {
                Log::info(print_r($e, true));
            }
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
