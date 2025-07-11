<div class="space-y-4">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-blue-900">Informasi User</h3>
        <p class="text-sm text-blue-700">Email: {{ $user->email }}</p>
        <p class="text-sm text-blue-700">Total Token: {{ $tokens->count() }}</p>
    </div>
    
    <div class="space-y-3">
        @foreach($tokens as $token)
            <div class="border rounded-lg p-4 {{ $token->last_used_at ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $token->name ?? 'Token #' . $token->id }}</h4>
                        <p class="text-xs text-gray-500">ID: {{ $token->id }}</p>
                    </div>
                    <div class="text-right">
                        @if($token->last_used_at)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Belum Digunakan
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Dibuat:</span>
                        <p class="text-gray-600">{{ $token->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Terakhir Digunakan:</span>
                        <p class="text-gray-600">
                            {{ $token->last_used_at ? $token->last_used_at->format('d M Y H:i') : 'Belum pernah' }}
                        </p>
                    </div>
                </div>
                
                @if($token->abilities && count($token->abilities) > 0)
                    <div class="mt-3">
                        <span class="font-medium text-gray-700 text-sm">Abilities:</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($token->abilities as $ability)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $ability }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>