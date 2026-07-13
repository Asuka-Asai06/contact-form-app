<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $perPage = $request->input('per_page', 20);

        $contacts = Contact::with([
            'category',
            'tags',
        ])
            ->when(
                $request->filled('keyword'),
                function ($query) use ($request) {
                    $keyword = $request->keyword;

                    $query->where(function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
                }
            )
            ->when(
                $request->filled('gender'),
                fn ($query) => $query->where(
                    'gender',
                    $request->gender
                )
            )
            ->when(
                $request->filled('category_id'),
                fn ($query) => $query->where(
                    'category_id',
                    $request->category_id
                )
            )
            ->when(
                $request->filled('date'),
                fn ($query) => $query->whereDate(
                    'created_at',
                    $request->date
                )
            )
            ->latest()
            ->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $tagIds = $validated['tag_ids'] ?? [];

        $contact = Contact::create(
            collect($validated)
                ->except('tag_ids')
                ->all()
        );

        $contact->tags()->attach($tagIds);

        $contact->load([
            'category',
            'tags',
        ]);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Contact $contact)
    {
        $contact->load([
            'category',
            'tags',
        ]);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validated = $request->validated();

        $tagIds = $validated['tag_ids'] ?? [];

        $contact->update(
            collect($validated)
                ->except('tag_ids')
                ->all()
        );

        $contact->update($validated);

        $contact->tags()->sync($tagIds);

        $contact->load([
            'category',
            'tags',
        ]);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
