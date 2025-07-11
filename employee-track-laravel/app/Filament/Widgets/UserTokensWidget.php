<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\PersonalAccessToken;

class UserTokensWidget extends BaseWidget
{
    protected static ?string $heading = '👥 Manajemen User & Token';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 5;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->with(['tokens' => function ($query) {
                        $query->latest()->limit(1);
                    }])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('🆔 ID')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('👤 Nama User')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('📧 Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('tokens_count')
                    ->label('🔑 Jumlah Token')
                    ->counts('tokens')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state <= 2 => 'success',
                        $state <= 5 => 'warning',
                        default => 'danger',
                    }),
                    
                Tables\Columns\TextColumn::make('latest_token')
                    ->label('🎫 Bearer Token')
                    ->getStateUsing(function (User $record) {
                        // Jika ini adalah user yang sedang login, ambil dari session
                        if (auth()->id() === $record->id) {
                            $sessionToken = session('sanctum_token');
                            return $sessionToken ? 'Bearer ' . $sessionToken : '❌ Tidak ada token di session';
                        }
                        
                        // Untuk user lain, ambil dari database
                        $latestToken = $record->tokens()->latest()->first();
                        return $latestToken ? 'Bearer ' . $latestToken->token : '❌ Tidak ada token';
                    })
                    ->copyable()
                    ->copyableState(function (User $record) {
                        // Jika ini adalah user yang sedang login, ambil dari session
                        if (auth()->id() === $record->id) {
                            $sessionToken = session('sanctum_token');
                            return $sessionToken ? 'Bearer ' . $sessionToken : null;
                        }
                        
                        // Untuk user lain, ambil dari database
                        $latestToken = $record->tokens()->latest()->first();
                        return $latestToken ? 'Bearer ' . $latestToken->token : null;
                    })
                    ->fontFamily('mono')
                    ->size('xs')
                    ->wrap()
                    ->color('gray')
                    ->tooltip('Klik untuk menyalin Bearer token lengkap'),
                    
                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('⏰ Terakhir Digunakan')
                    ->getStateUsing(function (User $record) {
                        $latestToken = $record->tokens()->latest()->first();
                        return $latestToken?->last_used_at?->diffForHumans() ?? '❓ Belum pernah';
                    })
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('📅 Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view_all_tokens')
                    ->label('👀 Lihat Semua Token')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->modalHeading(fn (User $record) => "🔑 Token untuk {$record->name}")
                    ->modalContent(function (User $record) {
                        $tokens = $record->tokens()->latest()->get();
                        
                        if ($tokens->isEmpty()) {
                            return view('filament.widgets.no-tokens');
                        }
                        
                        return view('filament.widgets.user-tokens-modal', [
                            'tokens' => $tokens,
                            'user' => $record
                        ]);
                    })
                    ->modalWidth('4xl'),
                    
                Tables\Actions\Action::make('revoke_all_tokens')
                    ->label('🗑️ Cabut Semua Token')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('⚠️ Cabut Semua Token')
                    ->modalDescription(fn (User $record) => "Apakah Anda yakin ingin mencabut semua token untuk {$record->name}? Tindakan ini tidak dapat dibatalkan.")
                    ->action(function (User $record) {
                        $record->tokens()->delete();
                        
                        $this->notify('success', '✅ Semua token berhasil dicabut');
                    })
                    ->visible(fn (User $record) => $record->tokens()->exists()),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
    
    protected function notify(string $type, string $message): void
    {
        \Filament\Notifications\Notification::make()
            ->title($message)
            ->$type()
            ->send();
    }
}