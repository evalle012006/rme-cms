<?php

namespace App\Imports;

use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\PropertyCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class PropertyImport implements ToCollection, WithHeadingRow
{

    public function collection(Collection $rows)
    {
        $propertyCategories = PropertyCategory::all();

        foreach ($rows as $row) {

            $validator = Validator::make($row->toArray(), [
                'company' => 'required',
                'project' => 'required',
                'address' => 'required',
                'state' => 'required',
                'postcode' => 'required',
                'website' => 'nullable',
                'suburb' => 'required',
            ]);

            if ($validator->fails()) {
                continue;
            }

            $company = CompanyDetails::where('company_name', $row['company'])->first();

            if ($company) {
                $property = Properties::create([
                    'developer_name' => $company->id,
                    'project_name' => $row['project'],
                    'address' => $row['address'],
                    'city' => $row['suburb'],
                    'state' => $row['state'],
                    'post_code' => $row['postcode'],
                    'website_url' => $row['website'] ? $row['website'] : '#',
                    'created_by' => auth()->id(),
                ]);

                if (!empty($property)) {
                    $property->propertyAllowedCategory()->sync($propertyCategories->pluck('id')->toArray());
                }
            }
        }
    }
}
