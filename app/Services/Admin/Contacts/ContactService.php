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

        return Contact::query()
            ->whereRelation('user', 'role_id', '=', $roleId)
            ->with('store')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($id)
    {
        $contact = Contact::query()->where('id', '=', $id)->first();

        if ($contact) {
            if ($contact->be_read == Contact::NOT_READ) {
                $contact->update(['be_read' => Contact::BE_READ]);
            }

            return $contact;
        }

        throw new InputException(trans('response.not_found'));
    }
}
