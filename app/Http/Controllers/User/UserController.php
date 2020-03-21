<?php

namespace App\Http\Controllers\User;

use App\Http\Resources\User as UserResource;
use App\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{
    /**
     * Function retrieves user data by id given in form as parameter.
     * Returns json with data described in App\Http\Resources\User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserData(Request $request)
    {
        $userId = $request->get('id');
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'basic.access-forbidden'
            ]);
        }

        $user = User::findOrFail($userId);
        return response()->json([
            'success' => true,
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Function tries to save current user profile.
     * User can change password and avatar.
     * Function returns information with status of this operation (fail or success)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveProfile(Request $request)
    {
        $user = $request->user();
        $changePassword = false;
        $message = '';
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'basic.user-not-found',
            ]);
        }

        if (
            !empty($request->currentPassword)
            && !empty($request->newPassword)
            && !empty($request->confirmation)
        ) {
            $message = $this->validatePassword($request->currentPassword, $request->newPassword, $request->confirmation, $user);
        }

        if (!empty($message)) {
            return response()->json([
                'success' => false,
                'message' => $message
            ]);
        }

        if ($changePassword) {
            $user->password = bcrypt($request->newPassword);
        }

        $image = $request->get('image');
        if (
            !empty($image)
            && !in_array(substr($image, $start = strpos($image, '/')+1, strpos($image, ';') - $start), ['jpeg', 'jpg', 'png'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'basic.user-profile-wrong-image'
            ]);
        }

        if (!empty($image)) {
            $name = time().'.'.explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
            $path = public_path('images/avatars/'.$user->id);
            $this->makeAvatarsDirectory($path, $user->avatar);
            Image::make($request->get('image'))->save($path.'/'.$name);
            $user->avatar = $name;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'basic.user-profile-saved'
        ], 201);
    }

    /**
     * Function is validating new password and checks if user entered correct current password.
     * Function returns key from locale file when there is an error or empty string if everything is ok.
     *
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmation
     * @param \App\User $user
     * @return string
     */
    private function validatePassword($currentPassword, $newPassword, $confirmation, $user)
    {
        if (strcmp($newPassword, $confirmation) !== 0) {
            return 'basic.passwords-not-equal';
        }

        if (!Hash::check($currentPassword, $user->password)) {
            return 'basic.wrong-password';
        }

        return '';
    }

    /**
     * Function creates folder for user avatar file from given path
     * or deletes current avatar if directory already exists
     *
     * @param string $filepath
     * @param string $oldFile
     */
    private function makeAvatarsDirectory($filepath, $oldFile)
    {
        if (!File::exists($filepath)) {
            File::makeDirectory($filepath, 0755, true, true);
        } else {
            $file = new Filesystem();
            $file->delete($filepath.'/'.$oldFile);
        }
    }
}
