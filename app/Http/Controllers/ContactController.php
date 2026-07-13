<?php

namespace App\Http\Controllers;

use App\Exports\ContactsExport;
use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
                collect($validated)->except('tag_ids')->all()
            );

            $contact->tags()->sync(
                $validated['tag_ids'] ?? []
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

    public function export(ExportContactRequest $request)
    {
        return Excel::download(
            new ContactsExport(
                $request->validated()
            ),
            'contacts.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}
