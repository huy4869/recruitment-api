<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactRequest;
use App\Http\Resources\Admin\Contacts\ContactResource;
use App\Http\Resources\Admin\Contacts\ContactsCollection;
use App\Http\Resources\Admin\Contacts\ContactStoreResource;
use App\Http\Resources\Admin\Contacts\DetailContactResource;
use App\Models\User;
use App\Services\Admin\Contacts\ContactService;
use Illuminate\Http\JsonResponse;

class ContactsController extends Controller
{
    /**
     * List contact
     *
     * @param ContactRequest $request
     * @return JsonResponse
     */
    public function list(ContactRequest $request)
    {
        $roleId = $request->role_id;
        $perPage = $request->per_page;
        $data = ContactService::getInstance()->list($roleId, $perPage);

        return $this->sendSuccessResponse(new ContactsCollection($data));
    }

    /**
     * Detail contact
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $data = ContactService::getInstance()->detail($id);

        if (!$data) {
            ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
        }

        if ($data['role_id'] == User::ROLE_USER) {
            $resource = new ContactResource($data['data']);
        } else {
            $resource = new ContactStoreResource($data['data']);
        }

        return $this->sendSuccessResponse($resource);
    }
}
