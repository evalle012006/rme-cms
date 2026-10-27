<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\PropertyCategory;
use App\Models\Review;
use App\Models\User;
use App\Rules\UniqueCompanyName;
use App\Services\ResendService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use \Illuminate\Support\Facades\Validator;
use PharIo\Manifest\InvalidEmailException;

class CompanyDetailsController extends BaseController
{
    public function addCompany(Request $request)
    {
        $rules = [
            'logo' => 'required',
            'company_name' => ['required', 'string', new UniqueCompanyName()],
            'bio' => 'required',
            'contact_number' => 'required',
            'website' => 'required',
            'status' => 'required',
//            'location' => 'required'
        ];

        try {
            $data = $this->validate($request, $rules);
            $data['company_name'] = trim($data['company_name']);
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileLogo = $this->uploadCompanyLogo($file);
                $data = array_merge($data, $fileLogo);
//                $data['logo'] = $this->saveToS3($file, self::COMPANY_LOGO_STORAGE_PATH);
//                $data['logo_thumb'] = $this->saveThumbToS3($data['logo']);
            }
            $company = CompanyDetails::create($data);
            return $this->formatSuccessResponse('Company Created', $company);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors(), 422);
        }
    }

    public function uploadCompanyLogo($file)
    {
        $data['logo'] = $this->saveToS3($file, self::COMPANY_LOGO_STORAGE_PATH);
        $thumb = $this->saveThumbToS3($data['logo']);
        if ($thumb) {
            $data['logo_thumb'] = $thumb;
        }
        return $data;
    }

    public function updateCompany(Request $request)
    {
        $rules = [
            'logo' => 'required',
            'company_name' => ['required', 'string', new UniqueCompanyName($request->id)],
            'bio' => 'required',
            'contact_number' => 'required',
            'website' => 'required',
            'status' => 'required',
//            'location' => 'required'
        ];

        try {
            $data = $this->validate($request, $rules);
            $data['company_name'] = trim($data['company_name']);

            $companyUserData = [];
            if ($request->company_user) {
                $companyUserData = json_decode($request->company_user, true);

                $rules = [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required|unique:users,email',
                    'password' => 'required',
//                    'display_name' => 'required'
                ];

                $validateUser = Validator::make(json_decode($request->company_user, true), $rules);

                if ($validateUser->fails()) {
                    return response()->json(['errors' => $validateUser->errors()], 500);
                }

                if ($request->hasFile('profile_image')) {
                    $file = $request->file('profile_image');
                    $companyUserData['profile_image'] = $this->saveToS3($file, self::PROFILE_STORAGE_PATH);
                }
            }

            if ($request->has('users')) {
                $users = json_decode($request->users, true);
                foreach ($users as $user) {
                    $updateArray = ['email_verified_at' => $user['status'] ? Carbon::now()->format('Y-m-d H:i:s') : null];
                    User::find($user['id'])->update($updateArray);
                }
            }

            if (!$request->has('logo')) {
                $data['logo'] = null;
            }

            $company_detail = CompanyDetails::find($request->id);

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $data['logo'] = $this->saveToS3($file, self::COMPANY_LOGO_STORAGE_PATH);
                if (!empty($company_detail->logo)) {
                    Storage::delete($company_detail->logo);
                }
            }

            if (count($companyUserData) > 0) {
                $data['status'] = 1;
            }

            $company_detail->update($data);
            if (count($companyUserData) > 0) {
                $companyUserData['company_id'] = $request->id;
                $companyUserData['email_verified_at'] = Carbon::now()->format('Y-m-d H:i:s');
                $passwordString = $companyUserData['password'];
                $companyUserData['password'] = bcrypt($companyUserData['password']);
                $companyUser = User::create($companyUserData);
                $companyUser->assignRole('builder');

                try {
                    $emailData = ['email' => $companyUser->email, 'password' => $passwordString, 'company_name' => $data['company_name']];
                    $this->sendResetPwMail($emailData);
                } catch (\Exception $e) {
                    Log::error('COMPANY_UPDATE_BUILDER_MAIL_ERROR: ' . $e->getMessage());
                }
            }
            return $this->formatSuccessResponse('Company Updated', $data);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors(), 422);
        }
    }

    public function sendResetPwMail($emailData)
    {
        try {
            $email = $emailData['email'];

            $user = User::where('email', $email)->first();

            if (!$user) {
                throw new InvalidEmailException();
            }
            $token = app('auth.password.broker')->createToken($user);

            $link = URL::to('/') . '/password/reset/' . $token . '?email=' . urlencode($email);

            $emailData['link'] = $link;
            app(ResendService::class)->send(
                $email,
                'Account registered successfully',
                'email-template.user-company-assigned',
                ['details' => $emailData]
            );
            return true;
        } catch (\Exception $e) {
            Log::info('RESET_PW_EMAIL_ERROR : ' . $e->getMessage());
            return false;
        }
    }

    public function getDatatableCompany(Request $request)
    {
        // `reviews_count` lets the portal tell the admin up front that a
        // company cannot be deleted until its reviews are moved elsewhere.
        return datatables(
            CompanyDetails::query()
                ->withCount('reviews')
                ->where('status', $request->status)
        )->toJson();
    }

    public function getCompany(Request $request)
    {
        $companies = CompanyDetails::where('id', $request->id)
            ->with(['companyUsers' => function ($qry) {
                $qry->select('id', 'firstname', 'lastname', 'email', 'company_id', 'email_verified_at');
            }])
            ->first();
        return response()->json(['success' => true, 'data' => $companies]);
    }

    public function getCompanySearch($searchItem)
    {
        $companies = CompanyDetails::where('company_name', 'LIKE', '%' . $searchItem . '%')->get()->toArray();
        return response()->json(['success' => true, 'data' => $companies]);
    }

    public function getAllCompany()
    {
        $companies = CompanyDetails::get()->toArray();
        return $this->formatSuccessResponse('success', $companies);
    }

    public function changeCompanyStatus(Request $request)
    {
        $data = $request->all();
        $rules = [
            'id' => 'required|exists:company_details,id',
            'status' => 'required'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->formatFailureResponse('Error in inputs', 400);
        } else {
            try {
                $updated = CompanyDetails::find($data['id'])->update(['status' => $data['status']]);
                if ($updated) {
                    return $this->formatSuccessResponse('success', []);
                }
                return $this->formatFailureResponse('Error while processing', 500);
            } catch (\Exception $e) {
                return $e;
            }
        }
    }

    /**
     * Delete a company and its projects.
     *
     * Refused while any review (of any status) still points at one of the
     * company's projects: reviews are the site's content and must be moved
     * to another company/project explicitly rather than disappearing with
     * the company.
     */
    public function deleteCompany(Request $request)
    {
        $validator = Validator::make(
            ['id' => $request->id],
            ['id' => 'required|exists:company_details,id,deleted_at,NULL']
        );
        if ($validator->fails()) {
            return $this->formatFailureResponse('Company does not exist', 404);
        }

        try {
            $company = CompanyDetails::find($request->id);
            $reviewCount = $company->reviews()->count();

            if ($reviewCount > 0) {
                $noun = $reviewCount === 1 ? 'review' : 'reviews';
                return $this->formatFailureResponse(
                    "Cannot delete \"{$company->company_name}\": it has {$reviewCount} {$noun} attached. "
                    . 'Move the reviews to another company or project first.',
                    409
                );
            }

            DB::transaction(function () use ($company) {
                // Users keep their accounts; they just stop pointing at a
                // company that no longer exists. (Builder portal treats a
                // null company_id as "no company assigned".)
                User::where('company_id', $company->id)->update(['company_id' => null]);

                // No reviews reference this company, so its (review-less)
                // projects can go with it.
                Properties::where('developer_name', $company->id)->delete();
                $company->delete();
            });

            return $this->formatSuccessResponse('record deleted', 'success');
        } catch (\Exception $e) {
            Log::error('COMPANY_DELETE_ERROR: ' . $e->getMessage());
            return $this->formatFailureResponse('Error while processing', 500);
        }
    }

}
