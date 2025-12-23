<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNodeRequest;
use App\Http\Requests\UpdateNodeRequest;
use App\Models\DataNode;
use App\Services\DataNodeService;
use Illuminate\Http\Request;

class DataNodeController extends Controller
{
    // Главная страница (Blade-вью)
    public function index()
    {
        return view('dashboard');
    }

    // Получение корневых узлов (у которых нет родителей)
    public function roots()
    {
        return DataNode::whereNotIn('id', function ($q) {
            $q->select('descendant_id')
                ->from('data_node_closure')
                ->where('depth', '>', 0);
        })->get();
    }

    // Получение прямых детей конкретного узла
    public function children($id)
    {
        return DataNode::join('data_node_closure as c', 'data_nodes.id', '=', 'c.descendant_id')
            ->where('c.ancestor_id', $id)
            ->where('c.depth', 1)
            ->select('data_nodes.*')
            ->get();
    }

    // Создание через сервис
    public function store(StoreNodeRequest $request, DataNodeService $service)
    {
        return $service->create($request->validated());
    }

    // Обновление данных узла
    public function update(UpdateNodeRequest $request, $id)
    {
        $node = DataNode::findOrFail($id);
        $node->update($request->validated());
        return $node;
    }

    // Удаление через сервис
    public function destroy($id, DataNodeService $service)
    {
        $service->delete($id);
        return response()->json(['status' => 'ok']);
    }
}
