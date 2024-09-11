<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    //
    public function uploadImage(Request $request){
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $uploadPath = public_path('beLaravel/uploads');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);  // Create the directory if it doesn't exist
        }
        
        // Store the image in public_html/uploads folder
        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move($uploadPath, $fileName);

        // Return the file URL to the client
        $fileUrl = asset('beLaravel/uploads/' . $fileName);
        return response()->json(['url' => $fileUrl], 200);
    }
}
