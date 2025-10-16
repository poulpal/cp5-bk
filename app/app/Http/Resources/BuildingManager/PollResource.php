<?php

namespace App\Http\Resources\BuildingManager;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

class PollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => (string)$this->title,
            'description' => (string)$this->description,
            'options' => $this->optionsWithPrecentage(),
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'votes_count' => $this->votes_count,
            'created_at' => $this->created_at,
            'remaining_time' => $this->remainingTime(),
        ];
    }

    private function optionsWithPrecentage()
    {
        $totalVotes = $this->votes_count;
        $options = $this->options;

        $newOptions = [];

        foreach ($options as $key => $option) {
            $option_votes_count = $this->votes->where('option', $key)->count();
            if ($totalVotes == 0) {
                $newOptions[$key] = $option;
                continue;
            }
            // $option = $option . ' (' . round(($option_votes_count / $totalVotes) * 100, 2) . '%)';
            $newOptions[$key] = $option . ' (' . $option_votes_count . ')' . "\n";
        }

        return $newOptions;
    }

    private function remainingTime()
    {
        $now = Carbon::now();
        $ends_at = Carbon::parse($this->ends_at);
        $starts_at = Carbon::parse($this->starts_at);

        if ($now->lt($starts_at)) {
            return 'pending';
        }

        if ($now->gt($ends_at)) {
            return 'ended';
        }

        return Jalalian::fromCarbon($ends_at)->ago() . ' تا پایان';
    }
}
