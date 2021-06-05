<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\Book;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBuyers()
    {
        $orders = DB::table('orders')
                                    ->where('orders.seller_id',Auth::id() )
                                    ->where('orders.status','requested')
                                    ->join('users', 'orders.buyer_id', '=', 'users.id')
                                    ->join('books', 'orders.book_id', '=', 'books.id')
                                    ->select('orders.id', 'orders.quantity','books.id as book_id','books.bookName', 'books.negotiable', 'users.id as buyer_id', 'users.firstname  as buyerFirstname','users.lastname as buyerLastname', 'books.price','books.author','users.contact','books.image')
                                    ->get();
        return response([ 'data' => $orders], 200);

    }

    public function getSellerResponse()
    {
        $orders = DB::table('orders')
                                    ->where('orders.buyer_id',Auth::id() )
                                    ->join('users', 'orders.seller_id', '=', 'users.id')
                                    ->join('books', 'orders.book_id', '=', 'books.id')
                                    ->select('orders.id', 'orders.status', 'orders.quantity','books.id as book_id','books.bookName', 'books.negotiable', 'users.id as seller_id', 'users.firstname  as buyerFirstname','users.lastname as buyerLastname', 'books.price','books.author','users.contact','books.image')
                                    ->get();
        return response([ 'data' => $orders], 200);

    }

    public function getAddedBooks()
    {
        $books = Book::where('books.seller_id',Auth::id() )->orderBy("created_at","desc")
                                    ->get();
        return response([ 'data' => $books], 200);

    }
    public function updateBookImage(Request $request,$id)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'image' => 'required|file|image|mimes:jpeg,png,gif,webp|max:9048'
        ]);
        if($validator->fails()){
            return response(['error' => $validator->errors()],422);
        }
        $book = Book::find($id);
        $image = $book['image'];
        $filename = $request->file('image')->getClientOriginalName();
        $filename = date('Ymd_') .time(). $filename;
        $photo = $request->file('image')->storeAs('public',$filename); 
       
        $book->update(["image"=> $filename]);
        Storage::delete('public/'.$image);

        return response([ 'message' => "Image Uploaded"], 200);

    }


   

  
    public function storeOrder(Request $request)
    {
        
        $data =  $request->all();
            
            $validator = Validator::make($data, [
                'seller_id' => 'required',
                'book_id' => 'required',
                'quantity' => 'required|min:1',
            ]);
            
            
            if($validator->fails()){
                return response(['error' => $validator->errors()]);
            }
            $data['buyer_id'] = Auth::id();
            $data['status'] = 'requested';
            $order = Order::create($data);
        //     $notification = ['order_id' => $order['id'],
        //                      'buyer' => false,
        //                      'seller' => false
        // ];
        //     Notification::create($notification);
            
            return response([ 'message' => 'Order Placed'], 200);
        
    }

    
    public function updateBuyer(Request $request, $id)
    {
        
        $data =  $request->all(); 
        $validator = Validator::make($data, [
            'quantity' => 'required|min:1',
        ]);

        if($validator->fails()){
            return response(['error' => $validator->errors()]);
        }
        $order = Order::find($id);
        if ($order == null)
        {
            return response([ 'message' => 'Sorry, There is no such order'],422 );
        }
        $order->quantity = $data['quantity'];
        $order->save();
        
        // $notification = Notification::
        //       where('order_id', $order['id'])
        //       ->update(['buyer' => false,'seller'=>false]);
        // ;

        return response([ 'message' => 'Updated successfully'], 204);
    
    }

    public function updateSeller(Request $request, $id)
    {
        $data =  $request->all(); 
        
        $validator = Validator::make($data, [
            'status' => 'required|in:accepted,rejected'
        ]);

        if($validator->fails()){
            return response(['error' => $validator->errors()]);
        }
        $order = Order::find($id);
        if ($order == null)
        {
            return response([ 'error' => 'Sorry, There is no such order'],422 );
        }
        $order->status = $data['status'];
        $order->save();

       
        // $notification = Notification::
        //       where('order_id', $order['id'])
        //       ->update(['seller'=>true]);
        // ;
       
        
        return response([ 'message' => 'Response recorded'], 200);
    
    }
    public function cancelOrder($id)
    {
        $order = Order::find($id);
        
        if ($order == null)
        {
            return response([ 'message' => 'Sorry, There is no such order'],422 );
        }
        if ($order['buyer_id'] == Auth::id() && $order['status'] != "sold")
        {
            
            $order->update(["status" => 'cancelled']);

            return response([ 'message' => 'Cancelled successfully'], 200);
        }
        return response([ 'error' => 'Sorry, The book is already purchased you'],422 );
        
    }

    public function  confirmSold(Request $request,$id)
    {
        $status = $request->status;

        $order = Order::find($id);
        
        $book = Book::find($order['book_id']);

        if ($order == null)
        {
            return response([ 'error' => 'Sorry, There is no such order'],422 );
        }

        if ($status === "sold")
        {
            $stock = $book->stock - $order->quantity;
            if ($stock < 0)
            {
                return response([ 'error' => 'You might have not sold this product'],422 );
            }
            $book->stock = $stock;
            $book->save();
            $order->update(['status'=>'sold']);
            return response([ 'message' => 'Order confirmed'], 200);
        }
        else{
            $order->update(['status'=>'rejected']);
            return response([ 'message' => 'Order Rejected'], 200);
        }
        
        
    }

    public function getConfirmList()
    {
          $orders = DB::table('orders')
                                    ->where('orders.seller_id',Auth::id())
                                    ->where('orders.status','accepted')
                                    ->join('users', 'orders.buyer_id', '=', 'users.id')
                                    ->join('books', 'orders.book_id', '=', 'books.id')
                                    ->select('orders.id', 'orders.quantity','books.id as book_id','books.bookName', 'books.negotiable', 'users.id as buyer_id', 'users.firstname  as buyerFirstname','users.lastname as buyerLastname', 'books.price','books.author','users.contact','books.image')
                                    ->get();
        return response([ 'data' => $orders], 200);

    }
}
