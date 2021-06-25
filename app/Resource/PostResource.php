<?php

namespace App\Resource;

use App\Model\Category;
use App\Model\User;
use Hyperf\Resource\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'author' => $this->author,
            'authorMeta' => (User::query()->select('name', 'id', 'avatar', 'credit', 'desc', 'background')->where('id', $this->author)->get()->toArray())[0],
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'type' => $this->type,
            'guid' => $this->guid,
            'comment_count' => (int) $this->comment_count,
            'status' => $this->status,
            'comment_status' => (bool)((int)$this->comment_status),
            'menu' => json_decode($this->menu),
            'menuMeta' => (Category::query()->select('label', 'value')->where('value', json_decode($this->menu))->first()->toArray()),
            'tag' => json_decode($this->tag),
            'download_status' => (bool)((int)$this->download_status),
            'download' => json_decode($this->download),
            'music' => json_decode($this->music),
            'video' => json_decode($this->video),
            'views' => (int) $this->views,
            'header_img' => $this->header_img,
            'updated_at' => str_replace(array('T','Z'),' ',$this->updated_at),
        ];
    }
}
