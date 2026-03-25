<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach ($media as $file)
        <div class="border rounded-xl p-2 shadow-sm bg-white">

            {{-- Imagen preview --}}
            @if(str_contains($file->mime_type, 'image'))
                <img src="{{ $file->getUrl() }}" 
                     class="rounded-lg w-full h-32 object-cover" />
            @else
                <div class="h-32 flex items-center justify-center bg-gray-100 rounded-lg">
                    📄
                </div>
            @endif

            {{-- Nombre --}}
            <p class="text-xs mt-2 truncate">
                {{ $file->name }}
            </p>

            {{-- Acciones --}}
            <div class="flex justify-between mt-2 text-xs">
                <a href="{{ $file->getUrl() }}" target="_blank" class="text-blue-600">
                    Ver
                </a>

                <a href="{{ $file->getUrl() }}" download class="text-green-600">
                    Descargar
                </a>
            </div>

        </div>
    @endforeach
</div>