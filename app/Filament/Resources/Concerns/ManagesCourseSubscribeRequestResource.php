<?php

namespace App\Filament\Resources\Concerns;

use App\Models\CourseSubscribeRequest;
use App\Services\CourseSubscribeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use RuntimeException;

/**
 * Shared Filament form / table / actions for guest course subscribe requests.
 */
trait ManagesCourseSubscribeRequestResource
{
    public static function subscribeRequestForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('المعلومات الشخصية'))->schema([
                Forms\Components\TextInput::make('full_name')->label(__('الاسم الكامل'))->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->label(__('البريد'))->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('phone_country_code')->label(__('رمز الدولة'))->maxLength(10),
                Forms\Components\TextInput::make('phone')->label(__('الهاتف'))->required()->maxLength(40),
            ])->columns(2),
            Forms\Components\Section::make(__('البيانات الأكاديمية والمهنية'))->schema([
                Forms\Components\TextInput::make('academic_level')->label(__('المستوى الدراسي أو المهني'))->required(),
                Forms\Components\Toggle::make('attended_similar_course')->label(__('التحق بدورة مشابهة سابقاً')),
                Forms\Components\Textarea::make('goals_interests')->label(__('أهداف واهتمامات (اختياري)'))->rows(3)->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make(__('أهداف الالتحاق'))->schema([
                Forms\Components\TextInput::make('join_goal')->label(__('هدف الالتحاق بالدورة'))->required(),
                Forms\Components\Textarea::make('additional_notes')->label(__('ملاحظات إضافية'))->rows(3)->columnSpanFull(),
            ]),
            Forms\Components\Section::make(__('الحالة'))->schema([
                Forms\Components\Placeholder::make('status_display')
                    ->label(__('الحالة'))
                    ->content(fn (?CourseSubscribeRequest $record) => match ($record?->status) {
                        'pending' => __('بانتظار المراجعة'),
                        'accepted' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => '—',
                    }),
                Forms\Components\Textarea::make('admin_notes')->label(__('ملاحظة الإدارة'))->columnSpanFull(),
            ]),
        ]);
    }

    public static function subscribeRequestInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('الكورس'))->schema([
                Infolists\Components\TextEntry::make('course.title')
                    ->label(__('الكورس'))
                    ->formatStateUsing(function ($state, CourseSubscribeRequest $record) {
                        return $record->course?->getTranslation('title', 'ar')
                            ?: $record->course?->getTranslation('title', 'en')
                            ?: '—';
                    }),
            ]),
            Infolists\Components\Section::make(__('المعلومات الشخصية'))->schema([
                Infolists\Components\TextEntry::make('full_name')->label(__('الاسم الكامل')),
                Infolists\Components\TextEntry::make('email')->label(__('البريد')),
                Infolists\Components\TextEntry::make('phone_country_code')->label(__('رمز الدولة')),
                Infolists\Components\TextEntry::make('phone')->label(__('الهاتف')),
            ])->columns(2),
            Infolists\Components\Section::make(__('البيانات الأكاديمية والمهنية'))->schema([
                Infolists\Components\TextEntry::make('academic_level')->label(__('المستوى الدراسي أو المهني')),
                Infolists\Components\IconEntry::make('attended_similar_course')
                    ->label(__('دورة مشابهة سابقاً'))
                    ->boolean(),
                Infolists\Components\TextEntry::make('goals_interests')->label(__('أهداف واهتمامات'))->columnSpanFull(),
            ])->columns(2),
            Infolists\Components\Section::make(__('أهداف الالتحاق'))->schema([
                Infolists\Components\TextEntry::make('join_goal')->label(__('هدف الالتحاق')),
                Infolists\Components\TextEntry::make('additional_notes')->label(__('ملاحظات إضافية'))->columnSpanFull(),
            ]),
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

    public static function subscribeRequestTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('الكورس'))
                    ->formatStateUsing(fn ($state, CourseSubscribeRequest $record) => $record->course?->getTranslation('title', 'ar')
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
                Tables\Columns\TextColumn::make('academic_level')->label(__('المستوى'))->toggleable(),
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
            ])
            ->actions(static::subscribeRequestTableActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int, Tables\Actions\Action|Tables\Actions\ViewAction|Tables\Actions\EditAction|Tables\Actions\DeleteAction>
     */
    protected static function subscribeRequestTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('accept')
                ->label(__('قبول'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (CourseSubscribeRequest $record) => $record->isPending())
                ->requiresConfirmation()
                ->action(function (CourseSubscribeRequest $record) {
                    try {
                        $service = app(CourseSubscribeService::class);
                        $service->accept($record, auth()->user());
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    if ($service->lastEmailError) {
                        Notification::make()
                            ->title(__('تم قبول الطلب، لكن تعذر جدولة البريد'))
                            ->body($service->lastEmailError)
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()->title(__('تم قبول الاشتراك — البريد في قائمة الانتظار'))->success()->send();
                }),
            Tables\Actions\Action::make('reject')
                ->label(__('رفض'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn (CourseSubscribeRequest $record) => $record->isPending())
                ->form([
                    Forms\Components\Textarea::make('admin_notes')->label(__('سبب الرفض')),
                ])
                ->action(function (CourseSubscribeRequest $record, array $data) {
                    try {
                        $service = app(CourseSubscribeService::class);
                        $service->reject(
                            $record,
                            auth()->user(),
                            $data['admin_notes'] ?? null,
                        );
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('تم رفض طلب الاشتراك — البريد في قائمة الانتظار'))->success()->send();
                }),
            Tables\Actions\DeleteAction::make(),
        ];
    }
}
