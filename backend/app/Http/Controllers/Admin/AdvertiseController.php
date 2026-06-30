<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Advertise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvertiseController extends BaseController
{
    public function getAdvertisesDatatable(Request $request)
    {
        $status = $request->status;
        $advertises = Advertise::with(['company' => function ($qry) {
            $qry->select('id', 'company_name');
        }])->where('status', $status);

        return datatables($advertises)->toJson();
    }

    public function changeAdvertiseStatus(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, ['id' => 'required|exists:advertise,id', 'status' => 'required']);
        if ($validator->fails()) {
            return $this->formatFailureResponse('Errors with inputs', 400);
        } else {
            $userUpdated = Advertise::find($data['id'])->update(['status' => $data['status']]);
            if ($userUpdated) {
                return $this->formatSuccessResponse('Success', []);
            }
            return $this->formatFailureResponse('Errors while processing', 500);
        }
    }
}
