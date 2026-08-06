<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseJoinRequestResource\Pages;
use App\Models\CourseJoinRequest;
use App\Services\CourseJoinService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseJoinRequestResource extends Resource
{
    protected static ?string $model = CourseJoinRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses');
    }

    public static function getModelLabel(): string
    {
        return 'طلب انضمام';
    }

    public static function getPluralModelLabel(): string
    {
        return 'طلبات الانضمام';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CourseJoinRequest::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('تفاصيل الطلب')->schema([
                Forms\Components\Placeholder::make('course_title')
                    ->label('الكورس')
                    ->content(fn (?CourseJoinRequest $record) => $record?->course?->title),
                Forms\Components\Placeholder::make('user_name')
                    ->label('المستخدم')
                    ->content(fn (?CourseJoinRequest $record) => $record?->user?->name),
                Forms\Components\TextInput::make('full_name')->label('الاسم في الطلب')->disabled(),
                Forms\Components\TextInput::make('email')->label('البريد')->disabled(),
                Forms\Components\TextInput::make('phone')->label('الهاتف')->disabled(),
                Forms\Components\Textarea::make('message')->label('رسالة الانضمام')->disabled()->columnSpanFull(),
                Forms\Components\Textarea::make('admin_notes')->label('ملاحظات الإدارة')->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'accepted' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ])
                    ->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('course.title')->label('الكورس')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('full_name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('user.email')->label('بريد الحساب')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('الهاتف')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => [
                        'pending' => 'قيد الانتظار',
                        'accepted' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الطلب')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('reviewed_at')->label('تاريخ المراجعة')->dateTime('Y-m-d H:i')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'pending' => 'قيد الانتظار',
                    'accepted' => 'مقبول',
                    'rejected' => 'مرفوض',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('accept')
                    ->label('قبول')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CourseJoinRequest $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('قبول طلب الانضمام')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('ملاحظات (اختياري)')
                            ->rows(3),
                    ])
                    ->action(function (CourseJoinRequest $record, array $data) {
                        app(CourseJoinService::class)->accept(
                            $record,
                            auth()->user(),
                            $data['admin_notes'] ?? null
                        );

                        Notification::make()
                            ->title('تم قبول الطلب وإرسال إشعار بالبريد للمستخدم')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CourseJoinRequest $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('رفض طلب الانضمام')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('سبب الرفض')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (CourseJoinRequest $record, array $data) {
                        app(CourseJoinService::class)->reject(
                            $record,
                            auth()->user(),
                            $data['admin_notes'] ?? null
                        );

                        Notification::make()
                            ->title('تم رفض الطلب')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseJoinRequests::route('/'),
            'view' => Pages\ViewCourseJoinRequest::route('/{record}'),
        ];
    }
}
