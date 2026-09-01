<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'avatar' => MediaUrl::make($this->avatar),
            'avatar_url' => MediaUrl::make($this->avatar),
            'status' => $this->status,
            'type' => $this->type ?: User::TYPE_USER,
            'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()->values()->all()),
            'permissions' => $this->whenLoaded('roles', fn () => $this->getAllPermissions()->pluck('name')->values()->all()),
            'is_content_creator' => $this->isContentCreator(),
            'creator' => $this->when(
                $this->relationLoaded('creator') && $this->creator,
                fn () => [
                    'uuid' => $this->creator->uuid,
                    'username' => $this->creator->username,
                    'bio' => $this->creator->getTranslations('bio'),
                    'avatar_url' => $this->creator->avatar_url,
                    'followers_count' => $this->creator->followers_count,
                    'socials' => $this->creator->relationLoaded('socials')
                        ? $this->creator->socials->map(fn ($s) => [
                            'platform' => $s->platform,
                            'url' => $s->url,
                        ])->values()
                        : [],
                ]
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
