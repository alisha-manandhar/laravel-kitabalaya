<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use Illuminate\Http\Request;
use  App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;




class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $books = Book::where('stock','>', 0)
        ->where('seller_id','<>',Auth::id())
        ->orderBy('created_at','desc')
        ->get();
        return response([ 'data' => BookResource::collection($books)], 200);

    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data =  $request->all();
        
        if ($data["negotiable"] === "true")
        {
            $data["negotiable"] = 1;
        }
        else
        $data["negotiable"]= 0;

        
        $validator = Validator::make($data, [
            'bookName' => 'required|max:150',
            'publication' => 'required|max:150',
            'author' => 'required|max:150',
            'isbn' => 'required|size:13',
            'edition' => 'required|numeric|min:1',
            'year' => 'required|size:4|after_or_equal:1980|before_or_equal:2021',
            'price' => 'required|numeric|min:20',
            'stock' => 'required|numeric|min:1',
            'negotiable' => 'required|boolean',
            'image' => 'required|file|image|mimes:jpeg,png,gif,webp|max:9048'

        ]);
        
        
        
        if($validator->fails()){
            return response(['error' => $validator->errors()],422);
        }
        $data['seller_id'] = Auth::id();
        
        $filename = $request->file('image')->getClientOriginalName();
        $filename = date('Ymd_') .time(). $filename;
        $photo = $request->file('image')->storeAs('public',$filename); 
        $data['image'] = $filename;
        Book::create($data);
        
        return response([ 'data' => $data, "message" => "Added Book in server"], 201);
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        $bookData = DB::table('books')
                                    ->where('books.id',$book["id"])
                                    ->join('users', 'books.seller_id', '=', 'users.id')
                                    ->select('books.*','users.contact','users.firstname','users.lastname')
                                    ->get();
                                    
        return response([ 'data' => $bookData], 200);

       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        $data =  $request->all();  
        if ($data["negotiable"] == "true")
        {
            $data["negotiable"] = true;
        }
        else
        $data["negotiable"]= false;      
        
        $validator = Validator::make($data, [
            'bookName' => 'required|max:150',
            'publication' => 'required|max:150',
            'author' => 'required|max:150',
            'isbn' => 'required|size:13',
            'edition' => 'required|numeric|min:1',
            'year' => 'required|size:4|after_or_equal:1980|before_or_equal:2021',
            'price' => 'required|numeric|min:20',
            'stock' => 'required|numeric|min:0',
            'negotiable' => 'required|boolean'

        ]);
        
        
        if($validator->fails()){
            return response(['error' => $validator->errors()]);
        }
        if ($book['seller_id'] == Auth::id())
        {
            $book->update($data);
            return response(['message' => "Book Updated succesfully"],200);
        }
        return response([ 'error' => 'Unuthorized user'],422);

        
        
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        if ($book['seller_id'] == Auth::id())
        {
            Storage::delete('public/'.$book['image']);
            $book->delete();
            return response([ 'message' => 'Deleted successfully'],200);
        }
        return response([ 'message' => 'Unuthorized user'],422);
        
    }
}
