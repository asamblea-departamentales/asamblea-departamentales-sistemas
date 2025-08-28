<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="rounded-2xl bg-white shadow p-6 border border-gray-200 hover:shadow-lg transition">
                <div class="flex items-center gap-4 mb-4">
                    <x-dynamic-component :component="$role['icon']" class="w-8 h-8 text-primary-600" />
                    <h2 class="text-xl font-semibold text-gray-800">{{ $role['name'] }}</h2>
                </div>
                <p class="text-sm text-gray-600">{{ $role['description'] }}</p>
            </div>
        @endforeach
    </div>
</x-filament::page>
