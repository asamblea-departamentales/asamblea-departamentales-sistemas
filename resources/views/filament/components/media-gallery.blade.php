<div class="grid grid-cols-2 md:grid-cols-4 gap-4">

    @forelse ($media as $file)
        <div class="border rounded-xl p-2 shadow-sm bg-white hover:shadow-md transition">

            {{-- Preview --}}
            @if(str_contains($file->mime_type, 'image'))
                <img src="{{ $file->getUrl() }}" 
                     class="rounded-lg w-full h-32 object-cover" />
            @else
                <div class="h-32 flex items-center justify-center bg-gray-100 rounded-lg text-2xl">
                    📄
                </div>
            @endif

            {{-- Nombre --}}
            <p class="text-xs mt-2 truncate font-medium">
                {{ $file->name }}
            </p>

            {{-- Acciones --}}
            <div class="flex justify-between mt-2 text-xs">
                <a href="{{ $file->getUrl() }}" target="_blank" class="text-blue-600 hover:underline">
                    Ver
                </a>

                <a href="{{ $file->getUrl() }}" download class="text-green-600 hover:underline">
                    Descargar
                </a>
            </div>

        </div>
    @empty
        <p class="text-gray-500 text-sm">No hay archivos adjuntos.</p>
    @endforelse

</div>