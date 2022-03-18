<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TarefaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Tarefa::select('id','title','description','image')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
            'description'=>'required',
            'image'=>'required|image'
        ]);

        try{
            $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('tarefa/image', $request->image,$imageName);
            Tarefa::create($request->post()+['image'=>$imageName]);

            return response()->json([
                'message'=>'Tarefa criada com sucesso!!'
            ]);
        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Acontece algo errado ao criar uma tarefa!!'
            ],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function show(Tarefa $tarefa)
    {
        return response()->json([
            'tarefa'=>$tarefa
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function edit(Tarefa $tarefa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tarefa $tarefa)
    {
        $request->validate([
            'title'=>'required',
            'description'=>'required',
            'image'=>'nullable'
        ]);

        try{

            $tarefa->fill($request->post())->update();

            if($request->hasFile('image')){

                // remove old image
                if($tarefa->image){
                    $exists = Storage::disk('public')->exists("tarefa/image/{$tarefa->image}");
                    if($exists){
                        Storage::disk('public')->delete("tarefa/image/{$tarefa->image}");
                    }
                }

                $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('tarefa/image', $request->image,$imageName);
                $tarefa->image = $imageName;
                $tarefa->save();
            }

            return response()->json([
                'message'=>'Tarefa atualizada com sucesso!!'
            ]);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Acontece algo errado ao atualizar uma tarefa!!'
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tarefa $tarefa)
    {
        try {

            if($tarefa->image){
                $exists = Storage::disk('public')->exists("tarefa/image/{$tarefa->image}");
                if($exists){
                    Storage::disk('public')->delete("tarefa/image/{$tarefa->image}");
                }
            }

            $tarefa->delete();

            return response()->json([
                'message'=>'Tarefa excluída com sucesso!!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Acontece algo errado ao excluir uma tarefa!!'
            ]);
        }
    }
}
