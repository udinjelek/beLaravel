<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class ImageUploadController extends Controller
{
    //
    public function uploadImage(Request $request){
        if($request->hasFile('image')){
            $file = $request->file('image');
            $filename =  $file->getClientOriginalName();
            $path = $file->storeAs('public/images', time() . "_" . $filename);

            if ($path) {
                $pathfile = Storage::url($path);
                $completeUrl = env('APP_URL') . $pathfile;
                

                $sqlInsert = "INSERT INTO 
                    file_collection 
                    (namefile, filetype, pathfile, downloadcount, created_at, created_by, is_deleted)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

                $dataInsert =   [   $filename, 
                                    'unknown', 
                                    $pathfile,
                                    0, // download count
                                    now(),
                                    1, // created_by
                                    false
                                ];
                $query = DB::insert($sqlInsert, $dataInsert);
                if (!$query){
                    return response()->json([
                            'result' => 'insert failed'
                            ],400);
                }

                return response()->json([
                            'filename' => $filename ,
                            'path' => $pathfile,
                            'url' => $completeUrl,
                            ],200);
                
            } else {
                return response()->json([
                        'error' => 'File was not saved.'
                        ], 500);
            }

            
        }

        return response()->json([
                'error' => 'No file uploaded'
                ], 400);
    }

    public function loadListImage(){
        $env_url = env('APP_URL');
        $sqlCode = <<<SQL
                        select 
                        namefile filename,
                        pathfile, 
                        concat('$env_url' , pathfile) url
                        from file_collection
                        where is_deleted = false;
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_queryListAllImage = ($query); 
        // return ['data' => $data_json_queryListAllImage];
        return response()->json([
                'data' => $data_json_queryListAllImage
                ],200);
    }

    public function deleteImage(Request $request){
        $pathfile = $request->input('pathfile');
        
        if (!$pathfile) {
            return response()->json([
                'message' => 'Image Pathfile is required'
                ], 400);
        }

        // Assuming the images are stored in 'public/images' directory
        $completeUrl = str_replace("/storage/images/","public/images/",$pathfile) ;
        
        if (Storage::exists($completeUrl)) {
            $updateSql =    <<<SQL
                                update file_collection
                                set is_deleted = true
                                where pathfile = ?;
                            SQL;
            
            $affectedRows = DB::update($updateSql, [$pathfile]);
            
            return response()->json([
                    'message' => 'Image deleted successfully' , 
                    'status' => 'success'
                    ], 200);
        } else {
            return response()->json([
                    'message' => 'Image not found' , 
                    'status' => 'failed'
                    ], 404);
        }
    }
}
