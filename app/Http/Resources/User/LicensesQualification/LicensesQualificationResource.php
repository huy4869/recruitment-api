<?php

namespace App\Http\Resources\User\LicensesQualification;

use App\Helpers\DateTimeHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicensesQualificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;
        $year = substr($data->new_issuance_date, 0, 4);
        $month = substr($data->new_issuance_date, 4);
        $newIssuanceDate = '';

        if ($data->new_issuance_date) {
            $newIssuanceDate = $year . '/' . $month;
        }

        return [
            'id' => $data->id,
            'name' => $data->name,
            'year' => $year,
            'month' => $month,
            'new_issuance_date' => $newIssuanceDate,
            'new_issuance_date_format' => DateTimeHelper::formatNameDateHalfJa($year, $month),
        ];
    }
}
