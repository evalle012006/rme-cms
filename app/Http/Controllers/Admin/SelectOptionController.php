<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\CmsPages;
use App\Models\OptionMaster;
use Illuminate\Http\Request;
use Dotenv\Exception\ValidationException;

class SelectOptionController extends BaseController
{
    public function getSelectOptionMasters($id)
    {
        if ($id) {
            $option_master = OptionMaster::where('select_id', $id)->get()->toArray();
        }
        return $this->formatSuccessResponse('success', $option_master);

    }
}
