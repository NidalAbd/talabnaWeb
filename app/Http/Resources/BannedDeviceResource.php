<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannedDeviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'device_id' => $this->device_id,
            'device_name' => $this->device_name,
            'device_brand' => $this->device_brand,
            'device_model' => $this->device_model,
            'os_version' => $this->os_version,
            'email' => $this->email,
            'phone' => $this->phone,
            'ban_reason' => $this->ban_reason,
            'banned_at' => $this->banned_at,
            'unban_at' => $this->unban_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_active' => $this->isActive(),
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }),
        ];
    }
}
