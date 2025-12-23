<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAP Enterprise Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-slate-50 h-screen flex flex-col font-sans">

<header class="bg-slate-900 text-white p-4 shadow-md flex justify-between items-center">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center font-bold">H</div>
        <h1 class="text-lg font-semibold tracking-wide">Hierarchical Analytics Platform</h1>
    </div>
    <div id="loader" class="hidden text-sm text-blue-400 animate-pulse">Загрузка данных...</div>
</header>

<div class="flex flex-1 overflow-hidden">
    <main class="flex-1 p-6 relative flex flex-col">
        <div class="mb-4 flex gap-2">
            <button onclick="resetToRoot()" class="px-4 py-2 bg-white border rounded shadow-sm hover:bg-gray-50 text-sm font-medium transition">
                🏠 В корень
            </button>
            <div id="breadcrumb" class="flex items-center text-sm text-gray-500">
            </div>
        </div>

        <div id="chart" class="flex-1 bg-white rounded-2xl shadow-sm border border-slate-200"></div>
    </main>

    <aside class="w-96 bg-white border-l border-slate-200 shadow-xl p-6 overflow-y-auto">
        <div id="detailsCard" class="mb-8 hidden">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Выбранный объект</h2>
            <div class="p-4 bg-slate-900 rounded-xl text-white">
                <p id="detName" class="text-xl font-bold mb-1"></p>
                <span id="detType" class="inline-block px-2 py-1 bg-blue-600 text-[10px] rounded uppercase mb-3"></span>
                <p id="detDesc" class="text-slate-400 text-sm"></p>
            </div>
        </div>

        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Добавить новый узел</h2>
        <form id="nodeForm" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Название</label>
                <input type="text" id="name" required class="w-full border-slate-200 border rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Значение</label>
                    <input type="number" id="value" required class="w-full border-slate-200 border rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Тип</label>
                    <select id="type" class="w-full border-slate-200 border rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="Region">Регион</option>
                        <option value="City">Город</option>
                        <option value="Organization">Организация</option>
                        <option value="Unit">Подразделение</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Описание</label>
                <textarea id="description" rows="3" class="w-full border-slate-200 border rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
            </div>

            <input type="hidden" id="parentId" value="">

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition transform active:scale-[0.98]">
                Создать запись
            </button>
        </form>

        <div class="mt-8 p-4 bg-amber-50 rounded-lg border border-amber-100 text-[11px] text-amber-700 leading-relaxed">
            <p>💡 <strong>Совет:</strong> Кликните на сектор графика, чтобы «провалиться» внутрь или выбрать родителя для новой записи.</p>
        </div>
    </aside>
</div>

<script>
    // Инстанс чарта
    const chart = echarts.init(document.getElementById('chart'));
    let currentParentId = null;

    // Конфиг Axios
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

    // 1. Загрузка данных
    async function loadData(id = null) {
        document.getElementById('loader').classList.remove('hidden');
        try {
            const url = id ? `/api/nodes/${id}/children` : '/api/nodes/roots';
            const response = await axios.get(url);
            render(response.data);
        } catch (e) {
            console.error("Ошибка загрузки:", e);
        } finally {
            document.getElementById('loader').classList.add('hidden');
        }
    }

    // 2. Отрисовка графика
    function render(data) {
        const hasData = data && data.length > 0;

        const option = {
            backgroundColor: 'transparent',
            tooltip: { trigger: 'item' },
            series: [{
                type: 'sunburst',
                data: data.map(n => ({
                    name: n.name,
                    value: n.value,
                    id: n.id,
                    type: n.type,
                    description: n.description
                })),
                radius: ['15%', '85%'],
                itemStyle: { borderRadius: 8, borderWidth: 2 },
                label: { show: true, fontSize: 10 },
                emphasis: { focus: 'ancestor' }
            }]
        };

        if (!hasData) {
            chart.showLoading({ text: 'Нет данных в этой ветке', showSpinner: false });
        } else {
            chart.hideLoading();
            chart.setOption(option, true);
        }
    }

    // 3. Обработка клика (Выбор узла)
    chart.on('click', function(params) {
        const node = params.data;
        if (!node || !node.id) return;

        currentParentId = node.id;
        document.getElementById('parentId').value = node.id;

        // Обновляем панель деталей
        document.getElementById('detailsCard').classList.remove('hidden');
        document.getElementById('detName').innerText = node.name;
        document.getElementById('detType').innerText = node.type || 'Object';
        document.getElementById('detDesc').innerText = node.description || 'Описание не заполнено.';

        // Проваливаемся глубже
        loadData(node.id);
    });

    // 4. Сброс в корень
    function resetToRoot() {
        currentParentId = null;
        document.getElementById('parentId').value = '';
        document.getElementById('detailsCard').classList.add('hidden');
        loadData();
    }

    // 5. Сохранение нового узла
    document.getElementById('nodeForm').onsubmit = async function(e) {
        e.preventDefault();

        const data = {
            name: document.getElementById('name').value,
            value: document.getElementById('value').value,
            type: document.getElementById('type').value,
            description: document.getElementById('description').value,
            parent_id: currentParentId || null
        };

        try {
            await axios.post('/api/nodes', data);
            // Очистка полей
            document.getElementById('name').value = '';
            document.getElementById('value').value = '';
            document.getElementById('description').value = '';
            // Перезагрузка текущего вида
            loadData(currentParentId);
        } catch (err) {
            alert("Ошибка: " + (err.response?.data?.message || "Проверьте данные"));
        }
    };

    // Начальная загрузка
    loadData();

    // Адаптивность графика
    window.addEventListener('resize', chart.resize);
</script>
</body>
</html>
