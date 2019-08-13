<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\FileUploadTrait;
use App\Image;
use ImageInt;
use Validator;

class ImagesController extends Controller
{
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $images = Image::all();

       return response()->json($images);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
        $rules = [
            'name' => 'required|min:3',
            'image' => 'required|image|dimensions:min_width=400,min_height=300',
            'width' => 'required',
            'height'=> 'required'
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        if($request->hasfile('image')){
            $fileName= $this->uploadImage($request->image);
            $width= $request->width;
            $height = $request->height;
            
            $image_resize = ImageInt::make($request->image);
            $image_resize->fit($width, $height, function ($constraint) {
            $constraint->upsize();
            });
            $image_resize->save('storage/'.$fileName);
        }
            
            $image = Image::create(  
            array_merge(
                $request->except('_token'),
                 ["image"=>$fileName ?? null]
            )
        );

        return response()->json(['success'=>'You have successfully uploaded an image.']);
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $image = Image::where('id',$id)->first();
        if(is_null($image)){
            return response()->json(["message"=> "This Image not found"], 404);
        }
        return response()->json($image);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $image = Image::findOrFail($id);
        if(is_null($image)){
            return response()->json(["message"=> "This Image not found"], 404);
        }

        return response()->json($image);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Image $image)
    { 
        $rules = [
            // 'image' => 'dimensions:min_width=400,min_height=300'
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        if($request->hasfile('image')){
            $fileName= $this->uploadImage($request->image);
            $width= $request->width;
            $height = $request->height;

            $image_resize = ImageInt::make($request->image);
            $image_resize->fit($width, $height, function ($constraint) {
            $constraint->upsize();
            });
            $image_resize->save('storage/'.$fileName);
        }

        $image->update(
            array_merge(
                $request->except('image','_token','_method'),
                ["image" => $fileName ?? $image->image ?? null]
            )
            );
            return response()->json(["message"=>"Successfully updated"],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $image = Image::findOrFail($id);
        if(is_null($image)){
            return response()->json(["message"=> "This Image not found"], 404);
        }

        $image->delete();

        return response()->json('Successfully Deleted');
    }
}
