<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClassResource extends Resource
{
    /**
     * Transform an Academic_Class into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $date_format = 'M j, Y g:i A';
        $author = $this->author;
        return [
            'name' => $this->name,
            'author_name' => $author ? $author->name() : null,
            'created' => $this->created_at->format($date_format),
            'updated' => $this->updated_at->format($date_format)
        ];
    }
}
