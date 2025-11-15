<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
</div>
{{-- Convert this from tailwind to bootstrap. --}}
<div>
    <div class="p-4 bg-gray-100 border-b border-gray-300 rounded-t-lg">
        <h2 class="text-xl font-semibold text-gray-800">File Explorer: {{ $path }}</h2>
    </div>

    <div class="space-y-2 p-4">
        @forelse ($items as $item)
            <div 
                wire:key="item-{{ $item->id }}" 
                class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer"
            >
                @if ($item->type === 'folder')
                    <svg wire:click="openFolder({{ $item->id }})" 
                        class="w-6 h-6 text-yellow-500 mr-3 flex-shrink-0" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span wire:click="openFolder({{ $item->id }})" 
                        class="font-medium text-blue-600 truncate">
                        {{ $item->title }}
                    </span>
                @else
                    <svg class="w-6 h-6 text-gray-500 mr-3 flex-shrink-0" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0015.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="text-gray-700 truncate">
                        {{ $item->title }}
                    </span>
                @endif
                
                <span class="ml-auto text-sm text-gray-500">
                    {{ $item->created_at->diffForHumans() }}
                </span>
            </div>
        @empty
            <div class="p-6 text-center text-gray-500 bg-white border border-dashed rounded-lg">
                This folder is empty.
            </div>
        @endforelse
    </div>
</div>
