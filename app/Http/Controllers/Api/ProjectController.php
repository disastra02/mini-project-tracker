<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProjectRequest;
use App\Http\Resources\Api\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $project = Project::with('tasks')->orderBy('id', 'DESC')->get();

        return new ProjectResource(true, 'Daftar data project', $project);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {
        $data = $request->validated();
        // Defaut status null karena ditentukan oleh task, dan progress 0 / NULL
        $create = Project::create($data);
        
        return new ProjectResource(true, 'Data project berhasil ditambahkan', $create);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::findOrFail($id);

        return new ProjectResource(true, 'Detail data project', $project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, string $id)
    {
        $data = $request->validated();
        $project = Project::findOrFail($id);
        $project->update($data);

        return new ProjectResource(true, 'Detail project berhasil diubah', $project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $project->tasks()->delete();
        $project->delete();

        return new ProjectResource(true, 'Project berhasil dihapus', null);
    }
}
