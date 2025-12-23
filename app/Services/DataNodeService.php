<?php

namespace App\Services;

use App\Models\DataNode;
use App\Models\DataNodeClosure;
use Illuminate\Support\Facades\DB;

class DataNodeService
{
    public function create(array $data): DataNode
    {
        return DB::transaction(function () use ($data) {
            $parentId = $data['parent_id'] ?? null;
            unset($data['parent_id']);

            $node = DataNode::create($data);

            DataNodeClosure::create([
                'ancestor_id' => $node->id,
                'descendant_id' => $node->id,
                'depth' => 0
            ]);

            if ($parentId) {
                $ancestors = DataNodeClosure::where('descendant_id', $parentId)->get();
                foreach ($ancestors as $ancestor) {
                    DataNodeClosure::create([
                        'ancestor_id' => $ancestor->ancestor_id,
                        'descendant_id' => $node->id,
                        'depth' => $ancestor->depth + 1
                    ]);
                }
            }
            return $node;
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(fn () => DataNode::where('id', $id)->delete());
    }
}
