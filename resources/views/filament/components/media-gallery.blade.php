<div class="grid grid-cols-2 md:grid-cols-4 gap-4">

    @forelse ($media as $file)
        <div class="border rounded-xl p-2 shadow-sm bg-white">

            {{-- Preview --}}
            @if(str_starts_with($file->mime_type, 'image'))
                <img src="{{ $file->getFullUrl() }}" 
                     class="rounded-lg w-full h-32 object-cover" />
            @else
                <div class="h-32 flex items-center justify-center bg-gray-100 rounded-lg text-2xl">
                    📄
                </div>
            @endif

            {{-- Nombre --}}
            <p class="text-xs mt-2 truncate">
                {{ $file->name }}
            </p>

            {{-- Acciones --}}
            <div class="flex justify-between mt-2 text-xs">
                <a href="{{ dd($file->getFullUrl()) }}" target="_blank" class="text-blue-600">
                    Ver
                </a>

                <a href="{{ $file->getFullUrl() }}" download class="text-green-600">
                    Descargar
                </a>
            </div>

        </div>
    @empty
        <p>No hay archivos</p>
    @endforelse

</div>