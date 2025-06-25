<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
  public function toArray($request)
{
    return [
            'id' => $this->id,
            'full_name' => $this->first_name .' '. $this->middle_name.' '.$this->last_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'phone' => $this->phone,
            'parent_phone' => $this->parent_phone,
            'parent_job' => $this->parent_job,
            'gender' => $this->gender,
            'government' => [
                'id'=>$this->government->id,
                'name'=>$this->government->name

            ],
             'category' => [
                'id'=>$this->category->id,
                'name'=>$this->category->name

            ],

            'email' => $this->email,
            'image' => $this->full_image_path ,

        // Only return 'text' if available; otherwise return 'audio'
    ];
}

}
