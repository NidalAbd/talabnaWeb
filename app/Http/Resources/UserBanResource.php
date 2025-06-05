<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phones,
            'is_active' => $this->is_active,
            'is_banned' => $this->isBanned(),
            'banned_devices_count' => $this->whenCounted('bannedDevices'),
            'banned_devices' => BannedDeviceResource::collection(
                $this->whenLoaded('bannedDevices')
            ),
        ];
    }
}
