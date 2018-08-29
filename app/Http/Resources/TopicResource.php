<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TopicResource extends Resource
{
    /**
     * Transform a Topic into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $date_format = 'M j, Y g:i A';
        return [
            'name' => $this->name,
            'author_name' => $this->author->name(),
            'created' => $this->created_at->format($date_format),
            'updated' => $this->updated_at->format($date_format)
        ];
    }
}
