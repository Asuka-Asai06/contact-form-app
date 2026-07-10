<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    public function store(StoreContactRequest $request)
    {
        DB::transaction(function () use ($request) {

            $validated = $request->validated();

            $contact = Contact::create(
                collect($validated)->except('tags')->all()
            );

            $contact->tags()->sync(
                $validated['tags'] ?? []
            );
        });

        return redirect()->route('contacts.thanks');
    }

    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $category = Category::find($validated['category_id']);

        return view('contact.confirm', compact('validated', 'category'));
    }

    public function thanks()
    {
        return view('contact.thanks');
    }
}
