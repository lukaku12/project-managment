<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TaskResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imagePath = '';
        if ($this->image_path) {
            if (str_contains($this->image_path, 'https://via.placeholder.com')) {
                $imagePath = $this->image_path;
            } else {
                $imagePath = Storage::url($this->image_path);
            }
        }

        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "created_at" => (new Carbon($this->created_at))->format("Y-m-d"),
            "due_date" => (new Carbon($this->due_date))->format("Y-m-d"),
            "status" => $this->status,
            "priority" => $this->priority,
            'image_path' => $imagePath,
            "project" => new ProjectResource($this->project),
            "assignedUser" => $this->assignedUser ? new UserResource($this->assignedUser) : null,
            "createdBy" => new UserResource($this->createdBy),
            "updatedBy" => new UserResource($this->updatedBy),
        ];
    }
}
