<?php

namespace App\Services\Admin\Contacts;

use App\Exceptions\InputException;
use App\Models\Contact;
use App\Models\User;
use App\Services\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactService extends Service
{
    public const PER_PAGE = 10;

    /**
     * @param $roleId
     * @param $perPage
     * @return array|LengthAwarePaginator
     */
    public function list($roleId, $perPage)
    {
        $roleId = $roleId ?? User::ROLE_USER;
        $perPage = $perPage ?? self::PER_PAGE;

        if ($roleId == User::ROLE_USER) {
            $contacts = Contact::query()->withTrashed()
                ->with('userTrashed')
                ->whereNull('store_id')
                ->orderByDesc('created_at')
                ->paginate($perPage);
        } else {
            $contacts = Contact::query()->withTrashed()
                ->with('storeTrashed')
                ->whereNotNull('store_id')
                ->orderByDesc('created_at')
                ->paginate($perPage);
        }//end if

        return [
            'role_id' => $roleId,
            'data' => $contacts
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws InputException
     */
    public function detail($id)
    {
        $contact = Contact::query()->withTrashed()->with(['userTrashed', 'storeTrashed'])->where('id', '=', $id)->first();
        $roleId = User::ROLE_RECRUITER;

        if (!$contact) {
            return null;
        }

        if ($contact->be_read == Contact::NOT_READ) {
            $contact->update(['be_read' => Contact::BE_READ]);
        }

        if (is_null($contact->store_id)) {
            $roleId = User::ROLE_USER;
        }

        return [
            'role_id' => $roleId,
            'data' =>  $contact
        ];
    }
}
