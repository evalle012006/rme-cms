<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Mail\ChangeRequestUpdate;
use App\Models\CompanyDetails;
use App\Models\ContactUs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use phpseclib\Crypt\Hash;

class UserController extends BaseController
{

    public function __construct()
    {
        $this->middleware(['auth:api']);
    }


    public function getDatatableUsers(Request $request)
    {
        $user = User::where('id', '!=', auth()->user()->id)
            ->whereHas('roles', function ($qry) {
                $qry->where('name', 'reviewer');
            });
        return datatables($user)->toJson();
    }

    public function getUserById(Request $request)
    {
        $data = User::with(['company:id,company_name', 'roles:id,name'])
            ->where('id', $request->id)->first();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function reviewerSignup(Request $request)
    {
        $data = $request->all();
        $rules = [
            'email'         => 'required|email|unique:users,email',
            'profile_image' => 'nullable',
            'firstname'     => 'required',
            'lastname'      => 'required',
            'display_name'  => 'nullable|string|max:255', // FIX RME-105: added display_name
            'mobile'        => 'required|unique:users,mobile',
            'location'      => 'required',
            'status'        => 'required',
        ];
        try {
            $data = $this->validate($request, $rules);

            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $data['profile_image'] = $this->saveToS3($file, self::PROFILE_STORAGE_PATH);
//                $data['profile_thumb_image'] = $this->saveThumbToS3($data['profile_image']);]
            }
            $data['password'] = bcrypt($request->password);
            $user = User::create($data);
            $user->assignRole('reviewer');

            // FIX RME-106: wrapped in try/catch so mail failure doesn't 500 the response
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                Log::error('REVIEWER_SIGNUP_VERIFICATION_EMAIL_ERROR: ' . $e->getMessage());
            }

            return $this->formatSuccessResponse('User Created', $user);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }

    }

    public function reviewerUpdate(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:users,email,'.$request->id,
            'profile_image' => 'nullable',
            'firstname' => 'required',
            'lastname' => 'required',
            'display_name' => 'required',
//            'mobile' => 'required|unique:users,mobile,' . $request->id,
            'mobile' => 'nullable',
//            'location' => 'required',
            'location' => 'nullable',
            'status' => 'required|in:1,2,3,4', // 1=Approved,2=Pending,3=Rejected,4=Suspended
        ];

        try {
            $data = $this->validate($request, $rules);
            if (!$request->has('profile_image')) {
                $data['profile_image'] = null;
            }

            $user = User::find($request->id);

            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $data['profile_image'] = $this->saveToS3($file, self::PROFILE_STORAGE_PATH);
//                $data['profile_thumb_image'] = $this->saveThumbToS3($data['profile_image']);
                if (!empty($user->profile_image)) {
                    Storage::delete($user->profile_image);
                }
            }

            $sendMail = false;
            if ($user->has_change_request) {
                $data['has_change_request'] = 0;
                $data['requested_display_name'] = null;
                $sendMail = true;
            }

            $user->update($data);

            if ($sendMail) {
                // FIX RME-106: wrapped in try/catch
                try {
                    Mail::to($user->email)->send(new ChangeRequestUpdate('approved'));
                } catch (\Exception $e) {
                    Log::error('REVIEWER_UPDATE_MAIL_ERROR: ' . $e->getMessage());
                }
            }

            return $this->formatSuccessResponse('User Updated', $data);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function builderSignup(Request $request)
    {
        $rules = [
            'firstname'  => 'required',
            'lastname'   => 'nullable|string|max:255',
            'email'      => 'unique:users,email|required',
            'password'   => 'required',
            'status'     => 'required',
            'brand_type' => 'required',
            'company_id' => 'required',
        ];

        try {
            $data = $this->validate($request, $rules);
            $userInsert = [];
            $userInsert['firstname']         = $data['firstname'];
            $userInsert['lastname']          = $data['lastname'] ?? null;
            $userInsert['email']             = $data['email'];
            $userInsert['status']            = $data['status'];
            $userInsert['brand_type']        = $data['brand_type'];
            $userInsert['company_id']        = $data['company_id'];
            $userInsert['password']          = bcrypt($request->password);
            // Mirror email_verified_at from status so the builder list
            // (which filters on email_verified_at, not status) reflects
            // the correct approval state immediately on creation.
            $userInsert['email_verified_at'] = $data['status'] === '1'
                ? \Carbon\Carbon::now()->format('Y-m-d H:i:s')
                : null;
            $user = User::create($userInsert);
            $user->assignRole('builder');

            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                Log::error('BUILDER_SIGNUP_VERIFICATION_EMAIL_ERROR: ' . $e->getMessage());
            }

            return $this->formatSuccessResponse('User Created', $user);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function builderUpdate(Request $request)
    {
        $data = $request->all();

        $rules = [
            'email'      => 'required|email|unique:users,email,'.$request->id,
            'firstname'  => 'required',
            'lastname'   => 'nullable|string|max:255', // FIX RME-107: added lastname
            'status'     => 'required',
            'brand_type' => 'required',
            'company_id' => 'required',
        ];
        try {
            $data = $this->validate($request, $rules);

            $user = User::find($request->id);

            $user = $user->update($data);

            return $this->formatSuccessResponse('User Updated', $user);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('Update Failed', $e->errors());
        }

    }

    public function getDatatableContact(Request $request)
    {
        return datatables(ContactUs::query())->toJson();
    }

    public function getUsers()
    {
        $users = User::get()->toArray();
        return $this->formatSuccessResponse('success', $users);
    }

    public function changeRequestStatus(Request $request)
    {
        $data = $request->all();
        $rules = [
            'id' => 'required|exists:users,id',
            'display_name' => 'required',
            'status' => 'required'
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->formatFailureResponse('Error in inputs', 400);
        } else {
            try {
                $user = User::find($data['id']);
                if ($user->has_change_request) {
                    $updateData = [
                        'has_change_request' => 0,
                        'requested_display_name' => null
                    ];
                    if ($data['status'] == 1) {
                        $updateData['display_name'] = $data['display_name'];
                    }

                    $updated = User::whereId($data['id'])->update($updateData);
                    if ($updated) {
                        // FIX RME-106: wrapped in try/catch
                        try {
                            Mail::to($user->email)->send(new ChangeRequestUpdate($data['status'] == 1 ? 'approved' : 'rejected'));
                        } catch (\Exception $e) {
                            Log::error('CHANGE_REQUEST_MAIL_ERROR: ' . $e->getMessage());
                        }
                        return $this->formatSuccessResponse($data['status'] == 1 ? 'Request approved successfully' : 'Request rejected successfully',
                            'success');
                    }
                }
                return $this->formatFailureResponse('No Change request found', 400);
            } catch (\Exception $e) {
                return $this->formatFailureResponse('Error while processing', 500);
            }
        }
    }

    public function getDatatableBuilders(Request $request)
    {
        $status = $request->status;
        $builders = User::select('id', 'company_id', 'email', 'firstname', 'lastname', 'email_verified_at')
            ->with([
                'company' => function ($qry) {
                    $qry->select('id', 'company_name');
                }
            ])
            ->whereHas('roles', function ($qry) {
                $qry->where('name', 'builder');
            });
        if ($status) {
            $builders = $builders->whereNotNull('email_verified_at');
        } else {
            $builders = $builders->whereNull('email_verified_at');
        }
        return datatables($builders)->toJson();
    }

    public function changeBuilderStatus(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, ['id' => 'required|exists:users,id', 'status' => 'required']);
        if ($validator->fails()) {
            return $this->formatFailureResponse('Errors with inputs', 400);
        } else {
            $user = User::find($data['id']);
            $userUpdated = $user->update(['email_verified_at' => $data['status'] ? Carbon::now()->format('Y-m-d H:i:s') : null]);
            if ($userUpdated) {
                if ($user->company && $user->company->id) {
                    $companyUpdate = $user->company->update(['status' => $data['status'] ? 1 : 0]);
                    if (!$companyUpdate) {
                        return $this->formatFailureResponse('Errors while processing', 500);
                    }
                }
                if ($data['status']) {
                    // FIX RME-106: wrapped in try/catch
                    try {
                        \Mail::to($user->email)->send(new \App\Mail\BuilderApproved());
                    } catch (\Exception $e) {
                        Log::error('BUILDER_STATUS_CHANGE_MAIL_ERROR: ' . $e->getMessage());
                    }
                }
                return $this->formatSuccessResponse('Success', 200);
            }
            return $this->formatFailureResponse('Errors while processing', 500);
        }
    }

    public function getBuilderInfoById(Request $request)
    {
        $validator = Validator::make(['id' => $request->id], ['id' => 'required|exists:users,id']);
        if ($validator->failed()) {
            return $this->formatFailureResponse('Errors with inputs', 400);
        } else {
            $user = User::select('id', 'firstname', 'lastname', 'email', 'email_verified_at', 'company_id')
                ->with([
                    'company' => function ($qry) {
                        $qry->select('id', 'bio', 'company_name', 'logo', 'website');
                    }
                ])->find($request->id);

            // Approval is determined solely by email_verified_at, matching
            // getDatatableBuilders / changeBuilderStatus / updateBuilder.
            // The legacy `status` column (seeder-origin: 1/2/3) is not used
            // for builder approval and is intentionally omitted here.
            $user->is_approved = !is_null($user->email_verified_at);

            return $this->formatSuccessResponse('Success', $user);
        }
    }

    public function updateBuilder(Request $request)
    {
        $data = $request->all();
        $rules = [
            'id' => 'required|exists:users,id',
            'company_id' => 'required|exists:company_details,id',
            'firstname' => 'required',
            'lastname' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->formatFailureResponse('Errors with inputs', 400);
        } else {
            DB::beginTransaction();
            try {
                $userUpdateData = [
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                ];
                if ($request->status == 0) {
                    $userUpdateData['email_verified_at'] = null;
                } else {
                    $userUpdateData['email_verified_at'] = Carbon::now()->format('Y-m-d H:i:s');
                }
                User::where('id', $data['id'])->update($userUpdateData);
                $user = User::find($data['id']);

                $companyUpdateData = [
                    'website' => $data['website'],
                    'company_name' => $data['company_name'],
                    'bio' => $data['company_bio'] ? $data['company_bio'] : null
                ];
                if ($data['status']) {
                    // FIX RME-106: wrapped in try/catch
                    try {
                        \Mail::to($user->email)->send(new \App\Mail\BuilderApproved());
                    } catch (\Exception $e) {
                        Log::error('UPDATE_BUILDER_MAIL_ERROR: ' . $e->getMessage());
                    }
                }
                if ($request->hasFile('company_logo')) {
                    $file = $request->file('company_logo');
                    $fileLogo = app('App\Http\Controllers\Admin\CompanyDetailsController')->uploadCompanyLogo($file);
                    $companyUpdateData = array_merge($companyUpdateData, $fileLogo);
                }
                CompanyDetails::where('id', $data['company_id'])->update($companyUpdateData);
                DB::commit();
                return $this->formatSuccessResponse('Success', []);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->formatFailureResponse('Error while processing', 500);
            }
        }
    }

    public function updateMe(Request $request)
    {
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'profile_image' => 'nullable'
        ];

        $data = $this->validate($request, $rules);

        $user = auth()->user();

        if (!$request->profile_image) {
            $data['profile_image'] = null;
        }

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $data['profile_image'] = $this->saveToS3($file, self::PROFILE_STORAGE_PATH);
        }

        $user->fill($data);
        $user->save();

        return $this->formatSuccessResponse('Profile Updated', $user);
    }
}