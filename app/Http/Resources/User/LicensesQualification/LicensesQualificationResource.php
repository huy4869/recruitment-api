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

        return [
            'id' => $data->id,
            'name' => $data->name,
            'new_issuance_date' => DateTimeHelper::formatDateHalfJaFe($data->new_issuance_date),
            'new_issuance_date_format' => DateTimeHelper::formatDateHalfJa($data->new_issuance_date),
        ];
    }
}
