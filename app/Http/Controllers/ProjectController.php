<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Response;
use Inertia\ResponseFactory;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response|ResponseFactory
    {
        $query = Project::query();

        $sortField = request('sort_field', 'created_at');
        $sortDirection = request('sort_direction', 'desc');

        if (request("name")) {
            $query->where("name", "like", "%", request("name") . "%");
        }
        if (request("status")) {
            $query->where("status", request("status"));
        }

        $projects = $query->orderBy($sortField, $sortDirection)->paginate(10)->onEachSide(1);

        return inertia("Project/Index", [
            "projects" => ProjectResource::collection($projects),
            "queryParams" => request()->query() ?: null,
            "success" => session('success'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response|ResponseFactory
    {
        return inertia("Project/Create",);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        /** @var $image UploadedFile */
        $image = $data['image'] ?? null;

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if ($image){
            $data['image_path'] = $image->store('project/' . Str::random(), 'public');
        }

        Project::create($data);

        return to_route('project.index')->with('success', 'Project was created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response|ResponseFactory
    {
        $query = $project->tasks();

        $sortField = request('sort_field', 'created_at');
        $sortDirection = request('sort_direction', 'desc');

        if (request("name")) {
            $query->where("name", "like", "%", request("name") . "%");
        }
        if (request("status")) {
            $query->where("status", request("status"));
        }

        $tasks = $query->orderBy($sortField, $sortDirection)->paginate(10)->onEachSide(1);

        return inertia('Project/Show', [
            'project'     => new ProjectResource($project),
            'tasks'       => TaskResource::collection($tasks),
            'queryParams' => request()->query() ?: null,

        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): Response|ResponseFactory
    {
        return inertia('Project/Edit', [
           'project' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        /** @var $image UploadedFile */
        $image = $data['image'] ?? null;

        if ($image){
            if ($project->image_path) {
                Storage::disk('public')->deleteDirectory(dirname($project->image_path));
            }
            $data['image_path'] = $image->store('project/' . Str::random(), 'public');
        }

        $project->update($data);

        return to_route('project.index')->with('success', "Project $project->name was updated!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $name = $project->name;

        if ($project->image_path) {
            Storage::disk('public')->deleteDirectory(dirname($project->image_path));
        }

        $project->delete();

        return to_route('project.index')->with('success', "Project $name was deleted!");
    }
}
