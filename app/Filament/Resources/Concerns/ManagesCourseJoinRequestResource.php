<?php

namespace App\Filament\Resources\Concerns;

use App\Models\CourseJoinRequest;
use App\Services\CourseJoinService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use RuntimeException;

/**
 * Shared Filament form / table / actions for course join & waitlist requests.
 */
trait ManagesCourseJoinRequestResource
{
    public static function joinRequestForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('بيانات الطلب'))->schema([
                Forms\Components\TextInput::make('full_name')->label(__('الاسم الكامل'))->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->label(__('البريد'))->email()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label(__('الهاتف'))->maxLength(40),
                Forms\Components\Textarea::make('message')->label(__('الرسالة'))->rows(4)->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make(__('الحالة'))->schema([
                Forms\Components\Placeholder::make('status_display')
                    ->label(__('الحالة'))
                    ->content(fn (?CourseJoinRequest $record) => match ($record?->status) {
                        'pending' => __('بانتظار المراجعة'),
                        'accepted' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => '—',
                    }),
                Forms\Components\Textarea::make('admin_notes')->label(__('ملاحظة الإدارة'))->columnSpanFull(),
            ]),
        ]);
    }

    public static function joinRequestInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('الكورس'))->schema([
                Infolists\Components\TextEntry::make('course.title')
                    ->label(__('الكورس'))
                    ->formatStateUsing(function ($state, CourseJoinRequest $record) {
                        $title = $record->course?->getTranslation('title', 'ar')
                            ?: $record->course?->getTranslation('title', 'en');

                        return $title ?: '—';
                    }),
                Infolists\Components\IconEntry::make('course.is_coming_soon')
                    ->label(__('قائمة انتظار'))
                    ->boolean(),
            ])->columns(2),
            Infolists\Components\Section::make(__('بيانات الطالب'))->schema([
                Infolists\Components\TextEntry::make('full_name')->label(__('الاسم الكامل')),
                Infolists\Components\TextEntry::make('email')->label(__('البريد')),
                Infolists\Components\TextEntry::make('phone')->label(__('الهاتف')),
                Infolists\Components\TextEntry::make('user.email')->label(__('حساب المستخدم')),
                Infolists\Components\TextEntry::make('message')->label(__('الرسالة'))->columnSpanFull(),
            ])->columns(2),
            Infolists\Components\Section::make(__('الحالة'))->schema([
                Infolists\Components\TextEntry::make('status')->label(__('الحالة'))
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => __('بانتظار المراجعة'),
                        'accepted' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => $state,
                    }),
                Infolists\Components\TextEntry::make('created_at')->label(__('تاريخ الطلب'))->dateTime(),
                Infolists\Components\TextEntry::make('reviewed_at')->label(__('تاريخ المراجعة'))->dateTime(),
                Infolists\Components\TextEntry::make('reviewer.name')->label(__('راجع بواسطة')),
                Infolists\Components\TextEntry::make('admin_notes')->label(__('ملاحظة الإدارة'))->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function joinRequestTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('الكورس'))
                    ->formatStateUsing(fn ($state, CourseJoinRequest $record) => $record->course?->getTranslation('title', 'ar')
                        ?: $record->course?->getTranslation('title', 'en')
                        ?: '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('course', function ($q) use ($search) {
                            $q->where('title->ar', 'like', "%{$search}%")
                                ->orWhere('title->en', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('full_name')->label(__('الاسم'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('البريد'))->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(__('الهاتف')),
                Tables\Columns\IconColumn::make('course.is_coming_soon')
                    ->label(__('انتظار'))
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')->label(__('الحالة'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => __('بانتظار المراجعة'),
                        'accepted' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label(__('التاريخ'))->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'pending' => __('بانتظار المراجعة'),
                        'accepted' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                    ]),
                Tables\Filters\TernaryFilter::make('waitlist')
                    ->label(__('قائمة الانتظار'))
                    ->queries(
                        true: fn ($query) => $query->whereHas('course', fn ($q) => $q->where('is_coming_soon', true)),
                        false: fn ($query) => $query->whereHas('course', fn ($q) => $q->where('is_coming_soon', false)),
                    ),
            ])
            ->actions(static::joinRequestTableActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int, Tables\Actions\Action|Tables\Actions\ViewAction|Tables\Actions\EditAction|Tables\Actions\DeleteAction>
     */
    protected static function joinRequestTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('accept')
                ->label(__('قبول'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (CourseJoinRequest $record) => $record->isPending())
                ->requiresConfirmation()
                ->action(function (CourseJoinRequest $record) {
                    try {
                        app(CourseJoinService::class)->accept($record, auth()->user());
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('تم قبول الطلب وإرسال بريد للمستخدم'))->success()->send();
                }),
            Tables\Actions\Action::make('reject')
                ->label(__('رفض'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn (CourseJoinRequest $record) => $record->isPending())
                ->form([
                    Forms\Components\Textarea::make('admin_notes')->label(__('سبب الرفض')),
                ])
                ->action(function (CourseJoinRequest $record, array $data) {
                    try {
                        app(CourseJoinService::class)->reject(
                            $record,
                            auth()->user(),
                            $data['admin_notes'] ?? null,
                        );
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('تم رفض الطلب وإرسال بريد للمستخدم'))->success()->send();
                }),
            Tables\Actions\DeleteAction::make(),
        ];
    }
}
