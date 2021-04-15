<?php

namespace App\Imports;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class ProjectImport implements ToModel
{


    public function model(array $row)
    {
        return new Project([
            'company_name' => $row[0],
            'contact_name'     => $row[1],
            'contact_phone'    => $row[2],
            'created_user_id' => Auth::user()->id,
            'owner_user_id' => 0,
            'merchant_id' => Auth::user()->merchant_id,
        ]);
    }

    public function headingRow(): int
    {
        return 2;
    }


}
