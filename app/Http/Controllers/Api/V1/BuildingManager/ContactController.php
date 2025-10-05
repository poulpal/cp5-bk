<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\ContactResource;
use Illuminate\Http\Request;

class ContactController extends Controller
{

    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
    }

    public function index()
    {
        $contacts = auth()->buildingManager()->building->contacts;

        return response()->json([
            'success' => true,
            'data' => [
                'contacts' => ContactResource::collection($contacts),
            ]
        ], 200);

    }

    public function show($id)
    {
        $contact = auth()->buildingManager()->building->contacts()->where('id', $id)->first();

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => __("مخاطب مورد نظر یافت نشد"),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'contact' => new ContactResource($contact),
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'mobile' => 'required|string',
            'category' => 'required|string',
        ]);

        $contact = auth()->buildingManager()->building->contacts()->create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'category' => $request->category,
        ]);

        return response()->json([
            'success' => true,
            'message' => __("با موفقیت ایجاد شد"),
            'data' => [
                'contact' => new ContactResource($contact),
            ]
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'mobile' => 'required|string',
            'category' => 'required|string',
        ]);

        $contact = auth()->buildingManager()->building->contacts()->where('id', $id)->first();

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => __("مخاطب مورد نظر یافت نشد"),
            ], 404);
        }

        $contact->update([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'category' => $request->category,
        ]);

        return response()->json([
            'success' => true,
            'message' => __("با موفقیت ویرایش شد"),
            'data' => [
                'contact' => new ContactResource($contact),
            ]
        ], 200);
    }

    public function destroy($id)
    {
        $contact = auth()->buildingManager()->building->contacts()->where('id', $id)->first();

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => __("مخاطب مورد نظر یافت نشد"),
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => __("با موفقیت حذف شد"),
        ], 200);
    }


}
