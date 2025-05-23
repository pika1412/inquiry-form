<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\User;

class ContactController extends Controller
{
    public function index(Request $request){
    $categories = Category::all();
    $input = $request->all(); 
    return view('contact.index', compact('categories', 'input'));
}

    public function confirm(ContactRequest $request){
    $contact=$request->all();
    $categories = [
    1 => '商品のお届けについて',
    2 => '商品の交換について',
    3 => '商品トラブル',
    4 => 'ショップへのお問い合わせ',
    5 => 'その他',];
    return view('contact.confirm',compact('contact','categories'));
}

     public function send(Request $request){
     if ($request->input('action') === 'back') {
    return redirect()->route('contact.index')->withInput();
    }
    Contact::create($request->only(['last_name','first_name','gender','email','tel1','tel2',
    'tel3','address',
    'building','category_id','message']));

    return redirect('/contact.thanks');
    }

    public function thanks(){
        return redirect('/contact.index');
    }

    public function admin(Request $request)
{
    $contacts = Contact::with('category')
     ->when($request->filled('search'), function ($query) use ($request) {
        return $query->KeywordSearch($request->search, $request->match_type ?? 'partial');
        })

        ->when($request->filled('gender'), function ($query) use ($request) {
        return $query->where('gender', $request->gender);
        })

        ->when($request->filled('category_id'), function ($query) use ($request) {
        return $query->where('category_id', $request->category_id);
        })
        
        ->when($request->filled('from_date'), function ($query) use ($request) {
        return $query->whereDate('created_at', '>=', $request->from_date);

        ->simplePaginate(7);
        ->withQueryString();

    $categories = Category::all();

    return view('admin.admin', compact('contacts', 'categories'));
}

    public function destroy($id)
{
    Contact::findOrFail($id)->delete();
    return response()->json(['success' => true]);
}


}

