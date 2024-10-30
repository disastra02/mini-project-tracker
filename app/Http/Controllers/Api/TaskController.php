<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TaskRequest;
use App\Http\Resources\Api\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private $valueFirstTask = 66.6;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $task = Task::where('projects_id', $request->id)->get();

        return new TaskResource(true, 'Daftar data task', $task);
    }

    /**
     * Task 1 memiliki bobot 2, selanjutnya bobot 1
     * Ketika Task 1 dihapus, maka ketika membuat kembali task akan otomatis memiliki bobot 2
     */
    public function store(TaskRequest $request)
    {
        $data = $request->validated();

        // Mengambil data projek
        $idProject = $data['projects_id'];
        $project = Project::findOrFail($idProject);

        // Menentukan nilai bobot
        $bobotDua = Task::where('projects_id', $idProject)->where('bobot', 2)->first();
        $data['bobot'] = $bobotDua ? 1 : 2;
        
        // Simpan task
        $create = Task::create($data);
        $this->updateDataProject($project, $idProject);
        
        return new TaskResource(true, 'Data task berhasil ditambahkan', $create);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::findOrFail($id);

        return new TaskResource(true, 'Detail data task', $task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, string $id)
    {
        $data = $request->validated();

        // Mengambil data projek
        $idProject = $data['projects_id'];
        $project = Project::findOrFail($idProject);
        
        $task = Task::findOrFail($id);
        $task->update($data);
        $this->updateDataProject($project, $idProject);

        return new TaskResource(true, 'Detail task berhasil diubah', $task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Mengambil data projek
        $task = Task::findOrFail($id);
        $idProject = $task->projects_id;
        $project = Project::findOrFail($idProject);

        $task->delete();
        $this->updateDataProject($project, $idProject);

        return new TaskResource(true, 'Detail task berhasil dihapus', null);
    }

    private function updateDataProject(Project $project, int $idProject): void
    {
        // Menghitung kalkulasi progress
        $status = $this->statusProgress($idProject); 
        $progress = $this->kalkulasiProgress($idProject); 
        
        // Update projek
        $project->status = $status;
        $project->progress = $progress >= 100 ? 100 : $progress;
        $project->save();
    }

    private function kalkulasiProgress(int $idProject)
    {
        $sisaProgress = 100 - $this->valueFirstTask;
        $totalProgressDone = 0;

        $totalTask = Task::where('projects_id', $idProject)
            ->where('bobot', 1)
            ->count();
        
        $bobotDua = Task::where('projects_id', $idProject)
            ->where('bobot', 2)
            ->first();

        if ($bobotDua) {
            $nilaiProgressBobotDua = ($bobotDua->status == "Done")
                ? ($totalTask == 0 ? 100 : $this->valueFirstTask) 
                : 0;

            $totalProgressDone += $nilaiProgressBobotDua;
        }

        $totalTaskDone = Task::where('projects_id', $idProject)
            ->where('bobot', 1)
            ->where('status', 'Done')
            ->count();
        
        if ($totalTask) {
            $totalProgress = $sisaProgress / $totalTask;
            $totalProgressDone += $totalTaskDone * $totalProgress;
        }

        return $totalProgressDone;
    }

    private function statusProgress(int $idProject)
    {
        // Total task ditambah 1 ketika pembuatan baru
        $totalTask = Task::where('projects_id', $idProject)
            ->count();

        // Jika semua task berstatus done
        $totalTaskDone = Task::where('projects_id', $idProject)
            ->where('status', 'Done')
            ->count();

        if ($totalTask == $totalTaskDone) {
            return 'Done';
        }

        $totalTaskInProgress = Task::where('projects_id', $idProject)
            ->where('status', 'In Progress')
            ->count();
        
        if ($totalTaskInProgress) {
            return 'In Progress';
        }
        
        $totalTaskDraft = Task::where('projects_id', $idProject)
            ->where('status', 'Draft')
            ->count();

        if ($totalTask == $totalTaskDraft) {
            return 'Draft';
        }

        // Dalam soal tidak ada kondisi jika task salah satu berstatus draft
        // Maka disini saya set default NULL
        return NULL;
    }
}
