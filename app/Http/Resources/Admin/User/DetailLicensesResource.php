<?php

namespace App\Http\Resources\Admin\User;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailLicensesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
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
