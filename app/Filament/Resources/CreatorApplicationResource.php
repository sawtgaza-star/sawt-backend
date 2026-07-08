<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreatorApplicationResource\Pages;
use App\Filament\Resources\CreatorApplicationResource\RelationManagers;
use App\Models\Creator;
use App\Models\CreatorApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CreatorApplicationResource extends Resource
{
    protected static ?string $model = CreatorApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Creators');
    }

    public static function getNavigationLabel(): string
    {
        return __('Creator Applications');
    }

    public static function getModelLabel(): string
    {
        return __('Creator Application');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Creator Applications');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'reference_number', 'uuid'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            __('Email') => $record->email,
            __('Status') => $record->status,
        ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات مقدّم الطلب')->schema([
                Forms\Components\TextInput::make('reference_number')->label('رقم المرجع')->disabled(),
                Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                Forms\Components\TextInput::make('email')->label('البريد الإلكتروني')->email()->required(),
                Forms\Components\TextInput::make('phone')->label('الهاتف'),
                Forms\Components\TextInput::make('content_type')->label('نوع المحتوى'),
                Forms\Components\TextInput::make('followers_count')->label('عدد المتابعين')->numeric(),
                Forms\Components\Textarea::make('bio')->label('نبذة تعريفية')->rows(3)->columnSpanFull(),
                Forms\Components\Textarea::make('extra_notes')->label('ملاحظات إضافية')->rows(2)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('حالة المراجعة')->schema([
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار', 'approved' => 'مقبول',
                        'rejected' => 'مرفوض', 'waitlist' => 'قائمة الانتظار',
                    ])
                    ->required()
                    ->disabled(),
                Forms\Components\Textarea::make('rejection_reason')->label('سبب الرفض')->rows(2),
                Forms\Components\Select::make('reviewed_by')->label('راجعه')->relationship('reviewer', 'name')->disabled(),
                Forms\Components\DateTimePicker::make('reviewed_at')->label('تاريخ المراجعة')->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')->label('المرجع')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('البريد')->searchable(),
                Tables\Columns\TextColumn::make('content_type')->label('نوع المحتوى'),
                Tables\Columns\TextColumn::make('followers_count')->label('المتابعون')->numeric(),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors([
                        'warning' => 'pending', 'success' => 'approved',
                        'danger' => 'rejected', 'gray' => 'waitlist',
                    ])
                    ->formatStateUsing(fn (string $state) => [
                        'pending' => 'قيد الانتظار', 'approved' => 'مقبول',
                        'rejected' => 'مرفوض', 'waitlist' => 'قائمة الانتظار',
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ التقديم')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار', 'approved' => 'مقبول',
                        'rejected' => 'مرفوض', 'waitlist' => 'قائمة الانتظار',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('قبول')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CreatorApplication $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('username')
                            ->label('اسم المستخدم للحساب الجديد')
                            ->required()
                            ->unique('creators', 'username'),
                    ])
                    ->action(function (CreatorApplication $record, array $data) {
                        $user = \App\Models\User::firstOrCreate(
                            ['email' => $record->email],
                            [
                                'name' => $record->name,
                                'phone' => $record->phone,
                                'password' => bcrypt(Str::random(16)),
                            ]
                        );

                        $creator = Creator::create([
                            'user_id' => $user->id,
                            'username' => $data['username'],
                            'bio' => $record->bio,
                            'content_type' => $record->content_type,
                            'followers_count' => $record->followers_count ?? 0,
                            'status' => 'active',
                        ]);

                        $record->update([
                            'status' => 'approved',
                            'creator_id' => $creator->id,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('تم قبول الطلب وإنشاء حساب صانع محتوى')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CreatorApplication $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->required(),
                    ])
                    ->action(function (CreatorApplication $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('تم رفض الطلب')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SocialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreatorApplications::route('/'),
            'view' => Pages\ViewCreatorApplication::route('/{record}'),
            'edit' => Pages\EditCreatorApplication::route('/{record}/edit'),
        ];
    }
}
