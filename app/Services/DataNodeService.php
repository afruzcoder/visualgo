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
        DB::transaction(function () use ($id) {
            // 1. Находим ID всех потомков (включая самого себя) через Closure Table
            $descendantsIds = DB::table('data_node_closure')
                ->where('ancestor_id', $id)
                ->pluck('descendant_id');

            // 2. Удаляем связи в Closure Table для всех найденных узлов
            DB::table('data_node_closure')
                ->whereIn('descendant_id', $descendantsIds)
                ->delete();

            // 3. Удаляем сами узлы из основной таблицы
            // (SQL автоматически удалит записи из-за ON DELETE CASCADE, если настроено,
            // но лучше сделать это явно для надежности)
            \App\Models\DataNode::whereIn('id', $descendantsIds)->delete();
        });
    }
}
